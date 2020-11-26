# laravel-sql-migrations

![tests](https://github.com/pmatseykanets/laravel-sql-migrations/workflows/tests/badge.svg)

Write your Laravel migrations in plain SQL.

## Contents

- [Why](#why)
- [Installation](#installation)
- [Usage](#usage)
  - [Make SQL Migrations](#make-sql-migrations)
  - [Run SQL Migrations](#run-sql-migrations)
- [Example Projects](#example-projects)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Why

Don't get me wrong, the Laravel's [`SchemaBuilder`](https://laravel.com/docs/master/migrations) is absolutely great and you can get a lot of millage out of it.

But there are cases when it's just standing in the way. Below are just a few examples where `SchemaBuilder` falls short.

### Using additional / richer data types

I.e. if you're using [PostgreSQL](https://www.postgresql.org/) and you want to use a case insensitive data type for string/text data you may consider `CITEXT`. This means that we have to resort to a hack like this

```php
class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrement('id');
            $table->string('email')->unique();
            // ...
        });

        DB::unprepared('ALTER TABLE users ALTER COLUMN email TYPE CITEXT');
    }
}
```

instead of just

```sql
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    email CITEXT UNIQUE,
    ...
);
```

Of course there are plenty of other data types (i.e. [Range](https://www.postgresql.org/docs/current/static/rangetypes.html) or [Text Search](https://www.postgresql.org/docs/current/static/datatype-textsearch.html) data types in PostgreSQL) that might be very useful but `SchemaBuilder` is unaware of and never will be. 

### Managing stored functions, procedures and triggers

This is a big one, especially if you're still using reverse (`down()`) migrations. This means that you need to cram both new and old source code of a function/procedure/trigger in `up()` and `down()` methods of your migration file and keep them in string variables which doesn't help with readability/maintainability.

Even with [`heredoc` / `nowdoc`](https://secure.php.net/manual/en/language.types.string.php) syntax in `php` it's still gross.

### Taking advantage of `IF [NOT] EXISTS` and alike

There is a multitude of important and useful SQL standard compliant and vendor specific clauses in DDL statements that can make your life so much easier. One of the well known and frequently used ones is `IF [NOT] EXISTS`.
  
Instead of letting `ShemaBuilder` doing a separate query(ies) to `information_schema`

```php
if (! Schema::hasTable('users')) {
    // create the table
}

if (! Schema::hasColumn('users', 'notes')) {
    // create the column
}
```

you can just write it natively in one statement

```sql
CREATE TABLE IF NOT EXISTS users (id BIGSERIAL PRIMARY KEY, ...);
ALTER TABLE users ADD IF NOT EXISTS notes TEXT;
```

### Using additional options when creating indexes

Some databases (i.e. PostgreSQL) allow you to (re)create indexes concurrently without locking your table.

```sql
CREATE INDEX CONCURRENTLY IF NOT EXISTS some_big_table_important_column_id 
    ON some_big_table (important_column);

CREATE INDEX IF NOT EXISTS table_json_column_idx USING GIN ON table (json_column);
```

You may need to create a specific type of index instead of a default `btree`

```sql
CREATE INDEX IF NOT EXISTS some_table_json_column_idx ON some_table (json_column) USING GIN;
```

Or create a partial/functional index

```sql
CREATE INDEX IF NOT EXISTS some_table_nullable_column_idx 
    ON some_table (nullable_column) 
    WHERE nullable_column IS NOT NULL;
```

### Taking advantage of database native procedural code (i.e. PL/pgSQL)

When using PostgreSQL you can use an anonymous [PL/pgSQL](https://www.postgresql.org/docs/current/static/plpgsql.html) code block if you need to. I.e. dynamically (without knowing the database name ahead of time) set `search_path` if you want to install all extensions in a dedicated schema instead of polluting `public`. 

The `.up.sql` migration could look like:

```sql
DO $$
BEGIN
  EXECUTE 'ALTER DATABASE ' || current_database() || ' SET search_path TO "$user",public,extensions';
END;
$$;
```

and the reverse `.down.sql`:

```sql
DO $$
BEGIN
  EXECUTE 'ALTER DATABASE ' || current_database() || ' SET search_path TO "$user",public';
END;
$$;
```

## Installation

You can install the package via composer:

```bash
composer require pmatseykanets/laravel-sql-migrations
```

If you're using Laravel < 5.5 or if you have package auto-discovery turned off you have to manually register the service provider:

```php
// config/app.php
'providers' => [
    ...
    SqlMigrations\SqlMigrationsServiceProvider::class,
],
```

## Usage

### Make SQL migrations

The most convenient way of creating SQL migrations is to use `artisan make:migration` with **`--sql`** option

```bash
php artisan make:migration create_users_table --sql
```

which will produce three files

```bash
database
└── migrations
    ├── 2018_06_15_000000_create_users_table.down.sql
    ├── 2018_06_15_000000_create_users_table.php
    └── 2018_06_15_000000_create_users_table.up.sql
```

*I know, it bloats `migrations` directory with additional files but this approach allows you to mix and match traditional and plain SQL migrations easily. If it's any consolation if you don't use reverse (`down`) migrations you can just delete `*.down.sql` file(s).*

**Note:** if you're creating files manually make sure that:

1. The base `php` migration class extends `SqlMigration` class and doesn't contain `up()` and `down()` methods, unless you mean to override the default behavior. 
2. The filename (without extension) of `.up.sql` and `.down.sql` files matches exactly (including the timestamp part) the filename of the base `php` migration.

At this point you can forget about `2018_06_15_000000_create_users_table.php` unless you want to configure or override behavior of this particular migration.

`SqlMigration` extends the built-in `Migration` so you can fine tune your migration in the same way

```php
class CreateNextIdFunction extends SqlMigration
{
    // Use a non default connection
    public $connection = 'pgsql2';
    // Wrap migration in a transaction if the database suports transactional DDL
    public $withinTransaction = true;
}
```

Now go ahead open up `*.sql` files and write your migration code.

I.e. `2018_06_15_000000_create_users_table.up.sql` might look along the lines of

```sql
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name CITEXT,
    email CITEXT,
    password TEXT,
    remember_token TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX IF NOT EXISTS users_email_idx ON users (email);
```

and `2018_06_15_000000_create_users_table.down.sql`

```sql
DROP TABLE IF EXISTS users;
```

You can also pass `--sql` option to `make:model` artisan command to instruct it to create plain SQL migrations for your newly created model.

```bash
php artisan make:model Post --migration --sql
```

### Run SQL migrations

Proceed as usual using `migrate`, `migrate:rollback` and other built-in commands.

## Example Projects

You can find bare Laravel 5.6 projects with default SQL migrations here:

- [PostgreSQL](https://github.com/pmatseykanets/laravel-sql-migrations-example-postgres)
- [MySQL](https://github.com/pmatseykanets/laravel-sql-migrations-example-mysql)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Peter Matseykanets](https://github.com/pmatseykanets)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
