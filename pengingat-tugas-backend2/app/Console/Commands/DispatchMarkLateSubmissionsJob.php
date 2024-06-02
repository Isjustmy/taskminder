<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\MarkLateSubmissionsJob;

class DispatchMarkLateSubmissionsJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:dispatch-mark-late-submissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch the job to mark late submissions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        MarkLateSubmissionsJob::dispatch();
        $this->info('Job dispatched successfully.');
        return 0;
    }
}
