<?php

use App\Jobs\MarkLateRequisitions;
use App\Jobs\SendAbandonedCartEmails;
use App\Jobs\SendRequisitionReminder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reminder: envia email a quem tem devolução amanhã.
// Em produção: adicionar ao crontab: * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
Schedule::job(new SendRequisitionReminder)->dailyAt('09:00');
Schedule::job(new MarkLateRequisitions)->dailyAt('00:30');
Schedule::job(new SendAbandonedCartEmails)->everyFifteenMinutes();
Schedule::command('orders:release-expired-reservations')->everyMinute();