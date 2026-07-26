<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class SetupArtisticQrCommand extends Command
{
    protected $signature = 'qr:setup-artistic';

    protected $description = 'Create the Python venv and install packages for Emblem artistic QR codes';

    public function handle(): int
    {
        $venvDir = base_path('scripts/artistic_qr/.venv');
        $requirements = base_path('scripts/artistic_qr/requirements.txt');

        if (! is_file($requirements)) {
            $this->error('Missing scripts/artistic_qr/requirements.txt');

            return self::FAILURE;
        }

        $python = $this->findBootstrapPython();
        if ($python === null) {
            $this->error('Python 3 was not found. Install Python 3.10+ and retry.');

            return self::FAILURE;
        }

        if (! is_dir($venvDir)) {
            $this->info('Creating virtualenv…');
            $create = Process::timeout(120)->run([$python, '-m', 'venv', $venvDir]);
            if ($create->failed()) {
                $this->error($create->errorOutput() ?: $create->output());

                return self::FAILURE;
            }
        }

        $venvPython = DIRECTORY_SEPARATOR === '\\'
            ? $venvDir.DIRECTORY_SEPARATOR.'Scripts'.DIRECTORY_SEPARATOR.'python.exe'
            : $venvDir.'/bin/python';

        if (! is_file($venvPython)) {
            $this->error("Virtualenv python missing at {$venvPython}");

            return self::FAILURE;
        }

        $this->info('Installing artistic QR packages…');
        $pip = Process::timeout(300)->run([
            $venvPython,
            '-m',
            'pip',
            'install',
            '-r',
            $requirements,
        ]);

        if ($pip->failed()) {
            $this->error($pip->errorOutput() ?: $pip->output());

            return self::FAILURE;
        }

        $this->info('Artistic QR toolchain is ready.');
        $this->line("Python: {$venvPython}");

        return self::SUCCESS;
    }

    private function findBootstrapPython(): ?string
    {
        foreach (['python3', 'python', 'py'] as $candidate) {
            if (DIRECTORY_SEPARATOR === '\\') {
                $where = Process::run(['where.exe', $candidate]);
                if (! $where->successful()) {
                    continue;
                }
                $line = strtok($where->output(), "\r\n");
                if (filled($line) && is_file($line)) {
                    return $line;
                }
            } else {
                $which = Process::run(['which', $candidate]);
                if ($which->successful()) {
                    $line = trim($which->output());
                    if (filled($line) && is_file($line)) {
                        return $line;
                    }
                }
            }
        }

        return null;
    }
}
