<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('uploads:clear')
    ->dailyAt('03:00')       // Força a rodar SÓ às 3 da manhã
    ->runInBackground()      // Libera a CPU imediatamente
    ->withoutOverlapping();  // Impede que acumule processos se travar