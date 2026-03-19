<?php

namespace App\Console\Commands;

use App\Services\WaitlistService;
use Illuminate\Console\Command;

class ExpireWaitlistOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'libver:expire-waitlist-offers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire waitlist offers that have passed their deadline and promote the next person';

    /**
     * Execute the console command.
     */
    public function handle(WaitlistService $waitlistService): int
    {
        $count = $waitlistService->expireOffers();
        $this->info("Expired {$count} waitlist offers.");

        return Command::SUCCESS;
    }
}
