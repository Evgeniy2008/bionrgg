<?php

namespace App\Services;

use App\DTO\Profile;
use RuntimeException;

class ExportService
{
    private string $basePath;
    private string $baseUrl;
    private string $baseDomain;

    public function __construct(string $basePath, string $baseUrl = '/uploads')
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->baseDomain = $_ENV['APP_BASE_URL'] ?? getenv('APP_BASE_URL') ?? 'https://bionrgg.com';
    }

    public function generateQr(Profile $profile): string
    {
        $folder = 'qr';
        $this->ensureDirectory($folder);

        $filename = $profile->usernameSlug . '-' . uniqid('', true) . '.png';
        $absolute = $this->basePath . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $filename;

        $qrData = $this->downloadQrPng($this->profileUrl($profile));
        if ($qrData === null) {
            $filename = $profile->usernameSlug . '-' . uniqid('', true) . '.svg';
            $absolute = $this->basePath . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $filename;
            $svg = $this->buildPlaceholderQr($profile);
            if (file_put_contents($absolute, $svg) === false) {
                throw new RuntimeException('Failed to create QR export.');
            }
        } else {
            if (file_put_contents($absolute, $qrData) === false) {
                throw new RuntimeException('Failed to create QR export.');
            }
        }

        return $this->baseUrl . '/' . $folder . '/' . $filename;
    }

    public function generatePdf(Profile $profile): string
    {
        $folder = 'pdf';
        $this->ensureDirectory($folder);

        $filename = $profile->usernameSlug . '-' . uniqid('', true) . '.pdf';
        $absolute = $this->basePath . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $filename;

        $pdf = $this->buildPlaceholderPdf($profile);
        if (file_put_contents($absolute, $pdf) === false) {
            throw new RuntimeException('Failed to create PDF export.');
        }

        return $this->baseUrl . '/' . $folder . '/' . $filename;
    }

    private function ensureDirectory(string $folder): void
    {
        $path = $this->basePath . DIRECTORY_SEPARATOR . $folder;
        if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException('Unable to create export directory.');
        }
    }

    private function buildPlaceholderQr(Profile $profile): string
    {
        $displayName = trim($profile->firstName . ' ' . $profile->lastName);
        $displayName = htmlspecialchars($displayName !== '' ? $displayName : $profile->usernameSlug, ENT_QUOTES, 'UTF-8');
        $url = htmlspecialchars($this->profileUrl($profile), ENT_QUOTES, 'UTF-8');

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256">
  <rect width="256" height="256" fill="#2572ad"/>
  <text x="50%" y="48%" font-size="18" fill="#ffffff" text-anchor="middle" font-family="Arial, sans-serif">Bionrgg QR</text>
  <text x="50%" y="60%" font-size="14" fill="#ffffff" text-anchor="middle" font-family="Arial, sans-serif">{$displayName}</text>
  <text x="50%" y="75%" font-size="10" fill="#ffffff" text-anchor="middle" font-family="Arial, sans-serif">{$url}</text>
</svg>
SVG;
    }

    private function buildPlaceholderPdf(Profile $profile): string
    {
        $lines = [
            'Bionrgg Profile Card',
            trim($profile->firstName . ' ' . $profile->lastName),
            $profile->positionTitle ?? '',
            $this->profileUrl($profile),
        ];

        $lines = array_filter(array_map(fn($line) => trim($line), $lines));
        $textContent = '';
        foreach ($lines as $index => $line) {
            $escaped = $this->escapePdfText($line);
            if ($index === 0) {
                $textContent .= "BT\n/F1 20 Tf\n72 720 Td\n({$escaped}) Tj\n";
            } else {
                $textContent .= sprintf("0 -28 Td\n(%s) Tj\n", $escaped);
            }
        }
        $textContent .= "ET\n";

        $objects = [];
        $offsets = [];
        $pdf = "%PDF-1.4\n";

        $append = function (int $id, string $body) use (&$pdf, &$offsets, &$objects): void {
            $offsets[$id] = strlen($pdf);
            $objects[$id] = "{$id} 0 obj\n{$body}\nendobj\n";
            $pdf .= $objects[$id];
        };

        $append(1, '<< /Type /Catalog /Pages 2 0 R >>');
        $append(2, '<< /Type /Pages /Kids [3 0 R] /Count 1 >>');
        $append(3, '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>');

        $stream = $textContent;
        $length = strlen($stream);
        $body4 = "<< /Length {$length} >>\nstream\n{$stream}endstream";
        $append(4, $body4);

        $append(5, '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>');

        $xrefOffset = strlen($pdf);
        $count = count($objects);
        $pdf .= "xref\n0 " . ($count + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size " . ($count + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function profileUrl(Profile $profile): string
    {
        return rtrim($this->baseDomain, '/') . '/@' . $profile->usernameSlug;
    }

    private function downloadQrPng(string $data): ?string
    {
        $url = 'https://chart.googleapis.com/chart?chs=320x320&cht=qr&chld=H|0&chl=' . rawurlencode($data);
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
            ]
        ]);
        $contents = @file_get_contents($url, false, $context);

        if ($contents === false) {
            return null;
        }

        return $contents;
    }

    private function escapePdfText(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}


