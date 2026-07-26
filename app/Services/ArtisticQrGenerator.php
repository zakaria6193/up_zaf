<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class ArtisticQrGenerator
{
    /**
     * Whether the Python artistic QR toolchain looks usable.
     */
    public function available(): bool
    {
        $python = $this->pythonBinary();
        $script = $this->scriptPath();

        return filled($python) && is_file((string) $python) && is_file($script);
    }

    /**
     * Generate an artistic QR PNG with the logo woven into the modules.
     *
     * @throws RuntimeException
     */
    public function generate(string $url, string $logoPath, string $outputPath): string
    {
        if (! is_file($logoPath)) {
            throw new RuntimeException("Logo file not found: {$logoPath}");
        }

        $python = $this->pythonBinary();
        $script = $this->scriptPath();

        if (! filled($python) || ! is_file($python)) {
            throw new RuntimeException('Artistic QR Python interpreter is not available.');
        }

        if (! is_file($script)) {
            throw new RuntimeException('Artistic QR script is missing.');
        }

        $directory = dirname($outputPath);
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException("Unable to create output directory: {$directory}");
        }

        $result = Process::timeout((int) config('services.artistic_qr.timeout', 90))
            ->run([
                $python,
                $script,
                '--url', $url,
                '--logo', $logoPath,
                '--output', $outputPath,
                '--version', (string) config('services.artistic_qr.version', 10),
                '--contrast', (string) config('services.artistic_qr.contrast', 1.15),
                '--brightness', (string) config('services.artistic_qr.brightness', 1.05),
            ]);

        if ($result->failed()) {
            Log::warning('Artistic QR generation failed.', [
                'exit_code' => $result->exitCode(),
                'error' => $result->errorOutput() ?: $result->output(),
            ]);

            throw new RuntimeException(
                trim($result->errorOutput() ?: $result->output()) ?: 'Artistic QR process failed.',
            );
        }

        $payload = $this->parseJsonPayload($result->output());
        if (! is_array($payload) || ($payload['ok'] ?? false) !== true || ! is_file($outputPath)) {
            throw new RuntimeException($payload['error'] ?? 'Artistic QR generation returned an invalid response.');
        }

        return $outputPath;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseJsonPayload(string $output): ?array
    {
        $lines = preg_split('/\R/', trim($output)) ?: [];
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = trim($lines[$i]);
            if ($line === '' || ! str_starts_with($line, '{')) {
                continue;
            }

            $decoded = json_decode($line, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $decoded = json_decode(trim($output), true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Prefer the project venv, then an explicit env path, then common binaries.
     */
    public function pythonBinary(): ?string
    {
        $configured = config('services.artistic_qr.python');
        if (filled($configured)) {
            $resolved = $this->resolveExecutable((string) $configured);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        $venvPython = DIRECTORY_SEPARATOR === '\\'
            ? base_path('scripts/artistic_qr/.venv/Scripts/python.exe')
            : base_path('scripts/artistic_qr/.venv/bin/python');

        if (is_file($venvPython)) {
            return $venvPython;
        }

        foreach (['python3', 'python'] as $candidate) {
            $resolved = $this->resolveExecutable($candidate);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return null;
    }

    public function scriptPath(): string
    {
        return (string) config(
            'services.artistic_qr.script',
            base_path('scripts/artistic_qr/generate.py'),
        );
    }

    private function resolveExecutable(string $binary): ?string
    {
        if (is_file($binary)) {
            return $binary;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            $where = Process::run(['where.exe', $binary]);
            if ($where->successful()) {
                $line = strtok($where->output(), "\r\n");

                return filled($line) && is_file($line) ? $line : null;
            }

            return null;
        }

        $which = Process::run(['which', $binary]);
        if ($which->successful()) {
            $line = trim($which->output());

            return filled($line) && is_file($line) ? $line : null;
        }

        return null;
    }
}
