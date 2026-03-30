<?php

use App\Console\Commands\CheckExpiredBatches;
use App\Console\Commands\SendDailySalesReport;
use Illuminate\Support\Facades\Schedule;

Schedule::command(CheckExpiredBatches::class)->dailyAt('07:00');
Schedule::command(SendDailySalesReport::class)->dailyAt('21:00');
