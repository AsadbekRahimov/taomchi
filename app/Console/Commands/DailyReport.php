<?php

namespace App\Console\Commands;

use App\Services\ReportService;
use App\Services\SendMessageService;
use Illuminate\Console\Command;

class DailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get everyday report information';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = ['start' => date('Y-m-d'), 'end' => date('Y-m-d')];
        ReportService::allReport($date, 'store');
        SendMessageService::sendReport();
    }
}
