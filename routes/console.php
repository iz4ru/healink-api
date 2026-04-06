<?php

use App\Console\Commands\CheckExpiredBatches;
use App\Console\Commands\SendDailySalesReport;
use Illuminate\Support\Facades\Schedule;

Schedule::command(CheckExpiredBatches::class)
    ->dailyAt('07:00')
    ->withoutOverlapping(1440)
    ->onOneServer();

Schedule::command(SendDailySalesReport::class)
    ->dailyAt('21:00')
    ->withoutOverlapping(1440)
    ->onOneServer();

Schedule::command('sanctum:prune-expired --hours=0')
    ->dailyAt('00:00')
    ->withoutOverlapping(60)
    ->onOneServer();
