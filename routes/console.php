<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sapu file upload sementara (temp/*) yang gagal/tidak diproses worker.
Schedule::command('uploads:cleanup-temp')->daily();
