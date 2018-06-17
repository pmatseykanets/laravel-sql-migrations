# laravel-sql-migrations

Write your laravel migrations in plain SQL.

## Contents

- [Installation](#installation)
- [Usage](#usage)
- [Security](#security)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Installation

You can install the package via composer:

```bash
$ composer require pmatseykanets/laravel-sql-migrations
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

To create a base migration and `up` and `down` sql files with it
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

At this point you can forget about `2018_06_15_000000_create_users_table.php` unless you want to override `up` and / or `down` methods for this particular migration.

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

### Run SQL migrations

Proceed as usual using `migrate`, `migrate:rollback` and other commands.

## Security

If you discover any security related issues, please email pmatseykanets@gmail.com instead of using the issue tracker.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Peter Matseykanets](https://github.com/pmatseykanets)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
