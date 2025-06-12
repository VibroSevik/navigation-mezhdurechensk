<?php

/**
 * This code generates an SVG QR code with rounded corners. It uses a round rect for each square and then additional
 * paths to fill in the gap where squares are next to each other. Adjacent squares overlap - to almost completely
 * eliminate hairline antialias "cracks" that tend to appear when two SVG paths are exactly adjacent to each other.
 *
 * composer require chillerlan/php-qrcode (tested with version v5 dev-main)
 */
namespace App\Service;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRMarkupSVG;

//require_once __DIR__.'/../vendor/autoload.php';

class RoundedCornerSVGQRCodeOutput extends QRMarkupSVG{

    private static string $qrColor = '';
    private static string $backgroundColor = '';

    public static function changeQrColor(string $qrColor): void
    {
        RoundedCornerSVGQRCodeOutput::$qrColor = $qrColor;
    }

    public static function changeBackgroundColor(string $backgroundColor): void
    {
        RoundedCornerSVGQRCodeOutput::$backgroundColor = $backgroundColor;
    }

    // this constant may be added to QRMatrix
    protected const neighbours = [
        0b00000001 => [-1, -1],
        0b00000010 => [ 0, -1],
        0b00000100 => [ 1, -1],
        0b00001000 => [ 1,  0],
        0b00010000 => [ 1,  1],
        0b00100000 => [ 0,  1],
        0b01000000 => [-1,  1],
        0b10000000 => [-1,  0]
    ];

    /**
     * Checks the status neighbouring modules of the given module at ($x, $y) and returns a bitmask with the results.
     *
     * The 8 flags of the bitmask represent the status of each of the neighbouring fields,
     * starting with the lowest bit for top left, going clockwise:
     *
     *   1 2 3
     *   8 # 4
     *   7 6 5
     *
     * @todo: when $M_TYPE_VALUE is given, it should check for the same $M_TYPE while igrnoring the IS_DARK flag
     *
     * (this method may be added to QRMatrix as direct array access is faster than method calls)
     */
    protected function checkNeighbours(int $x, int $y, int $M_TYPE_VALUE):int{
        $bits = 0;

        foreach($this::neighbours as $bit => $coord){
            if($this->matrix->check($x + $coord[0], $y + $coord[1])){
                $bits |= $bit;
            }
        }

        return $bits;
    }


    protected function paths():string{
        // main square with rounded corners
        $svg = $this->symbolWithPath('s', ['M0,0 m0,0.3 v0.4 q0,0.3 0.3,0.3 h0.4 q0.3,0 0.3,-0.3 v-0.4 q0,-0.3 -0.3,-0.3 h-0.4 q-0.3,0 -0.3,0.3Z']);

        if (RoundedCornerSVGQRCodeOutput::$backgroundColor != '') {
            $svg .= $this->addBackground();
        }

        // "plus" to invert the corner radius
        $svg .= $this->symbolWithPath('p', [
            // a curved plus, radius slightly righter than the main square
            'M0.3 0.6 Q0.58,0.58 0.6,0.3 Q0.62,0.58 0.9,0.6 Q0.62,0.62 0.6,0.9 Q0.58,0.61 0.3,0.6 Z',
            // a sharp plus (with points further out logner than the curved one)
            'M0.6 0 L0.61 0.59 L1.2 0.6 L0.61 0.61 L0.6 1.2 L0.59 0.61 L0 0.6 L0.59 0.59 Z'
        ]);

        // top/left/bottom/right triangles fill in edges
        $svg .= $this->symbolWithPath('t', ['M0 0.3 L0.6 0.3 L0.3 0.9 Z']);
        $svg .= $this->symbolWithPath('l', ['M0.6 0 L0 0.3 L0.6 0.6 Z']);
        $svg .= $this->symbolWithPath('b', ['M0 0.6 L0.6 0.6 L0.3 0 Z']);
        $svg .= $this->symbolWithPath('r', ['M0.3 0 L1.2 0.3 L0.3 0.6 Z']);

        // a daimond to fill in block areas
        $svg .= $this->symbolWithPath('d', ['M0.6 0 L1.2 0.6 L0.6 1.2 L0 0.6 Z']);

        foreach($this->matrix->matrix() as $y => $row){
            foreach($row as $x => $val){

                if(($val & QRMatrix::IS_DARK) !== QRMatrix::IS_DARK){
                    continue;
                }

                $bits  = $this->checkNeighbours($x, $y, $val);
                $check = fn(int $mask):bool => ($bits & $mask) === $mask;

                // main square block
                $svg .= $this->use('s', $x, $y);

                // top left corner
                if($check(0b10000011)){
                    $svg .= $this->use('d', $x - 0.6, $y - 0.6);
                }
                elseif($check(0b10000001)){
                    $svg .= $this->use('p', $x - 0.6, $y - 0.6);
                }
                elseif($check(0b00000011)){
                    $svg .= $this->use('p', $x - 0.6, $y - 0.6);
                }
                elseif($check(0b00000010)){
                    $svg .= $this->use('r', $x - 0.3, $y - 0.3);
                }
                elseif($check(0b10000000)){
                    $svg .= $this->use('t', $x - 0.3, $y - 0.3);
                }

                // bottom right corner
#				if($check(0b00111000)){
                // already done
#				} else
                if($check(0b00011000)){
                    $svg .= $this->use('p', $x + 1 - 0.6, $y + 1 - 0.6);
                }
                elseif($check(0b00110000)){
                    $svg .= $this->use('p', $x + 1 - 0.6, $y + 1 - 0.6);
                }
                elseif($check(0b00100000)){
                    $svg .= $this->use('l', $x + 1 - 0.6, $y + 1 - 0.3);
                }
                elseif($check(0b00001000)){
                    $svg .= $this->use('b', $x + 1 - 0.3, $y + 1 - 0.6);
                }

                // top right corner
#				if($check(0b00001110)){
                // already done
#				} else
                if($check(0b00001100)){
                    $svg .= $this->use('p', $x + 1 - 0.6, $y - 0.6);
                }
                elseif($check(0b00000110)){
                    $svg .= $this->use('p', $x + 1 - 0.6, $y - 0.6);
                }

            }
        }

        return $svg;
    }

