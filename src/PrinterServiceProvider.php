<?php

namespace TsaiKoga\PerformancePrinter;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\ServiceProvider;

use TsaiKoga\PerformancePrinter\Printer\PrinterManager;

class PrinterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (config('app.env') === 'local') {
            if (!file_exists(config_path('performance_printer.php'))) {
                $this->publishes([
                    dirname(__DIR__).'/config/PerformancePrinter.php' => config_path('PerformancePrinter.php'),
                ], 'config');
            }
            $this->mergeConfigFrom(dirname(__DIR__).'/config/PerformancePrinter.php', 'performance_printer');
            if (config('performance_printer')['enable']) {
                foreach (config('performance_printer')['query']['connections'] as $conn) {
                    DB::connection($conn)->enableQueryLog();
                }
                Event::listen(RequestHandled::class, function ($event) {
                    $printer_manager = new PrinterManager(config('performance_printer'));
                    $printer_manager->print($event);
                });
            }
        }
    }
}
