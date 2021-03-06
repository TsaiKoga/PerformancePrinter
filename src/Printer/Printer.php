<?php
/**
 * @package   PerformancePrinter
 * @author    TsaiKoga
 * @version   1.1.1
 *
 */
namespace TsaiKoga\PerformancePrinter\Printer;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use TsaiKoga\PerformancePrinter\Printer\PrinterLogger;

class Printer extends ConsoleOutput
{

    /**
     * The request of action
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The response of action
     *
     * @var \Illuminate\Http\Response
     */
    protected $response;

    /**
     * The querylog instance
     *
     * @var \TsaiKoga\PrinterPerformance\Query\QueryLog
     */
    protected $querylog;

    /**
     * The table instance
     *
     * @var Symfony\Component\Console\Helper\Table
     */
    protected $table;

    /**
     * The expense time
     *
     * @var float
     */
    protected $expense;

    /**
     * The language
     *
     * @var string
     */
    protected $lang;


    /**
     * is logging enabled
     *
     * @var bool
     */
    protected $is_logging_enabled;

    /**
     * logger
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * Create a new Printer instance.
     *
     * @param  Illuminate\Http\Request $request
     * @param  Illuminate\Http\Response $response
     * @param  TsaiKoga\PrinterPerformance\Query\QueryLog $querylog
     */
    function __construct($request, $response, $querylog)
    {
        parent::__construct();

        $this->setTable();
        $this->setLang();
        $this->request = $request;
        $this->response = $response;
        $this->querylog = $querylog;
        $this->expense = round((microtime(true) - $request->server()['REQUEST_TIME_FLOAT']) *1000, 2);
    }

    /**
     * Set table
     *
     * @return TsaiKoga\PerformancePrinter\Printer\Printer $this
     */
    protected function setTable()
    {
        $table = new Table($this);
        $table->setStyle('default');
        $this->table = $table;
        return $this;
    }

    /**
     * Set table Style
     *
     * @param string $style
     * @return TsaiKoga\PerformancePrinter\Printer\Printer $this
     */
    public function setTableStyle($style)
    {
        $this->table->setStyle($style);
        return $this;
    }

    /**
     * Set Language
     *
     * @param string $lang
     * @return TsaiKoga\PerformancePrinter\Printer\Printer $this
     */
    public function setLang($lang = 'en')
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * Enable logging
     *
     * @param string $filepath
     * @return TsaiKoga\PerformancePrinter\Printer\Printer $this
     */
    public function enableLogging($filepath)
    {
        $this->is_logging_enabled = true;
        $logger = new PrinterLogger();
        $logger->pushHandler($logger->setFormatter($filepath));
        $this->logger = $logger;
        return $this;

    }

    /**
     * Get table
     *
     * @return Symfony\Component\Console\Helper\Table $table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get Language
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Output table
     *
     * @param Symfony\Component\Console\Helper\Table $table
     * @param array $header
     * @param array $content
     * @return void
     */
    protected function outputTable($table, $header, $content)
    {
        if (!empty($header) && !empty($content)) {
            $table->setHeaders($header);
            $table->setRows($content);
            $table->render();
        }

        if ($this->is_logging_enabled) {
            $this->logger->renderTable($header, $content);
        }
    }

    /**
     * Output Request
     *
     * @return void
     */
    public function outputRequest()
    {
        $request = $this->request->server();
        $this->outputMsg("<question>[ {$request['REQUEST_METHOD']} ]</question> <info>{$request['REQUEST_URI']}</info>");
        if (in_array($request['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH'])) {
            if (isset($request['HTTP_CONTENT_TYPE'])) $this->outputMsg("<question>[ Content-Type ]</question> :  {$request['HTTP_CONTENT_TYPE']} ");
            $this->outputMsg($this->request->getContent());
        }
    }

    /**
     * Output the count of included files
     *
     * @return void
     */
    public function outputFilesCount()
    {
        $included_files_count = count(get_included_files());
        if ($this->lang === 'en') {
            $this->outputMsg("<question>[ Included Files Count ] </question> $included_files_count");
        } else {
            $this->outputMsg("<question>[ 加载文件数量 ] </question> $included_files_count");
        }
    }

    /**
     * Output Query Count
     *
     * @return void
     */
    public function outputQueryCount()
    {
        $total = $this->querylog->getQueriesTotal();
        $times = $this->querylog->getQueriesRunningTime();
        if ($this->lang === 'en') {
            $this->outputMsg("<question>[ Total ]</question> $total queries and ran for $times ms.");
        } else {
            $this->outputMsg("<question>[ 总计 ]</question> $total 条语句运行了 $times 毫秒。");
        }
    }

    /**
     * Output Queries' infomation
     *
     * @return void
     */
    public function outputQueries()
    {
        foreach ($this->querylog->getQueryLogs() as $index => $log) {
            $this->outputRawSql($log);
            $this->outputExplainSql($log);
        }
    }

    /**
     * Output raw sql
     *
     * @return void
     */
    protected function outputRawSql($log)
    {
        if ($this->lang === 'en') {
            if ($log['count'] > 1) {
                $this->outputMsg("<info>>>>></info> There are a total of {$log['count']} same queries with different bindings:");
                $this->outputMsg("<question>[ These queries ran for {$log['time']} ms totally ]</question> RAW SQL: <info>{$log['sql']}</info>");
            } else {
                $this->outputMsg("<question>[ SQL ran for {$log['time']} ms ]</question> RAW SQL: <info>{$log['sql']}</info>");
            }
        } else {
            if ($log['count'] > 1) {
                $this->outputMsg("<info>>>>></info> 总共有 {$log['count']} 条相同的查询只是查询参数不同:");
                $this->outputMsg("<question>[ 这些查询总共运行 {$log['time']} 毫秒 ]</question> 原生 SQL: <info>{$log['sql']}</info>");
            } else {
                $this->outputMsg("<question>[ SQL 运行了 {$log['time']} 毫秒 ]</question> 原生 SQL: <info>{$log['sql']}</info>");
            }
        }

        $this->outputBlankLine(1);
    }

    /**
     * Output explain sql
     *
     * @return void
     */
    protected function outputExplainSql($log)
    {
        $this->outputTable($this->table, ...$log['explain']);
        $this->outputBlankLine(1);
    }

    /**
     * Output Response with expenditure
     *
     * @return void
     */
    public function outputResponse()
    {
        $response = $this->response->content();
        if ($this->lang === 'en') {
            $this->outputMsg("<question>[ Response Load $this->expense ms]</question> $response");
        } else {
            $this->outputMsg("<question>[ 响应时间 $this->expense 毫秒 ]</question> $response");
        }
    }

    /**
     * Output blank line
     *
     * @param integer $num
     * @return void
     */
    public function outputBlankLine($num)
    {
        foreach(range(1, $num) as $i) {
            $this->writeln('');
            if ($this->is_logging_enabled) {
                $this->logger->info('');
            }
        }
    }

    /**
     * output message
     *
     * @param string $msg
     * @return void
     */
    public function outputMsg($msg)
    {
        $this->writeln($msg);
        if ($this->is_logging_enabled) {
            $this->logger->info(strip_tags($msg));
        }
    }

    /**
     * TODO: Output sql index suggestion
     *
     * @param string $sql
     * @param string $query
     * @param array $bindings
     * @param array $explains
     * @return void
     */
    protected function outputIndexSuggestion($sql, $query, $bindings, $explains)
    {
    }
}
