# Tamedevelopers Database CLI

## Quick start

1. Install dependencies
```bash
composer install
```

2. List commands
```bash
php database list
# or via composer bin (after composer install)
./vendor/bin/database list
```

3. Scaffold example table
```bash
php database scaffold --name=posts
```

## Adding new commands
- Create a new class in `src/Console/Commands`, e.g. `MakeModelCommand.php` with a `handle(array $args): int` method.
- Register it in `src/Console/Artisan.php` constructor:
```php
$this->register('make:model', [new MakeModelCommand(), 'handle']);
```

## Notes
- Commands run in CLI, no manual browser reload needed.
- Env and logger are booted automatically in the sample command.
