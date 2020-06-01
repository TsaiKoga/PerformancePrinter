<h1 align="center">Performance Printer</h1>

<p align="center">
<a link="https://packagist.org/packages/tsaikoga/performance-printer" style="text-decoration:none;">
  <img src="https://img.shields.io/badge/unstable-dev--master-blue" alt="Unstable">
</a>
<a link="https://packagist.org/packages/tsaikoga/performance-printer" style="text-decoration:none;">
  <img src="https://img.shields.io/badge/license-MIT-orange.svg" alt="License">
</a>
<a link="https://packagist.org/packages/tsaikoga/performance-printer" style="text-decoration:none;">
  <img src="https://img.shields.io/badge/laravel-5.5%2B-green" alt="Laravel">
</a>
</p>

Performance Printer is a laravel package to print each requests' performance infomation for development.


**require** : Laravel/framework 5.5+

## Features
- Request
> Display request method, request path and request body.
- Files
> The count of included files
- Query
> Each raw SQL that be generated by request and the times it costs.
>
> The explain of SQL
>
> The SQL count of request
- Response
> The response datas and the times it costs.

## Usage
1. Install the package for development environment:
```bash
composer require tsaikoga/performance-printer:dev-master --dev
```

You can add custom configuration for the printer.

2. Create a file `config/performance_printer.php`:
```php
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

    // language: Only support English(en) and Chinese(cn) now
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
];
```

3. In `.env` file, set `local` to the application environment:
```bash
APP_ENV=local
```

4. Run the server:
```bash
php artisan serve
```

## Preview
Printing Result:
```bash
[ POST ] /api/user/login
[ Content-Type ] :  application/json
{
	"user": "12345678910",
	"password": "b49f16999ce2a0a0df9b6e0e66bd4f32"
}

[ Included Files Count ]  379

[ Total ] 2 queries and ran for 16.39 ms.
[ SQL ran for 11.56 ms ] RAW SQL: select * from `users` where `phone` = "12345678910" or `email` = "12345678910" limit 1

+----+-------------+-------+------------+------+--------------------+-----+---------+-----+------+----------+-------------+
| id | select_type | table | partitions | type | possible_keys      | key | key_len | ref | rows | filtered | Extra       |
+----+-------------+-------+------------+------+--------------------+-----+---------+-----+------+----------+-------------+
| 1  | SIMPLE      | users |            | ALL  | users_email_unique |     |         |     | 1770 | 19       | Using where |
+----+-------------+-------+------------+------+--------------------+-----+---------+-----+------+----------+-------------+

[ SQL ran for 4.83 ms ] RAW SQL: insert into `ticket` (`user_id`, `expire_time`, `ticket`, `updated_at`, `created_at`) values (1, "2020-06-08 17:20:39", "08c14bace9bdfd9dbe3558adba463d1f198", "2020-06-01 17:20:39", "2020-06-01 17:20:39")

+----+-------------+--------+------------+------+---------------+-----+---------+-----+------+----------+-------+
| id | select_type | table  | partitions | type | possible_keys | key | key_len | ref | rows | filtered | Extra |
+----+-------------+--------+------------+------+---------------+-----+---------+-----+------+----------+-------+
| 1  | INSERT      | ticket |            | ALL  |               |     |         |     |      |          |       |
+----+-------------+--------+------------+------+---------------+-----+---------+-----+------+----------+-------+

[ Response Load 88.63 ms] {"code":200,"data":{"user":{"id":1,"username":"koga","phone":"12345678910","email":"koga@gmail.com","created_at":"2020-06-01 14:45:09","updated_at":"2019-06-01 14:45:09","loginname":"koga","from":"api","regip":null,"regdate":null,"ticket":"08c14bace9bdfd9dbe3558adba463d1f198"}},"msg":"\u767b\u5f55\u6210\u529f\uff01"}
```
