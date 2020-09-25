<?php
return [
    // print request message
    'request' => true,

    // print response datas
    'response' => false,

    // included files count
    'included_files_count' => true,

    // print table Style
    'table_style' => 'default',

    // language
    'lang' => 'cn',

    'query' => [
        // print raw sql
        'raw_sql' => true,

        // print explain sql
        'explain' => true,

        // Same queries with different bindings are superimposed
        'unique_query' => true,

        // The connections
        'connections' => ['mysql'],
    ],

    // enable this package
    'enable' => true,

    // log
    'log' => [
        // is logging enable?
        'enable' => false,

        // The path that the log file stored
        'filepath' => '/tmp/performance_printer.log',
    ],
];
