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
- Register your provider in `composer.json`:

```json
"extra": {
    "tamedevelopers": {
        "providers": [
            "Vendor\\Package\\Kernal"
        ]
    }
},
```

- Create a new class anywhere inside directory `app/Console`, e.g. `Kernal.php` 
```php

namespace Vendor\Package;

use App\Console\Commands\MakeModelCommand;
use Tamedevelopers\Support\Capsule\CommandProviderInterface;

class KernalCommand implements CommandProviderInterface
{
    /** @inheritDoc */
    public function register(Artisan $artisan)  :void
    {
        $this->register(
            'make:model', 
            [new MakeModelCommand(), 'handle'], 
            'Database model generator'
        );
    }
}
```

## Notes
- Commands run in CLI, no manual browser reload needed.
- Env and logger are booted automatically in the sample command.
