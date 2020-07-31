<?php
/**
 * @package   PerformancePrinter
 * @author    TsaiKoga
 * @version   1.0.0
 *
 */
namespace TsaiKoga\PerformancePrinter\Query;

use Illuminate\Support\Facades\DB;

class QueryLog
{
    /**
     * If the queries are same, do they need to be collapsed
     *
     * @var boolean
     */
    protected $is_unique;

    /**
     * To explain sql or not
     *
     * @var boolean
     */
    protected $is_explain;

    /**
     * Connections [the connection names in config/database.php]
     *
     * @var array
     */
    protected $connections;

    /**
     * The querylogs array
     *
     * @var array
     * @example [
     *            [
     *              'query' => 'select * from `posts` where `id` = ?',
     *              'bindings' => [1],
     *              'time' => 79.5,
     *              'count' => 1,
     *              'sql' => 'select * from `posts` where `id` = 1'
     *              'connection' => 'mysql',
     *              'explain' => [],
     *            ],[
     *              'query' => 'select * from categories where categories.id in (?,?)'
     *              ...
     *            ]
     *          ]
     */
    protected $querylogs;

    /**
     * Create a new QueryLog instance
     */
    public function __construct($options = [])
    {
        $this->is_unique   = $options['is_unique'] ?? true;
        $this->is_explain  = $options['explain'] ?? true;
        $this->connections = $options['connections'] ?? ['mysql'];
        $this->setQueryLogs();
    }

    /**
     * Set Query Logs
     *
     * @return TsaiKoga\PerformancePrinter\Query\QueryLog $this
     */
    public function setQueryLogs()
    {
        $logs = array();
        foreach ($this->connections as $conn) {
            $tmp = array_map(function($log) use ($conn) {
                $log['connection'] = $conn;
                return $log;
            }, DB::connection($conn)->getQueryLog());
            $logs = array_merge($logs, $tmp);
        }
        $this->querylogs = $this->is_unique? $this->getCollapsedQueryLogs($logs) :
                                             $this->getAllQueryLogs($logs);
        return $this;
    }

    /**
     * Get query logs
     *
     * @return array
     */
    public function getQueryLogs()
    {
        return $this->querylogs;
    }

    /**
     * Get queries total
     *
     * @return integer
     */
    public function getQueriesTotal()
    {
        $total = 0;
        foreach ($this->querylogs as $log) {
            $total += $log['count'];
        }
        return $total;
    }

    /**
     * Get queries total running time
     *
     * @return float
     */
    public function getQueriesRunningTime()
    {
        $time = 0;
        foreach ($this->querylogs as $log) {
            $time += $log['time'];
        }
        return $time;
    }

    /**
     * get query logs that the same query will be collapsed
     *
     * @param array $logs
     * @return array
     */
    public function getCollapsedQueryLogs($logs)
    {
        $tmp_logs = array();
        foreach ($logs as $index => $log) {
            // Fold the same queries
            if (in_array($log['query'], array_keys($tmp_logs))) {
                $tmp_logs[$log['query']]['time']  += $log['time'];
                $tmp_logs[$log['query']]['count'] += 1;
                continue;
            } else {
                $sql = $this->getSqlWithBindings($log['query'], $log['bindings']);
                $tmp_logs[$log['query']] = array(
                    'time'       => $log['time'],
                    'query'      => $log['query'],
                    'bindings'   => $log['bindings'],
                    'connection' => $log['connection'],
                    'sql'        => $sql,
                    'explain'    => $this->getExplainArray($sql, $log['connection']),
                    'count'      => 1
                );
            }
        }
        return array_values($tmp_logs);
    }

    /**
     * get all query logs that the same query will not be collapsed
     *
     * @param array $logs
     * @return array
     */
    public function getAllQueryLogs($logs)
    {
        $querylogs = array();
        foreach ($logs as $index => $log) {
            $part = array(
                'connection' => $conn,
                'sql'     => $this->getSqlWithBindings($log['query'], $log['bindings']),
                'explain' => $this->getExplainArray($sql, $log['connection']),
                'count'   => 1
            );
            $querylogs[$index] = array_merge($log, $part);
        }
        return $querylogs;
    }

    /**
     * Get raw sql with bindings
     *
     * @param string $query
     * @param array $bindings
     * @return string
     */
    protected function getSqlWithBindings($query, $bindings)
    {
        $bindings = array_map(function($q) {
                        $result = $q;
                        if (is_string($q) && !in_array(mb_strtolower($q), ["true", "false"])) $result = "\"".str_replace('"', '\"', $q)."\"";
                        if (in_array(mb_strtolower($q), ["true", "false"])) $result = mb_strtolower($q);
                        if (is_null($q)) $result = "null";
                        if (is_object($q) && (new \ReflectionClass($q))->getShortName() === "Carbon") $result = "\"{$q->format('Y-m-d H:i:s')}\"";
                        return $result;
                    }, $bindings);

        if (preg_match('/\?/', $query)) {
            // deal with query with question mark
            $sql_with_bindings = str_replace_array('?', $bindings, $query);
        } else {
            // deal with query with symbol variable
            $sql_with_bindings = $query;
            foreach ($bindings as $key => $value) {
                $sql_with_bindings = preg_replace('/\s(:'.$key.')/', $value, $sql_with_bindings);
            }
        }
        return $sql_with_bindings;
    }

    /**
     * Explain SQL
     *
     * @param string $sql
     * @param string $conn
     * @return array
     */
    public function explainSql($sql, $conn)
    {
        $items = DB::connection($conn)->select("explain ".$sql);
        return $items;
    }

    /**
     * Get explain result, split them to header and contents
     *
     * @param string $sql
     * @return array
     */
    protected function getExplainArray($sql, $conn)
    {
        $results = array();
        if ($this->is_explain) {
            $result  = array();
            foreach($this->explainSql($sql, $conn) as $i => $item) {
                $arr = get_object_vars($item);
                if ($i == 0) $results[0] = array_keys($arr);
                $val_arr = array_values($arr);
                $result []= $val_arr;
            }
            $results[1] = $result;
        } else {
            $results = array(null, null);
        }
        return $results;
    }
}
