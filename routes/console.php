<?php

use App\Console\Commands\CheckExpiredBatches;
use Illuminate\Support\Facades\Schedule;

Schedule::command(CheckExpiredBatches::class)->dailyAt('07:00');
