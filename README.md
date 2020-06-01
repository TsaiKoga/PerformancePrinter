<h1 align="center">Performance Printer</h1>

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
