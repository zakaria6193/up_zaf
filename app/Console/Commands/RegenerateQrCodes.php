<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('qr:regenerate')]
#[Description('Regenerate QR codes for all businesses')]
class RegenerateQrCodes extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Regenerating QR codes for all businesses...');

        $businesses = Business::all();
        $bar = $this->output->createProgressBar($businesses->count());
        $bar->start();

        foreach ($businesses as $business) {
            $business->generateQrCode();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully regenerated {$businesses->count()} QR codes!");

        return self::SUCCESS;
    }
}