    private function addBackground(): string
    {
        $backgroundColor = RoundedCornerSVGQRCodeOutput::$backgroundColor;
        return "<path class='qr-quietzone light qrcode' fill='$backgroundColor' d='M0 0 h1 v1 h-1Z M1 0 h1 v1 h-1Z M2 0 h1 v1 h-1Z M3 0 h1 v1 h-1Z M4 0 h1 v1 h-1Z M5 0 h1 v1 h-1Z M6 0 h1 v1 h-1Z M7 0 h1 v1 h-1Z M8 0 h1 v1 h-1Z M9 0 h1 v1 h-1Z M10 0 h1 v1 h-1Z M11 0 h1 v1 h-1Z M12 0 h1 v1 h-1Z M13 0 h1 v1 h-1Z M14 0 h1 v1 h-1Z M15 0 h1 v1 h-1Z M16 0 h1 v1 h-1Z M17 0 h1 v1 h-1Z M18 0 h1 v1 h-1Z M19 0 h1 v1 h-1Z M20 0 h1 v1 h-1Z M21 0 h1 v1 h-1Z M22 0 h1 v1 h-1Z M23 0 h1 v1 h-1Z M24 0 h1 v1 h-1Z M25 0 h1 v1 h-1Z M26 0 h1 v1 h-1Z M27 0 h1 v1 h-1Z M28 0 h1 v1 h-1Z M29 0 h1 v1 h-1Z M30 0 h1 v1 h-1Z M31 0 h1 v1 h-1Z M32 0 h1 v1 h-1Z M33 0 h1 v1 h-1Z M34 0 h1 v1 h-1Z M35 0 h1 v1 h-1Z M36 0 h1 v1 h-1Z M37 0 h1 v1 h-1Z M38 0 h1 v1 h-1Z M39 0 h1 v1 h-1Z M40 0 h1 v1 h-1Z M41 0 h1 v1 h-1Z M42 0 h1 v1 h-1Z M43 0 h1 v1 h-1Z M44 0 h1 v1 h-1Z M45 0 h1 v1 h-1Z M46 0 h1 v1 h-1Z M47 0 h1 v1 h-1Z M48 0 h1 v1 h-1Z M49 0 h1 v1 h-1Z M50 0 h1 v1 h-1Z M51 0 h1 v1 h-1Z M52 0 h1 v1 h-1Z M0 1 h1 v1 h-1Z M1 1 h1 v1 h-1Z M2 1 h1 v1 h-1Z M3 1 h1 v1 h-1Z M4 1 h1 v1 h-1Z M5 1 h1 v1 h-1Z M6 1 h1 v1 h-1Z M7 1 h1 v1 h-1Z M8 1 h1 v1 h-1Z M9 1 h1 v1 h-1Z M10 1 h1 v1 h-1Z M11 1 h1 v1 h-1Z M12 1 h1 v1 h-1Z M13 1 h1 v1 h-1Z M14 1 h1 v1 h-1Z M15 1 h1 v1 h-1Z M16 1 h1 v1 h-1Z M17 1 h1 v1 h-1Z M18 1 h1 v1 h-1Z M19 1 h1 v1 h-1Z M20 1 h1 v1 h-1Z M21 1 h1 v1 h-1Z M22 1 h1 v1 h-1Z M23 1 h1 v1 h-1Z M24 1 h1 v1 h-1Z M25 1 h1 v1 h-1Z M26 1 h1 v1 h-1Z M27 1 h1 v1 h-1Z M28 1 h1 v1 h-1Z M29 1 h1 v1 h-1Z M30 1 h1 v1 h-1Z M31 1 h1 v1 h-1Z M32 1 h1 v1 h-1Z M33 1 h1 v1 h-1Z M34 1 h1 v1 h-1Z M35 1 h1 v1 h-1Z M36 1 h1 v1 h-1Z M37 1 h1 v1 h-1Z M38 1 h1 v1 h-1Z M39 1 h1 v1 h-1Z M40 1 h1 v1 h-1Z M41 1 h1 v1 h-1Z M42 1 h1 v1 h-1Z M43 1 h1 v1 h-1Z M44 1 h1 v1 h-1Z M45 1 h1 v1 h-1Z M46 1 h1 v1 h-1Z
                M47 1 h1 v1 h-1Z M48 1 h1 v1 h-1Z M49 1 h1 v1 h-1Z M50 1 h1 v1 h-1Z M51 1 h1 v1 h-1Z M52 1 h1 v1 h-1Z M0 2 h1 v1 h-1Z M1 2 h1 v1 h-1Z M2 2 h1 v1 h-1Z M3 2 h1 v1 h-1Z M4 2 h1 v1 h-1Z M5 2 h1 v1 h-1Z M6 2 h1 v1 h-1Z M7 2 h1 v1 h-1Z M8 2 h1 v1 h-1Z M9 2 h1 v1 h-1Z M10 2 h1 v1 h-1Z M11 2 h1 v1 h-1Z M12 2 h1 v1 h-1Z M13 2 h1 v1 h-1Z M14 2 h1 v1 h-1Z M15 2 h1 v1 h-1Z M16 2 h1 v1 h-1Z M17 2 h1 v1 h-1Z M18 2 h1 v1 h-1Z M19 2 h1 v1 h-1Z M20 2 h1 v1 h-1Z M21 2 h1 v1 h-1Z M22 2 h1 v1 h-1Z M23 2 h1 v1 h-1Z M24 2 h1 v1 h-1Z M25 2 h1 v1 h-1Z M26 2 h1 v1 h-1Z M27 2 h1 v1 h-1Z M28 2 h1 v1 h-1Z M29 2 h1 v1 h-1Z M30 2 h1 v1 h-1Z M31 2 h1 v1 h-1Z M32 2 h1 v1 h-1Z M33 2 h1 v1 h-1Z M34 2 h1 v1 h-1Z M35 2 h1 v1 h-1Z M36 2 h1 v1 h-1Z M37 2 h1 v1 h-1Z M38 2 h1 v1 h-1Z M39 2 h1 v1 h-1Z M40 2 h1 v1 h-1Z M41 2 h1 v1 h-1Z M42 2 h1 v1 h-1Z M43 2 h1 v1 h-1Z M44 2 h1 v1 h-1Z M45 2 h1 v1 h-1Z M46 2 h1 v1 h-1Z M47 2 h1 v1 h-1Z M48 2 h1 v1 h-1Z M49 2 h1 v1 h-1Z M50 2 h1 v1 h-1Z M51 2 h1 v1 h-1Z M52 2 h1 v1 h-1Z M0 3 h1 v1 h-1Z M1 3 h1 v1 h-1Z M2 3 h1 v1 h-1Z M3 3 h1 v1 h-1Z M4 3 h1 v1 h-1Z M5 3 h1 v1 h-1Z M6 3 h1 v1 h-1Z M7 3 h1 v1 h-1Z M8 3 h1 v1 h-1Z M9 3 h1 v1 h-1Z M10 3 h1 v1 h-1Z M11 3 h1 v1 h-1Z M12 3 h1 v1 h-1Z M13 3 h1 v1 h-1Z M14 3 h1 v1 h-1Z M15 3 h1 v1 h-1Z M16 3 h1 v1 h-1Z M17 3 h1 v1 h-1Z M18 3 h1 v1 h-1Z M19 3 h1 v1 h-1Z M20 3 h1 v1 h-1Z M21 3 h1 v1 h-1Z M22 3 h1 v1 h-1Z M23 3 h1 v1 h-1Z M24 3 h1 v1 h-1Z M25 3 h1 v1 h-1Z M26 3 h1 v1 h-1Z M27 3 h1 v1 h-1Z M28 3 h1 v1 h-1Z M29 3 h1 v1 h-1Z M30 3 h1 v1 h-1Z M31 3 h1 v1 h-1Z M32 3 h1 v1 h-1Z M33 3 h1 v1 h-1Z M34 3 h1 v1 h-1Z M35 3 h1 v1 h-1Z M36 3 h1 v1 h-1Z M37 3 h1 v1 h-1Z M38 3 h1 v1 h-1Z M39 3 h1 v1 h-1Z M40 3 h1 v1 h-1Z
                M41 3 h1 v1 h-1Z M42 3 h1 v1 h-1Z M43 3 h1 v1 h-1Z M44 3 h1 v1 h-1Z M45 3 h1 v1 h-1Z M46 3 h1 v1 h-1Z M47 3 h1 v1 h-1Z M48 3 h1 v1 h-1Z M49 3 h1 v1 h-1Z M50 3 h1 v1 h-1Z M51 3 h1 v1 h-1Z M52 3 h1 v1 h-1Z M0 4 h1 v1 h-1Z M1 4 h1 v1 h-1Z M2 4 h1 v1 h-1Z M3 4 h1 v1 h-1Z M49 4 h1 v1 h-1Z M50 4 h1 v1 h-1Z M51 4 h1 v1 h-1Z M52 4 h1 v1 h-1Z M0 5 h1 v1 h-1Z M1 5 h1 v1 h-1Z M2 5 h1 v1 h-1Z M3 5 h1 v1 h-1Z M49 5 h1 v1 h-1Z M50 5 h1 v1 h-1Z M51 5 h1 v1 h-1Z M52 5 h1 v1 h-1Z M0 6 h1 v1 h-1Z M1 6 h1 v1 h-1Z M2 6 h1 v1 h-1Z M3 6 h1 v1 h-1Z M49 6 h1 v1 h-1Z M50 6 h1 v1 h-1Z M51 6 h1 v1 h-1Z M52 6 h1 v1 h-1Z M0 7 h1 v1 h-1Z M1 7 h1 v1 h-1Z M2 7 h1 v1 h-1Z M3 7 h1 v1 h-1Z M49 7 h1 v1 h-1Z M50 7 h1 v1 h-1Z M51 7 h1 v1 h-1Z M52 7 h1 v1 h-1Z M0 8 h1 v1 h-1Z M1 8 h1 v1 h-1Z M2 8 h1 v1 h-1Z M3 8 h1 v1 h-1Z M49 8 h1 v1 h-1Z M50 8 h1 v1 h-1Z M51 8 h1 v1 h-1Z M52 8 h1 v1 h-1Z M0 9 h1 v1 h-1Z M1 9 h1 v1 h-1Z M2 9 h1 v1 h-1Z M3 9 h1 v1 h-1Z M49 9 h1 v1 h-1Z M50 9 h1 v1 h-1Z M51 9 h1 v1 h-1Z M52 9 h1 v1 h-1Z M0 10 h1 v1 h-1Z M1 10 h1 v1 h-1Z M2 10 h1 v1 h-1Z M3 10 h1 v1 h-1Z M49 10 h1 v1 h-1Z M50 10 h1 v1 h-1Z M51 10 h1 v1 h-1Z M52 10 h1 v1 h-1Z M0 11 h1 v1 h-1Z M1 11 h1 v1 h-1Z M2 11 h1 v1 h-1Z M3 11 h1 v1 h-1Z M49 11 h1 v1 h-1Z M50 11 h1 v1 h-1Z M51 11 h1 v1 h-1Z M52 11 h1 v1 h-1Z M0 12 h1 v1 h-1Z M1 12 h1 v1 h-1Z M2 12 h1 v1 h-1Z M3 12 h1 v1 h-1Z M49 12 h1 v1 h-1Z M50 12 h1 v1 h-1Z M51 12 h1 v1 h-1Z M52 12 h1 v1 h-1Z M0 13 h1 v1 h-1Z M1 13 h1 v1 h-1Z M2 13 h1 v1 h-1Z M3 13 h1 v1 h-1Z M49 13 h1 v1 h-1Z M50 13 h1 v1 h-1Z M51 13 h1 v1 h-1Z M52 13 h1 v1 h-1Z M0 14 h1 v1 h-1Z M1 14 h1 v1 h-1Z M2 14 h1 v1 h-1Z M3 14 h1 v1 h-1Z M49 14 h1 v1 h-1Z M50 14 h1 v1 h-1Z M51 14 h1 v1 h-1Z M52 14 h1 v1 h-1Z
                M0 15 h1 v1 h-1Z M1 15 h1 v1 h-1Z M2 15 h1 v1 h-1Z M3 15 h1 v1 h-1Z M49 15 h1 v1 h-1Z M50 15 h1 v1 h-1Z M51 15 h1 v1 h-1Z M52 15 h1 v1 h-1Z M0 16 h1 v1 h-1Z M1 16 h1 v1 h-1Z M2 16 h1 v1 h-1Z M3 16 h1 v1 h-1Z M49 16 h1 v1 h-1Z M50 16 h1 v1 h-1Z M51 16 h1 v1 h-1Z M52 16 h1 v1 h-1Z M0 17 h1 v1 h-1Z M1 17 h1 v1 h-1Z M2 17 h1 v1 h-1Z M3 17 h1 v1 h-1Z M49 17 h1 v1 h-1Z M50 17 h1 v1 h-1Z M51 17 h1 v1 h-1Z M52 17 h1 v1 h-1Z M0 18 h1 v1 h-1Z M1 18 h1 v1 h-1Z M2 18 h1 v1 h-1Z M3 18 h1 v1 h-1Z M49 18 h1 v1 h-1Z M50 18 h1 v1 h-1Z M51 18 h1 v1 h-1Z M52 18 h1 v1 h-1Z M0 19 h1 v1 h-1Z M1 19 h1 v1 h-1Z M2 19 h1 v1 h-1Z M3 19 h1 v1 h-1Z M49 19 h1 v1 h-1Z M50 19 h1 v1 h-1Z M51 19 h1 v1 h-1Z M52 19 h1 v1 h-1Z M0 20 h1 v1 h-1Z M1 20 h1 v1 h-1Z M2 20 h1 v1 h-1Z M3 20 h1 v1 h-1Z M49 20 h1 v1 h-1Z M50 20 h1 v1 h-1Z M51 20 h1 v1 h-1Z M52 20 h1 v1 h-1Z M0 21 h1 v1 h-1Z M1 21 h1 v1 h-1Z M2 21 h1 v1 h-1Z M3 21 h1 v1 h-1Z M49 21 h1 v1 h-1Z M50 21 h1 v1 h-1Z M51 21 h1 v1 h-1Z M52 21 h1 v1 h-1Z M0 22 h1 v1 h-1Z M1 22 h1 v1 h-1Z M2 22 h1 v1 h-1Z M3 22 h1 v1 h-1Z M49 22 h1 v1 h-1Z M50 22 h1 v1 h-1Z M51 22 h1 v1 h-1Z M52 22 h1 v1 h-1Z M0 23 h1 v1 h-1Z M1 23 h1 v1 h-1Z M2 23 h1 v1 h-1Z M3 23 h1 v1 h-1Z M49 23 h1 v1 h-1Z M50 23 h1 v1 h-1Z M51 23 h1 v1 h-1Z M52 23 h1 v1 h-1Z M0 24 h1 v1 h-1Z M1 24 h1 v1 h-1Z M2 24 h1 v1 h-1Z M3 24 h1 v1 h-1Z M49 24 h1 v1 h-1Z M50 24 h1 v1 h-1Z M51 24 h1 v1 h-1Z M52 24 h1 v1 h-1Z M0 25 h1 v1 h-1Z M1 25 h1 v1 h-1Z M2 25 h1 v1 h-1Z M3 25 h1 v1 h-1Z M49 25 h1 v1 h-1Z M50 25 h1 v1 h-1Z M51 25 h1 v1 h-1Z M52 25 h1 v1 h-1Z M0 26 h1 v1 h-1Z M1 26 h1 v1 h-1Z M2 26 h1 v1 h-1Z M3 26 h1 v1 h-1Z M49 26 h1 v1 h-1Z M50 26 h1 v1 h-1Z M51 26 h1 v1 h-1Z M52 26 h1 v1 h-1Z M0 27 h1 v1 h-1Z M1 27 h1 v1 h-1Z M2 27 h1 v1 h-1Z M3 27 h1 v1 h-1Z
                M49 27 h1 v1 h-1Z M50 27 h1 v1 h-1Z M51 27 h1 v1 h-1Z M52 27 h1 v1 h-1Z M0 28 h1 v1 h-1Z M1 28 h1 v1 h-1Z M2 28 h1 v1 h-1Z M3 28 h1 v1 h-1Z M49 28 h1 v1 h-1Z M50 28 h1 v1 h-1Z M51 28 h1 v1 h-1Z M52 28 h1 v1 h-1Z M0 29 h1 v1 h-1Z M1 29 h1 v1 h-1Z M2 29 h1 v1 h-1Z M3 29 h1 v1 h-1Z M49 29 h1 v1 h-1Z M50 29 h1 v1 h-1Z M51 29 h1 v1 h-1Z M52 29 h1 v1 h-1Z M0 30 h1 v1 h-1Z M1 30 h1 v1 h-1Z M2 30 h1 v1 h-1Z M3 30 h1 v1 h-1Z M49 30 h1 v1 h-1Z M50 30 h1 v1 h-1Z M51 30 h1 v1 h-1Z M52 30 h1 v1 h-1Z M0 31 h1 v1 h-1Z M1 31 h1 v1 h-1Z M2 31 h1 v1 h-1Z M3 31 h1 v1 h-1Z M49 31 h1 v1 h-1Z M50 31 h1 v1 h-1Z M51 31 h1 v1 h-1Z M52 31 h1 v1 h-1Z M0 32 h1 v1 h-1Z M1 32 h1 v1 h-1Z M2 32 h1 v1 h-1Z M3 32 h1 v1 h-1Z M49 32 h1 v1 h-1Z M50 32 h1 v1 h-1Z M51 32 h1 v1 h-1Z M52 32 h1 v1 h-1Z M0 33 h1 v1 h-1Z M1 33 h1 v1 h-1Z M2 33 h1 v1 h-1Z M3 33 h1 v1 h-1Z M49 33 h1 v1 h-1Z M50 33 h1 v1 h-1Z M51 33 h1 v1 h-1Z M52 33 h1 v1 h-1Z M0 34 h1 v1 h-1Z M1 34 h1 v1 h-1Z M2 34 h1 v1 h-1Z M3 34 h1 v1 h-1Z M49 34 h1 v1 h-1Z M50 34 h1 v1 h-1Z M51 34 h1 v1 h-1Z M52 34 h1 v1 h-1Z M0 35 h1 v1 h-1Z M1 35 h1 v1 h-1Z M2 35 h1 v1 h-1Z M3 35 h1 v1 h-1Z M49 35 h1 v1 h-1Z M50 35 h1 v1 h-1Z M51 35 h1 v1 h-1Z M52 35 h1 v1 h-1Z M0 36 h1 v1 h-1Z M1 36 h1 v1 h-1Z M2 36 h1 v1 h-1Z M3 36 h1 v1 h-1Z M49 36 h1 v1 h-1Z M50 36 h1 v1 h-1Z M51 36 h1 v1 h-1Z M52 36 h1 v1 h-1Z M0 37 h1 v1 h-1Z M1 37 h1 v1 h-1Z M2 37 h1 v1 h-1Z M3 37 h1 v1 h-1Z M49 37 h1 v1 h-1Z M50 37 h1 v1 h-1Z M51 37 h1 v1 h-1Z M52 37 h1 v1 h-1Z M0 38 h1 v1 h-1Z M1 38 h1 v1 h-1Z M2 38 h1 v1 h-1Z M3 38 h1 v1 h-1Z M49 38 h1 v1 h-1Z M50 38 h1 v1 h-1Z M51 38 h1 v1 h-1Z M52 38 h1 v1 h-1Z M0 39 h1 v1 h-1Z M1 39 h1 v1 h-1Z M2 39 h1 v1 h-1Z M3 39 h1 v1 h-1Z M49 39 h1 v1 h-1Z M50 39 h1 v1 h-1Z M51 39 h1 v1 h-1Z M52 39 h1 v1 h-1Z
                M0 40 h1 v1 h-1Z M1 40 h1 v1 h-1Z M2 40 h1 v1 h-1Z M3 40 h1 v1 h-1Z M49 40 h1 v1 h-1Z M50 40 h1 v1 h-1Z M51 40 h1 v1 h-1Z M52 40 h1 v1 h-1Z M0 41 h1 v1 h-1Z M1 41 h1 v1 h-1Z M2 41 h1 v1 h-1Z M3 41 h1 v1 h-1Z M49 41 h1 v1 h-1Z M50 41 h1 v1 h-1Z M51 41 h1 v1 h-1Z M52 41 h1 v1 h-1Z M0 42 h1 v1 h-1Z M1 42 h1 v1 h-1Z M2 42 h1 v1 h-1Z M3 42 h1 v1 h-1Z M49 42 h1 v1 h-1Z M50 42 h1 v1 h-1Z M51 42 h1 v1 h-1Z M52 42 h1 v1 h-1Z M0 43 h1 v1 h-1Z M1 43 h1 v1 h-1Z M2 43 h1 v1 h-1Z M3 43 h1 v1 h-1Z M49 43 h1 v1 h-1Z M50 43 h1 v1 h-1Z M51 43 h1 v1 h-1Z M52 43 h1 v1 h-1Z M0 44 h1 v1 h-1Z M1 44 h1 v1 h-1Z M2 44 h1 v1 h-1Z M3 44 h1 v1 h-1Z M49 44 h1 v1 h-1Z M50 44 h1 v1 h-1Z M51 44 h1 v1 h-1Z M52 44 h1 v1 h-1Z M0 45 h1 v1 h-1Z M1 45 h1 v1 h-1Z M2 45 h1 v1 h-1Z M3 45 h1 v1 h-1Z M49 45 h1 v1 h-1Z M50 45 h1 v1 h-1Z M51 45 h1 v1 h-1Z M52 45 h1 v1 h-1Z M0 46 h1 v1 h-1Z M1 46 h1 v1 h-1Z M2 46 h1 v1 h-1Z M3 46 h1 v1 h-1Z M49 46 h1 v1 h-1Z M50 46 h1 v1 h-1Z M51 46 h1 v1 h-1Z M52 46 h1 v1 h-1Z M0 47 h1 v1 h-1Z M1 47 h1 v1 h-1Z M2 47 h1 v1 h-1Z M3 47 h1 v1 h-1Z M49 47 h1 v1 h-1Z M50 47 h1 v1 h-1Z M51 47 h1 v1 h-1Z M52 47 h1 v1 h-1Z M0 48 h1 v1 h-1Z M1 48 h1 v1 h-1Z M2 48 h1 v1 h-1Z M3 48 h1 v1 h-1Z M49 48 h1 v1 h-1Z M50 48 h1 v1 h-1Z M51 48 h1 v1 h-1Z M52 48 h1 v1 h-1Z M0 49 h1 v1 h-1Z M1 49 h1 v1 h-1Z M2 49 h1 v1 h-1Z M3 49 h1 v1 h-1Z M4 49 h1 v1 h-1Z M5 49 h1 v1 h-1Z M6 49 h1 v1 h-1Z M7 49 h1 v1 h-1Z M8 49 h1 v1 h-1Z M9 49 h1 v1 h-1Z M10 49 h1 v1 h-1Z M11 49 h1 v1 h-1Z M12 49 h1 v1 h-1Z M13 49 h1 v1 h-1Z M14 49 h1 v1 h-1Z M15 49 h1 v1 h-1Z M16 49 h1 v1 h-1Z M17 49 h1 v1 h-1Z M18 49 h1 v1 h-1Z M19 49 h1 v1 h-1Z M20 49 h1 v1 h-1Z M21 49 h1 v1 h-1Z M22 49 h1 v1 h-1Z M23 49 h1 v1 h-1Z M24 49 h1 v1 h-1Z M25 49 h1 v1 h-1Z M26 49 h1 v1 h-1Z M27 49 h1 v1 h-1Z
                M28 49 h1 v1 h-1Z M29 49 h1 v1 h-1Z M30 49 h1 v1 h-1Z M31 49 h1 v1 h-1Z M32 49 h1 v1 h-1Z M33 49 h1 v1 h-1Z M34 49 h1 v1 h-1Z M35 49 h1 v1 h-1Z M36 49 h1 v1 h-1Z M37 49 h1 v1 h-1Z M38 49 h1 v1 h-1Z M39 49 h1 v1 h-1Z M40 49 h1 v1 h-1Z M41 49 h1 v1 h-1Z M42 49 h1 v1 h-1Z M43 49 h1 v1 h-1Z M44 49 h1 v1 h-1Z M45 49 h1 v1 h-1Z M46 49 h1 v1 h-1Z M47 49 h1 v1 h-1Z M48 49 h1 v1 h-1Z M49 49 h1 v1 h-1Z M50 49 h1 v1 h-1Z M51 49 h1 v1 h-1Z M52 49 h1 v1 h-1Z M0 50 h1 v1 h-1Z M1 50 h1 v1 h-1Z M2 50 h1 v1 h-1Z M3 50 h1 v1 h-1Z M4 50 h1 v1 h-1Z M5 50 h1 v1 h-1Z M6 50 h1 v1 h-1Z M7 50 h1 v1 h-1Z M8 50 h1 v1 h-1Z M9 50 h1 v1 h-1Z M10 50 h1 v1 h-1Z M11 50 h1 v1 h-1Z M12 50 h1 v1 h-1Z M13 50 h1 v1 h-1Z M14 50 h1 v1 h-1Z M15 50 h1 v1 h-1Z M16 50 h1 v1 h-1Z M17 50 h1 v1 h-1Z M18 50 h1 v1 h-1Z M19 50 h1 v1 h-1Z M20 50 h1 v1 h-1Z M21 50 h1 v1 h-1Z M22 50 h1 v1 h-1Z M23 50 h1 v1 h-1Z M24 50 h1 v1 h-1Z M25 50 h1 v1 h-1Z M26 50 h1 v1 h-1Z M27 50 h1 v1 h-1Z M28 50 h1 v1 h-1Z M29 50 h1 v1 h-1Z M30 50 h1 v1 h-1Z M31 50 h1 v1 h-1Z M32 50 h1 v1 h-1Z M33 50 h1 v1 h-1Z M34 50 h1 v1 h-1Z M35 50 h1 v1 h-1Z M36 50 h1 v1 h-1Z M37 50 h1 v1 h-1Z M38 50 h1 v1 h-1Z M39 50 h1 v1 h-1Z M40 50 h1 v1 h-1Z M41 50 h1 v1 h-1Z M42 50 h1 v1 h-1Z M43 50 h1 v1 h-1Z M44 50 h1 v1 h-1Z M45 50 h1 v1 h-1Z M46 50 h1 v1 h-1Z M47 50 h1 v1 h-1Z M48 50 h1 v1 h-1Z M49 50 h1 v1 h-1Z M50 50 h1 v1 h-1Z M51 50 h1 v1 h-1Z M52 50 h1 v1 h-1Z M0 51 h1 v1 h-1Z M1 51 h1 v1 h-1Z M2 51 h1 v1 h-1Z M3 51 h1 v1 h-1Z M4 51 h1 v1 h-1Z M5 51 h1 v1 h-1Z M6 51 h1 v1 h-1Z M7 51 h1 v1 h-1Z M8 51 h1 v1 h-1Z M9 51 h1 v1 h-1Z M10 51 h1 v1 h-1Z M11 51 h1 v1 h-1Z M12 51 h1 v1 h-1Z M13 51 h1 v1 h-1Z M14 51 h1 v1 h-1Z M15 51 h1 v1 h-1Z M16 51 h1 v1 h-1Z M17 51 h1 v1 h-1Z M18 51 h1 v1 h-1Z M19 51 h1 v1 h-1Z M20 51 h1 v1 h-1Z M21 51 h1 v1 h-1Z
                M22 51 h1 v1 h-1Z M23 51 h1 v1 h-1Z M24 51 h1 v1 h-1Z M25 51 h1 v1 h-1Z M26 51 h1 v1 h-1Z M27 51 h1 v1 h-1Z M28 51 h1 v1 h-1Z M29 51 h1 v1 h-1Z M30 51 h1 v1 h-1Z M31 51 h1 v1 h-1Z M32 51 h1 v1 h-1Z M33 51 h1 v1 h-1Z M34 51 h1 v1 h-1Z M35 51 h1 v1 h-1Z M36 51 h1 v1 h-1Z M37 51 h1 v1 h-1Z M38 51 h1 v1 h-1Z M39 51 h1 v1 h-1Z M40 51 h1 v1 h-1Z M41 51 h1 v1 h-1Z M42 51 h1 v1 h-1Z M43 51 h1 v1 h-1Z M44 51 h1 v1 h-1Z M45 51 h1 v1 h-1Z M46 51 h1 v1 h-1Z M47 51 h1 v1 h-1Z M48 51 h1 v1 h-1Z M49 51 h1 v1 h-1Z M50 51 h1 v1 h-1Z M51 51 h1 v1 h-1Z M52 51 h1 v1 h-1Z M0 52 h1 v1 h-1Z M1 52 h1 v1 h-1Z M2 52 h1 v1 h-1Z M3 52 h1 v1 h-1Z M4 52 h1 v1 h-1Z M5 52 h1 v1 h-1Z M6 52 h1 v1 h-1Z M7 52 h1 v1 h-1Z M8 52 h1 v1 h-1Z M9 52 h1 v1 h-1Z M10 52 h1 v1 h-1Z M11 52 h1 v1 h-1Z M12 52 h1 v1 h-1Z M13 52 h1 v1 h-1Z M14 52 h1 v1 h-1Z M15 52 h1 v1 h-1Z M16 52 h1 v1 h-1Z M17 52 h1 v1 h-1Z M18 52 h1 v1 h-1Z M19 52 h1 v1 h-1Z M20 52 h1 v1 h-1Z M21 52 h1 v1 h-1Z M22 52 h1 v1 h-1Z M23 52 h1 v1 h-1Z M24 52 h1 v1 h-1Z M25 52 h1 v1 h-1Z M26 52 h1 v1 h-1Z M27 52 h1 v1 h-1Z M28 52 h1 v1 h-1Z M29 52 h1 v1 h-1Z M30 52 h1 v1 h-1Z M31 52 h1 v1 h-1Z M32 52 h1 v1 h-1Z M33 52 h1 v1 h-1Z M34 52 h1 v1 h-1Z M35 52 h1 v1 h-1Z M36 52 h1 v1 h-1Z M37 52 h1 v1 h-1Z M38 52 h1 v1 h-1Z M39 52 h1 v1 h-1Z M40 52 h1 v1 h-1Z M41 52 h1 v1 h-1Z M42 52 h1 v1 h-1Z M43 52 h1 v1 h-1Z M44 52 h1 v1 h-1Z M45 52 h1 v1 h-1Z M46 52 h1 v1 h-1Z M47 52 h1 v1 h-1Z M48 52 h1 v1 h-1Z M49 52 h1 v1 h-1Z M50 52 h1 v1 h-1Z M51 52 h1 v1 h-1Z M52 52 h1 v1 h-1Z'/>";
    }

    protected function use($node, $x, $y){
        return "<use href=\"#$node\" x=\"$x\" y=\"$y\"/>\n";
    }

    protected function symbolWithPath($id, $paths, $fill = null){
        $color = RoundedCornerSVGQRCodeOutput::$qrColor;

        $fillOutput = $fill === null ? ' fill="' . $color . '"' : " fill=\"$fill\"";
        $output     = "<symbol id=\"$id\"$fillOutput>";

        foreach($paths as $path){
            $output .= "<path d=\"$path\" class=\"dark\" shape-rendering=\"geometricPrecision\" fill=\"$color\" />";
        }

        $output .= "</symbol>\n";

        return $output;
    }

}