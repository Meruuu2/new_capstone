<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use App\Models\FormSubmission;

Schedule::command('bookings:normalize-and-complete')->hourly()->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('payment-receipts:clean-orphans {--days=30} {--dry-run}', function () {
    $days = filter_var($this->option('days'), FILTER_VALIDATE_INT);

    if ($days === false || $days < 1) {
        $this->error('The --days option must be a positive integer.');
        return 1;
    }

    $disk = Storage::disk('payment_receipts');
    if (! is_dir(config('filesystems.disks.payment_receipts.root'))) {
        $this->info('The receipt directory does not exist; there are no orphaned files to remove.');
        return 0;
    }

    $referencedPaths = [];
    FormSubmission::query()
        ->select(['payment_attachment', 'payload'])
        ->chunk(500, function ($submissions) use (&$referencedPaths) {
            foreach ($submissions as $submission) {
                if ($submission->payment_attachment) {
                    $referencedPaths[$submission->payment_attachment] = true;
                }

                $attachments = $submission->payload['attachments'] ?? [];
                foreach (is_array($attachments) ? $attachments : [] as $path) {
                    if (is_string($path)) {
                        $referencedPaths[$path] = true;
                    }
                }
            }
        });
    $cutoff = now()->subDays($days)->getTimestamp();
    $removed = 0;

    foreach ($disk->allFiles() as $path) {
        if (isset($referencedPaths[$path]) || $disk->lastModified($path) >= $cutoff) {
            continue;
        }

        if ($this->option('dry-run')) {
            $this->line("Would remove {$path}");
            continue;
        }

        if (! $disk->delete($path)) {
            $this->error("Unable to remove orphaned upload: {$path}");
            return 1;
        }

        $removed++;
    }

    $this->info($this->option('dry-run')
        ? "Dry run complete. Use the command without --dry-run to delete orphaned files older than {$days} days."
        : "Removed {$removed} orphaned upload(s) older than {$days} days.");

    return 0;
})->purpose('Remove old local facility uploads not referenced by a submission');
 