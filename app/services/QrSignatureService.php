<?php
/**
 * Service QR Signature — generate QR code untuk TTD digital
 */

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;

class QrSignatureService
{
    public function generateUrlQr(string $data): string
    {
        // Tetap dipertahankan kalau dibutuhkan, walau tidak dipakai lagi
        $encoded = urlencode($data);
        return "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl={$encoded}&choe=UTF-8";
    }

    public function generateAndSave(string $data, string $filename): string
    {
        $path = UPLOAD_PATH_QR . $filename . '.png';
        $logoPath = BASEPATH . '/assets/logo.png';

        // Ensure the target directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Use GD-based generation if GD extension is available
        if (extension_loaded('gd') && class_exists(QrCode::class)) {
            // Use endroid/qr-code library which relies on GD for PNG output
            $writer = new PngWriter();

            $qrCode = QrCode::create($data)
                ->setEncoding(new Encoding('UTF-8'))
                ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
                ->setSize(300)
                ->setMargin(10)
                ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin());

            if (file_exists($logoPath)) {
                $logo = Logo::create($logoPath)
                    ->setResizeToWidth(70)
                    ->setPunchoutBackground(true);
                $result = $writer->write($qrCode, $logo);
            } else {
                $result = $writer->write($qrCode);
            }

            $result->saveToFile($path);
            return 'uploads/qr/' . $filename . '.png';
        }

        // Fallback: generate QR via Google Chart API (no logo)
        $url = $this->generateUrlQr($data);
        $qrData = @file_get_contents($url);
        if ($qrData !== false) {
            file_put_contents($path, $qrData);
            return 'uploads/qr/' . $filename . '.png';
        }

        return '';
    }
}
