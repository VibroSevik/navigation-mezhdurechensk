<?php

namespace App\Controller\Api\Route;

use App\Controller\Api\Route\Request\BuildRequest;
use App\Repository\MapObjectRepository;
use App\Service\QrCodeGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
class BuildController extends AbstractController
{
    public function __construct(
        private readonly MapObjectRepository $mapObjectRepository,
        private readonly QrCodeGeneratorService $qrCodeGenerator
    )
    {}

    public function __invoke(#[MapRequestPayload] BuildRequest $request): BinaryFileResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $startPoint = $this->mapObjectRepository->findOneBy(['id' => $request->startId]);
        if (!$startPoint) {
            throw new NotFoundHttpException('Start point not found');
        }

        $endPoint = $this->mapObjectRepository->findOneBy(['id' => $request->endId]);
        if (!$endPoint) {
            throw new NotFoundHttpException('End point not found');
        }

        [$x0, $y0] = [$startPoint->getLatitude(), $startPoint->getLongitude()];
        [$x1, $y1] = [$endPoint->getLatitude(), $endPoint->getLongitude()];
        $url = 'https://yandex.ru/maps/?rtext=' . $x0 . ',' . $y0 . '~' . $x1 . ',' . $y1;

        $outputPath = 'qrcode/qr.svg';
        $this->qrCodeGenerator
                ->setQrColor('black')
                ->setBackground(false) // by default
               #->setBackgroundColor('white')
                ->generateDefault($url, $outputPath);

        return new BinaryFileResponse($outputPath, Response::HTTP_OK);
    }
}