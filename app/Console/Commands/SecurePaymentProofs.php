<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

#[Signature('payments:secure-proofs {--dry-run : Show legacy proof files without moving them}')]
#[Description('Move legacy payment proofs from public storage to private storage')]
class SecurePaymentProofs extends Command
{
    public function handle(): int
    {
        $publicDisk = Storage::disk('public');
        $privateDisk = Storage::disk('local');
        $dryRun = $this->option('dry-run');
        $migrated = 0;
        $alreadyPrivate = 0;
        $processedPaths = [];

        foreach (Payment::query()
            ->whereNotNull('proof_file')
            ->where('proof_file', '!=', '')
            ->select(['id', 'proof_file'])
            ->lazyById(100) as $payment) {
            $path = $payment->proof_file;

            if (isset($processedPaths[$path])) {
                continue;
            }

            $processedPaths[$path] = true;
            $result = $this->secureProofFile($path, $publicDisk, $privateDisk, $dryRun);
            if ($result === false) {
                return self::FAILURE;
            }

            if ($result === 'already-private') {
                $alreadyPrivate++;
            } else {
                $migrated++;
            }
        }

        foreach ($publicDisk->allFiles('payments') as $path) {
            if (isset($processedPaths[$path])) {
                continue;
            }

            $result = $this->secureProofFile($path, $publicDisk, $privateDisk, $dryRun);
            if ($result === false) {
                return self::FAILURE;
            }

            $migrated++;
        }

        $this->info($dryRun
            ? "{$migrated} legacy payment proof file(s) would be secured; {$alreadyPrivate} already private; no files were changed."
            : "{$migrated} payment proof file(s) secured; {$alreadyPrivate} already private.");

        return self::SUCCESS;
    }

    private function secureProofFile(
        string $path,
        FilesystemAdapter $publicDisk,
        FilesystemAdapter $privateDisk,
        bool $dryRun
    ): string|false {
        if (! str_starts_with($path, 'payments/') || str_contains($path, '..') || str_contains($path, '\\')) {
            $this->error("Invalid payment proof path: {$path}");

            return false;
        }

        if (! $publicDisk->exists($path)) {
            if ($privateDisk->exists($path)) {
                return 'already-private';
            }

            $this->error("Payment proof file is missing from both disks: {$path}");

            return false;
        }

        $publicHash = hash_file('sha256', $publicDisk->path($path));
        if ($publicHash === false) {
            $this->error("Could not read payment proof file: {$path}");

            return false;
        }

        if ($privateDisk->exists($path)) {
            $privateHash = hash_file('sha256', $privateDisk->path($path));
            if ($privateHash === false || ! hash_equals($publicHash, $privateHash)) {
                $this->error("Conflicting public and private payment proof files: {$path}");

                return false;
            }
        } elseif (! $dryRun) {
            $stream = $publicDisk->readStream($path);
            if (! is_resource($stream)) {
                $this->error("Could not read payment proof file: {$path}");

                return false;
            }

            try {
                $stored = $privateDisk->put($path, $stream);
            } finally {
                fclose($stream);
            }

            if (! $stored) {
                $this->error("Could not copy payment proof file to private storage: {$path}");

                return false;
            }

            $privateHash = hash_file('sha256', $privateDisk->path($path));
            if ($privateHash === false || ! hash_equals($publicHash, $privateHash)) {
                $this->error("Payment proof file copy could not be verified: {$path}");

                return false;
            }
        }

        if ($dryRun) {
            $this->line("Would secure {$path}");

            return 'would-secure';
        }

        if (! $publicDisk->delete($path) || $publicDisk->exists($path)) {
            $this->error("Could not remove the public payment proof file: {$path}");

            return false;
        }

        return 'secured';
    }
}
