## 
## Description

System settings for the site, using the database. 

- convenient and easy way to get values from system settings
- command to synchronize system settings between seeds and database

## Install

Open file **bootstrap/providers.php** and connect the provider from the package (optional, using laravel discovered package system by default)

```
\Crow\LaravelSettingLite\Providers\SettingServiceProvider::class,
```

## Run commands

For creating config file

```
php artisan vendor:publish --provider="Crow\LaravelSettingLite\Providers\SettingServiceProvider" --tag=config
```

For creating language file (if need for setting description or custom exception text)

```
php artisan vendor:publish --provider="Crow\LaravelSettingLite\Providers\SettingServiceProvider" --tag=lang
```

For creating migration file

```
php artisan setting:publish --tag=migration
```

For generate table

```
php artisan migrate
```

## Configure seed file

**1.** Create seeder file if not exists for settings.
In the created seed file, you need to add a static method (for example, `public static function data()`).
The method must return an array of standard to fill the database

**2.** Open config file `config/setting_lite.php` and add this class and method in exists parameters

```
'data' => [
    'class' => \Database\Seeders\SettingTableSeeder::class,
    'method' => 'data',
],
```

**3** Example content seeder file `database/seeders/SettingTableSeeder.php`

```
<?php

namespace Database\Seeders;

use Crow\LaravelSettingLite\Enums\SettingTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingTableSeeder extends Seeder
{
    public static function data()
    {
        return [
            [
                'id' => 1,
                'key' => 'set_1',
                'type' => SettingTypeEnum::TYPE_LIST->value,
                'group' => 'Main',
                'is_public' => false,
                'options' => json_encode(['v1', 'v2']),
                'created_at' => '2021-02-03 15:00:00',
                'updated_at' => '2021-02-03 15:00:00',
            ],
            [
                'id' => 2,
                'key' => 'set_2',
                'type' => SettingTypeEnum::TYPE_BOOLEAN->value,
                'group' => 'Main',
                'is_public' => true,
                'created_at' => '2021-02-03 15:00:00',
                'updated_at' => '2021-02-03 15:00:00',
            ],
            ...
        ];
    }
}
```

## Command for sync settings

```
php artisan setting:sync
```

## Usage

Get setting value

```
setting('SETTING_KEY', 'DEFAULT VALUE IF EMPTY SETTING VALUE');
```

Update setting value

```
setting_save('SETTING_KEY', 'NEW VALUE');
```

Get types for render on UI

```
return \Crow\LaravelSettingLite\Enums\SettingTypeEnum::values();
```

Get groups

Only saved groups from table of settings

```
return \Crow\LaravelSettingLite\Model\SettingModel::query()
    ->groupBy('group')
    ->orderBy('group', 'ASC')
    ->pluck('group')->toArray();
```
