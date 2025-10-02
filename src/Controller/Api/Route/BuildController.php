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

        $destinationPoint = $this->mapObjectRepository->findOneBy(['id' => $request->destinationId]);
        if (!$destinationPoint) {
            throw new NotFoundHttpException('Destination point not found');
        }

        if (!$destinationPoint->getLatitude()) {
            throw new NotFoundHttpException('Destination point latitude is null');
        }

        if (!$destinationPoint->getLongitude()) {
            throw new NotFoundHttpException('Destination point longitude is null');
        }

        [$x0, $y0] = ['53.697395', '88.034651'];
        [$x1, $y1] = [$destinationPoint->getLatitude(), $destinationPoint->getLongitude()];
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