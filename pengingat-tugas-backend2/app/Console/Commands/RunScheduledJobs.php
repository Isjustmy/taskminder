<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunScheduledJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:run-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the Laravel scheduler in a loop to simulate cron jobs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Running scheduled jobs every minute. Press Ctrl+C to stop.');

        while (true) {
            // Run the Laravel scheduler
            Artisan::call('schedule:run');

            // Output the result
            $this->info('Scheduled jobs run at ' . now());

            // Sleep for 60 seconds
            sleep(60);
        }

        return 0;
    }
}
