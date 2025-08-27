<?php

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function() {
    $count = DB::table('stock_movements')->where('created_at', '<', now()->subDays(90))->delete();

    DB::table('inventory_adjustments')->where('reason', 'like', '%hello%')->delete();
    Notification::make("Stock Movement Cleanup")
    ->title("Stock Movement Cleanup")
    ->body("Deleted $count stock movement records older than 90 days.")
    ->sendToDatabase(auth()->user());
})->everyMinute();

