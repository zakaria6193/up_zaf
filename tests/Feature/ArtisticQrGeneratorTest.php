<?php

namespace Tests\Feature;

use App\Services\ArtisticQrGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtisticQrGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_python_toolchain_is_detected_when_venv_exists(): void
    {
        $generator = app(ArtisticQrGenerator::class);
        $venvPython = base_path('scripts/artistic_qr/.venv/Scripts/python.exe');
        $unixPython = base_path('scripts/artistic_qr/.venv/bin/python');

        if (! is_file($venvPython) && ! is_file($unixPython)) {
            $this->markTestSkipped('Artistic QR venv is not installed. Run: php artisan qr:setup-artistic');
        }

        $this->assertTrue($generator->available());
        $this->assertFileExists($generator->scriptPath());
    }

    public function test_artistic_qr_script_weaves_logo_into_output(): void
    {
        $generator = app(ArtisticQrGenerator::class);
        if (! $generator->available()) {
            $this->markTestSkipped('Artistic QR venv is not installed. Run: php artisan qr:setup-artistic');
        }

        Storage::fake('public');
        $logo = UploadedFile::fake()->image('logo.jpg', 240, 240);
        $logoPath = Storage::disk('public')->path($logo->store('logos', 'public'));
        // Fake images need real bytes on disk for Python/Pillow.
        file_put_contents($logoPath, $logo->get());

        $output = storage_path('app/tmp/artistic-test-'.uniqid().'.png');

        try {
            $path = $generator->generate(
                'http://127.0.0.1:8000/p/testqr01',
                $logoPath,
                $output,
            );

            $this->assertFileExists($path);
            $info = getimagesize($path);
            $this->assertNotFalse($info);
            $this->assertGreaterThan(200, $info[0]);
            $this->assertGreaterThan(200, $info[1]);
        } finally {
            if (is_file($output)) {
                unlink($output);
            }
        }
    }
}
