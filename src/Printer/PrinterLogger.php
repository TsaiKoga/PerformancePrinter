<?php
/**
 * @package   PerformancePrinter
 * @author    TsaiKoga
 * @version   1.1.1
 *
 */
namespace TsaiKoga\PerformancePrinter\Printer;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class PrinterLogger extends Logger
{

    /**
     * Create a new command instance.
     *
     * @param string $logger_name
     */
    public function __construct($logger_name="PrinterPerformance")
    {
        parent::__construct($logger_name);
    }

    /**
     * Set formatter to Logger
     *
     * @param string $filepath
     * @return Monolog\Handler\StreamHandler $debug_handler
     */
    public function setFormatter($filepath)
    {
        $formatter = new LineFormatter(
            "%message% %context% %extra%\n", // Format of message in log
            null,                            // Datetime format
            true,                            // allowInlineLineBreaks option, default false
            true                             // ignoreEmptyContextAndExtra option, default false
        );
        $debug_handler = new StreamHandler($filepath, Logger::DEBUG);
        $debug_handler->setFormatter($formatter);
        return $debug_handler;
    }

    /**
     * build table array for rendering
     *
     * @param array $header
     * @param array $content
     * @return array
     */
    public function buildTable($header, $content)
    {
        $cols_count  = count($header);
        $rows_count  = count($content);
        $data        = array_merge([$header], $content);
        $space_count = 2;

        $result      = array();
        $max_len     = $this->getMaxLenOfRows($cols_count, $rows_count, $data);

        foreach (range(0, $rows_count) as $rows_index) {
            // For header
            if ($rows_index === 0) {
                foreach ($data[$rows_index] as $i => $item) {
                    $index = $rows_index;
                    $strlen = strlen($item);
                    // concat string and convert it to something like this: +-------+
                    if ($i === 0){
                        $result[$index][$i] = '+' . str_repeat('-', ($max_len[$i] + $space_count)) . '+';
                        $index += 1;
                        $space_suffix = str_repeat(' ', ($max_len[$i] - $strlen));
                        $result[$index][$i] = '| ' . $item . $space_suffix . ' |';
                        $index += 1;
                        $result[$index][$i] = '+' . str_repeat('-', ($max_len[$i] + $space_count)) . '+';
                        $index += 1;
                    } else {
                        $result[$index][$i] = '' . str_repeat('-', ($max_len[$i] + $space_count)) . '+';
                        $index += 1;
                        $space_suffix = str_repeat(' ', ($max_len[$i] - $strlen));
                        $result[$index][$i] = ' ' . $item . $space_suffix . ' |';
                        $index += 1;
                        $result[$index][$i] = '' . str_repeat('-', ($max_len[$i] + $space_count)) . '+';
                        $index += 1;
                    }
                }
            } else {
                // Add For Content
                foreach ($data[$rows_index] as $i => $item) {
                    $index = $rows_index + 3;
                    $strlen = strlen($item);
                    if ($i === 0) {
                        $space_suffix = str_repeat(' ', ($max_len[$i] - $strlen));
                        $result[$index][$i] = '| ' . $item . $space_suffix . ' |';
                        $index += 1;
                    } else {
                        $space_suffix = str_repeat(' ', ($max_len[$i] - $strlen));
                        $result[$index][$i] = ' ' . $item . $space_suffix . ' |';
                        $index += 1;
                    }
                }
            }

            // Add bottom line
            if ($rows_index >= $rows_count) {
                foreach ($data[$rows_index] as $i => $item) {
                    if ($i === 1) {
                        $result[$index][$i] = '+' . str_repeat('-', ($max_len[$i] + $space_count)) . '+';
                    } else {
                        $result[$index][$i] = '' . str_repeat('-', ($max_len[$i] + $space_count)) . '+';
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get max length of each field's value
     *
     * @param integer $cols_count
     * @param integer $rows_count
     * @param array   $data
     * @return array
     */
    public function getMaxLenOfRows($cols_count, $rows_count, $data)
    {
        $length = array();
        foreach (range(0, $cols_count - 1) as $col_index) {
            $max_len = 0;
            foreach(range(0, $rows_count) as $row_index) {
                $str_len = strlen($data[$row_index][$col_index]);
                if ($max_len <= $str_len) $max_len = $str_len;
            }
            $length[$col_index] = $max_len;
        }
        return $length;
    }

    /**
     * render table
     *
     * @param array $header
     * @param array $content
     * @return void
     */
    public function renderTable($header, $content)
    {
        $result = $this->buildTable($header, $content);
        foreach ($result as $i => $items) {
            $this->info(implode('', $items));
        }
    }
}
