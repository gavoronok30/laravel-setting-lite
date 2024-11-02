<?php

use Crow\LaravelSettingLite\Model\SettingModel;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    function setting($key = null, $default = null): mixed
    {
        $cacheKey = SettingModel::CACHE_PREFIX . $key;
        if (config('setting_lite.cache.enabled') && Cache::has($cacheKey)) {
            $value = Cache::get($cacheKey);
        } else {
            $builder = SettingModel::query();
            $builder->where('key', '=', $key);

            if (!$builder->first()) {
                return $default;
            }

            $value = $builder->first()->value;

            if (config('setting_lite.cache.enabled')) {
                Cache::forever($cacheKey, $value);
            }
        }

        if (!$value) {
            $value = $default;
        }

        return $value;
    }
}

if (!function_exists('setting_save')) {

    function setting_save(string $key = null, mixed $value = null): void
    {
        $setting = SettingModel::query()
            ->where('key', '=', $key)
            ->first();

        if ($setting) {
            $setting->value = $value;
            $setting->save();
        }
    }
}
