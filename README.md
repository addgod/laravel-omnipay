# Omnipay for laravel

## Installation
```composer require addgod/laravel-omnipay```

## Publish config
```php artisan vendor:publish --provider "Addgod\Omnipay\ServiceProvider"```


## Run migration
```php artisan migrate```

## Usage

```
use Addgod\Omnipay\app\Models\Transaction

$transaction = Transaction::make([
    'amount' => 10000
    'route_to' => route('receipt.url');
]);

$transaction->purchase(); // Use for direct capture
$transaction->authorize(); // Use for reserving the amount on a card.
```

## TODO
Make documentation for database driver usage.
