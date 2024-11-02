<?php

namespace Crow\LaravelSettingLite\Observers;

use Crow\LaravelSettingLite\Exceptions\SettingException;
use Crow\LaravelSettingLite\Model\SettingModel;
use Illuminate\Support\Facades\Cache;

class SettingModelObserver
{
    public function creating(SettingModel $model): void
    {
        $count = SettingModel::query()
            ->where('key', '=', $model->key)
            ->count();
        if ($count) {
            throw SettingException::keyDuplicate($model);
        }
    }

    public function updating(SettingModel $model): void
    {
        $count = SettingModel::query()
            ->where('key', '=', $model->key)
            ->where('id', '!=', $model->id)
            ->count();
        if ($count) {
            throw SettingException::keyDuplicate($model);
        }
    }

    public function saved(SettingModel $model): void
    {
        if (config('setting_lite.cache.enabled')) {
            Cache::forget(SettingModel::CACHE_PREFIX . $model->key);
        }
    }
}
