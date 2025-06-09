<?php

namespace App\Controller\Api\User;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;

class TokenAuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RefreshTokenManagerInterface $refreshTokenManager,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly UserProviderInterface $userProvider,
        private readonly RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
    )
    {}

    #[Route(path: '/api/token/refresh', name: 'api_refresh_token', methods: ['POST'])]
    public function refreshToken(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        if (!isset($content['refresh_token'])) {
            return new JsonResponse(['error' => 'No refresh token provided'], Response::HTTP_BAD_REQUEST);
        }

        $refreshToken = $content['refresh_token'];

        if (!$refreshToken) {
            return new JsonResponse(['error' => 'No refresh token provided'], Response::HTTP_BAD_REQUEST);
        }

        $token = $this->refreshTokenManager->get($refreshToken);

        if (!$token || !$token->isValid()) {
            return new JsonResponse(['error' => 'Invalid refresh token'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userProvider->loadUserByIdentifier($token->getUsername());

        $newJwtToken = $this->jwtManager->create($user);

        return new JsonResponse(['token' => $newJwtToken]);
    }

    #[Route('/api/authentication_token', name: 'api_get_token', methods: ['POST'])]
    public function getToken(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Username and password are required'], 400);
        }

        $user = $this->userRepository->findOneBy(['username' => $data['username']]);

        if (!$user) {
            return new JsonResponse(['error' => 'Invalid credentials'], 400);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], 400);
        }

        $token = $this->jwtManager->create($user);
        $ttl = new \DateTime('+1 month');
        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl(
            $user,
            $ttl->getTimestamp() - time()
        );

        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        return $this->json(['token' => $token, 'refresh_token' => $refreshToken->getRefreshToken()]);
    }
}