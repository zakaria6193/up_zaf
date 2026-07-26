<?php

namespace App\Services;

use App\Models\Business;
use App\Support\QrStyle;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class QrCardGenerator
{
    public function __construct(
        private ArtisticQrGenerator $artisticQrGenerator,
    ) {}

    /**
     * Generate a designed QR card PNG and store it on the public disk.
     */
    public function generate(Business $business): string
    {
        $hasLogo = filled($business->logo) && Storage::disk('public')->exists($business->logo);
        $style = QrStyle::normalize($business->qr_style, $hasLogo);

        if ($business->qr_style !== $style) {
            $business->forceFill(['qr_style' => $style])->saveQuietly();
        }

        $brand = $this->parseHex($business->color ?: '#4d54d9');
        $logoPath = $hasLogo ? Storage::disk('public')->path($business->logo) : null;
        $qrBinary = $this->buildQrPng($business->publicUrl(), $style, $brand, $logoPath);
        $cardBinary = $this->composeCard(
            $business->name,
            $style,
            $brand,
            $qrBinary,
            $business->qrLabelText(),
            $business->qrHeadlineText(),
        );

        $filename = "qrcodes/{$business->nanoid}.png";
        Storage::disk('public')->put($filename, $cardBinary);

        return $filename;
    }

    /**
     * @param  array{r: int, g: int, b: int}  $brand
     */
    private function buildQrPng(string $url, string $style, array $brand, ?string $logoPath): string
    {
        $isDark = $style === QrStyle::Noir;
        $fg = $isDark
            ? new Color(255, 255, 255)
            : new Color($brand['r'], $brand['g'], $brand['b']);
        $bg = $isDark
            ? new Color(24, 24, 27)
            : new Color(255, 255, 255);

        if ($style === QrStyle::Emblem && filled($logoPath) && is_file($logoPath)) {
            return $this->buildEmblemQrPng($url, $fg, $bg, $logoPath);
        }

        $builder = new Builder(
            writer: new PngWriter,
            data: $url,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 520,
            margin: 18,
            foregroundColor: $fg,
            backgroundColor: $bg,
        );

        return $builder->build()->getString();
    }

    /**
     * Prefer Python artistic QR (logo woven into modules). Fall back to a high-ECC
     * Endroid QR with a centered logo punchout if Python is unavailable.
     */
    private function buildEmblemQrPng(string $url, Color $foreground, Color $background, string $logoPath): string
    {
        $tempPath = storage_path('app/tmp/artistic-'.uniqid('qr_', true).'.png');

        try {
            if ($this->artisticQrGenerator->available()) {
                $this->artisticQrGenerator->generate($url, $logoPath, $tempPath);
                $binary = (string) file_get_contents($tempPath);

                if ($binary !== '') {
                    return $binary;
                }
            }
        } catch (Throwable $exception) {
            Log::warning('Falling back from artistic QR to centered logo QR.', [
                'message' => $exception->getMessage(),
            ]);
        } finally {
            if (is_file($tempPath)) {
                @unlink($tempPath);
            }
        }

        return $this->buildCenteredLogoQrPng($url, $foreground, $background, $logoPath);
    }

    /**
     * Classic brand QR: high ECC + logo punched into the center (fallback only).
     */
    private function buildCenteredLogoQrPng(string $url, Color $foreground, Color $background, string $logoPath): string
    {
        $builder = new Builder(
            writer: new PngWriter,
            data: $url,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 520,
            margin: 18,
            foregroundColor: $foreground,
            backgroundColor: $background,
            logoPath: $logoPath,
            logoResizeToWidth: 120,
            logoPunchoutBackground: true,
        );

        return $builder->build()->getString();
    }

    /**
     * @param  array{r: int, g: int, b: int}  $brand
     */
    private function composeCard(
        string $businessName,
        string $style,
        array $brand,
        string $qrBinary,
        string $label,
        string $headline,
    ): string {
        $width = 900;
        $height = 1180;
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        $name = $this->truncate($businessName, 28);
        $label = $this->truncate($label, 18);
        $headline = $this->truncate($headline, 32);

        match ($style) {
            QrStyle::Noir => $this->paintNoir($canvas, $width, $height, $brand, $name, $label, $headline, $qrBinary),
            QrStyle::Emblem => $this->paintEmblem($canvas, $width, $height, $brand, $name, $label, $headline, $qrBinary),
            default => $this->paintPulse($canvas, $width, $height, $brand, $name, $label, $headline, $qrBinary),
        };

        ob_start();
        imagepng($canvas, null, 8);
        $png = (string) ob_get_clean();
        imagedestroy($canvas);

        return $png;
    }

    /**
     * @param  array{r: int, g: int, b: int}  $brand
     */
    private function paintPulse($canvas, int $width, int $height, array $brand, string $name, string $label, string $headline, string $qrBinary): void
    {
        $bg = imagecolorallocate($canvas, 250, 248, 245);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $ink = imagecolorallocate($canvas, 28, 25, 23);
        $accent = imagecolorallocate($canvas, $brand['r'], $brand['g'], $brand['b']);

        imagefilledrectangle($canvas, 0, 0, $width, $height, $bg);
        imagefilledrectangle($canvas, 0, 0, $width, 28, $accent);
        imagefilledrectangle($canvas, 70, 90, $width - 70, $height - 90, $white);

        $this->textSpaced($canvas, $label, $width / 2, 155, 16, $accent, true, true, 8);
        $this->text($canvas, $headline, $width / 2, 210, 40, $ink, true, true);
        $this->placeQr($canvas, $qrBinary, 190, 280, 520);
        $this->text($canvas, $name, $width / 2, 920, 36, $ink, true, true);
        imagefilledrectangle($canvas, ($width / 2) - 36, 960, ($width / 2) + 36, 968, $accent);
    }

    /**
     * @param  array{r: int, g: int, b: int}  $brand
     */
    private function paintNoir($canvas, int $width, int $height, array $brand, string $name, string $label, string $headline, string $qrBinary): void
    {
        $bg = imagecolorallocate($canvas, 24, 24, 27);
        $panel = imagecolorallocate($canvas, 39, 39, 42);
        $white = imagecolorallocate($canvas, 250, 250, 250);
        $accent = imagecolorallocate($canvas, $brand['r'], $brand['g'], $brand['b']);

        imagefilledrectangle($canvas, 0, 0, $width, $height, $bg);
        imagefilledrectangle($canvas, 60, 80, $width - 60, $height - 80, $panel);
        imagefilledrectangle($canvas, 60, 80, $width - 60, 96, $accent);

        $this->textSpaced($canvas, $label, $width / 2, 160, 15, $accent, true, true, 10);
        $this->text($canvas, $headline, $width / 2, 215, 38, $white, true, true);
        $this->placeQr($canvas, $qrBinary, 190, 290, 520);
        $this->text($canvas, $name, $width / 2, 930, 34, $white, true, true);
    }

    /**
     * @param  array{r: int, g: int, b: int}  $brand
     */
    private function paintEmblem($canvas, int $width, int $height, array $brand, string $name, string $label, string $headline, string $qrBinary): void
    {
        $bg = imagecolorallocate($canvas, 255, 255, 255);
        $ink = imagecolorallocate($canvas, 15, 23, 42);
        $accent = imagecolorallocate($canvas, $brand['r'], $brand['g'], $brand['b']);
        $soft = imagecolorallocate($canvas, 248, 250, 252);

        imagefilledrectangle($canvas, 0, 0, $width, $height, $bg);
        imagefilledrectangle($canvas, 0, 0, $width, 48, $accent);
        imagefilledrectangle($canvas, 0, $height - 48, $width, $height, $accent);
        imagefilledrectangle($canvas, 0, 0, 48, $height, $accent);
        imagefilledrectangle($canvas, $width - 48, 0, $width, $height, $accent);
        imagefilledrectangle($canvas, 80, 100, $width - 80, $height - 100, $soft);

        $this->text($canvas, $name, $width / 2, 175, 36, $ink, true, true);
        $this->textSpaced($canvas, $label, $width / 2, 215, 14, $accent, true, true, 8);
        $this->text($canvas, $headline, $width / 2, 260, 30, $accent, true, true);
        $this->placeQr($canvas, $qrBinary, 190, 310, 500);
    }

    private function placeQr($canvas, string $qrBinary, int $x, int $y, int $size): void
    {
        $qr = imagecreatefromstring($qrBinary);
        if ($qr === false) {
            return;
        }

        $srcW = imagesx($qr);
        $srcH = imagesy($qr);
        imagecopyresampled($canvas, $qr, $x, $y, 0, 0, $size, $size, $srcW, $srcH);
        imagedestroy($qr);
    }

    private function text($canvas, string $text, int $x, int $y, int $size, int $color, bool $bold, bool $center): void
    {
        if (trim($text) === '') {
            return;
        }

        $font = $this->fontPath($bold);
        $box = imagettfbbox($size, 0, $font, $text);
        $textWidth = abs($box[2] - $box[0]);
        $drawX = $center ? (int) ($x - ($textWidth / 2)) : $x;
        imagettftext($canvas, $size, 0, $drawX, $y, $color, $font, $text);
    }

    /**
     * Draw uppercase tracked lettering for a more designed look.
     */
    private function textSpaced(
        $canvas,
        string $text,
        int $x,
        int $y,
        int $size,
        int $color,
        bool $bold,
        bool $center,
        int $tracking = 6,
    ): void {
        $text = mb_strtoupper(trim($text));
        if ($text === '') {
            return;
        }

        $font = $this->fontPath($bold);
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $widths = [];
        $total = 0;

        foreach ($chars as $index => $char) {
            $box = imagettfbbox($size, 0, $font, $char);
            $charWidth = abs($box[2] - $box[0]);
            $widths[] = $charWidth;
            $total += $charWidth;
            if ($index < count($chars) - 1) {
                $total += $tracking;
            }
        }

        $cursor = $center ? (int) ($x - ($total / 2)) : $x;
        foreach ($chars as $index => $char) {
            imagettftext($canvas, $size, 0, $cursor, $y, $color, $font, $char);
            $cursor += $widths[$index] + $tracking;
        }
    }

    private function fontPath(bool $bold): string
    {
        $preferred = $bold
            ? resource_path('fonts/qr-sans-bold.ttf')
            : resource_path('fonts/qr-sans.ttf');

        if (is_file($preferred)) {
            return $preferred;
        }

        $fallback = base_path('vendor/endroid/qr-code/assets/open_sans.ttf');
        if (is_file($fallback)) {
            return $fallback;
        }

        throw new \RuntimeException('No TTF font available for QR card text.');
    }

    /**
     * @return array{r: int, g: int, b: int}
     */
    private function parseHex(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            $hex = '4d54d9';
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    private function truncate(string $value, int $max): string
    {
        $value = trim($value);
        if (mb_strlen($value) <= $max) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $max - 1)).'…';
    }
}
