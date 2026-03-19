<?php

namespace App\Console\Commands;

use App\Services\DataAnonymizationService;
use Illuminate\Console\Command;

class AnonymizePersonalData extends Command
{
    protected $signature = 'libver:anonymize-data {--batch=200 : Number of records to process per entity type}';

    protected $description = 'Remove or anonymize personal data after the retention window lapses.';

    public function handle(DataAnonymizationService $service): int
    {
        $batchSize = (int) $this->option('batch');
        $results = $service->run($batchSize);

        $this->info(sprintf(
            'Anonymized %d users, %d children; flagged %d registrations.',
            $results['users'],
            $results['children'],
            $results['registrations']
        ));

        return Command::SUCCESS;
    }
}
