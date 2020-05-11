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
        'connections' => ['mysql', 'mysql_online', 'mysql_infzm_x'],
    ],
];
