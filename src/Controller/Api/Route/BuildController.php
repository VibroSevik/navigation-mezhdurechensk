<?php

namespace App\Controller\Api\Route;

use App\Controller\Api\Route\Request\BuildRequest;
use App\Repository\MapObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
class BuildController extends AbstractController
{
    public function __construct(
        private readonly MapObjectRepository $mapObjectRepository,
    )
    {}

    public function __invoke(#[MapRequestPayload] BuildRequest $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $startPoint = $this->mapObjectRepository->findOneBy(['id' => $request->startId]);
        if (!$startPoint) {
            return new JsonResponse('Start point not found', Response::HTTP_NOT_FOUND);
        }

        $endPoint = $this->mapObjectRepository->findOneBy(['id' => $request->endId]);
        if (!$endPoint) {
            return new JsonResponse('End point not found', Response::HTTP_NOT_FOUND);
        }

        [$x0, $y0] = [$startPoint->getLongitude(), $startPoint->getLatitude()];
        [$x1, $y1] = [$endPoint->getLongitude(), $endPoint->getLatitude()];
        $url = 'https://yandex.ru/maps/?rtext=' . $x0 . ',' . $y0 . '~' . $x1 . ',' . $y1;

        return $this->json(['url' => $url], Response::HTTP_OK);
    }
}