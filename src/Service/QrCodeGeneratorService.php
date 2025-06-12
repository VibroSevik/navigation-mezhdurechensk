<?php

namespace App\Service;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeGeneratorService
{
    public function setQrColor(string $color): self
    {
        RoundedCornerSVGQRCodeOutput::changeQrColor($color);
        return $this;
    }

    public function setBackgroundColor(string $color): self
    {
        RoundedCornerSVGQRCodeOutput::changeBackgroundColor($color);
        return $this;
    }

    public function setBackground(bool $setBackground): self
    {
        RoundedCornerSVGQRCodeOutput::setBackground($setBackground);
        return $this;
    }

    public function generateWithOptions(int $eccLevel,
                                        bool $imageBase64,
                                        bool $addQuietzone,
                                        string $outputType,
                                        int $version,
                                        bool $outputBase64,
                                        string $url,
                                        string $outputPath): string
    {
        $options = new QROptions;
        $options->eccLevel = $eccLevel;
        $options->imageBase64 = $imageBase64;
        $options->addQuietzone = $addQuietzone;
        $options->outputType = $outputType;
        $options->outputInterface = RoundedCornerSVGQRCodeOutput::class;
        $options->version = $version;
        $options->outputBase64 = $outputBase64;

        return $this->generateWithArrayOptions($options, $url, $outputPath);
    }

    public function generateWithArrayOptions(QROptions $options, string $url, string $outputPath): string
    {
        $qrcode = (new QRCode($options))->render($url, $outputPath);
        return $qrcode;
    }

    public function generateDefault(string $url, string $outputPath): string
    {
        $options = new QROptions;
        $options->eccLevel = EccLevel::L;
        $options->imageBase64 = false;
        $options->addQuietzone = true;
        $options->outputType = QRCODE::OUTPUT_CUSTOM;
        $options->outputInterface = RoundedCornerSVGQRCodeOutput::class;
        $options->version = 7;
        $options->outputBase64 = false;
        return $this->generateWithArrayOptions($options, $url, $outputPath);
    }

}