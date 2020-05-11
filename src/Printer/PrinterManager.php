<?php
/**
 * @package   PerformancePrinter
 * @author    TsaiKoga
 * @version   1.0.0
 *
 */
namespace TsaiKoga\PerformancePrinter\Printer;

use TsaiKoga\PerformancePrinter\Printer\Printer;
use TsaiKoga\PerformancePrinter\Query\QueryLog;

class PrinterManager
{
    /**
     * configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new instance.
     *
     * @param  array    $options
     */
    public function __construct(array $options)
    {
        $this->options  = $options;
    }

    /**
     * Print the message:
     *
     * - request
     * - included files count
     * - query count
     * - raw sql
     * - explain sql
     * - response
     *
     * @param Illuminate\Support\Facades\Event
     * @return void
     */
    public function print($event)
    {
        $querylog = new QueryLog($this->options['query']);
        $printer  = new Printer($event->request, $event->response, $querylog);
        $printer->setTableStyle($this->options['table_style']);
        $printer->setLang($this->options['lang']);

        if ($this->options['request']) {
            $printer->outputRequest();
            $printer->outputBlankLine(1);
        }

        if ($this->options['included_files_count']) {
            $printer->outputFilesCount();
            $printer->outputBlankLine(1);
        }

        $printer->outputQueryCount();

        $printer->outputQueries();

        if ($this->options['response']) {
            $printer->outputResponse();
        }
        $printer->outputBlankLine(3);
    }
}
