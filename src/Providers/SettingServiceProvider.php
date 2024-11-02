<?php

namespace Crow\LaravelSettingLite\Providers;

use Illuminate\Support\ServiceProvider;
use Crow\LaravelSettingLite\Console\Commands\SettingPublishCommand;
use Crow\LaravelSettingLite\Console\Commands\SettingSyncCommand;
use Crow\LaravelSettingLite\Model\SettingModel;
use Crow\LaravelSettingLite\Observers\SettingModelObserver;

class SettingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadCustomCommands();
        $this->loadCustomConfig();
        $this->loadCustomPublished();
        $this->loadObservers();
    }

    private function loadCustomCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    SettingSyncCommand::class,
                    SettingPublishCommand::class,
                ]
            );
        }
    }

    private function loadCustomConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/setting_lite.php', 'setting_lite');
    }

    private function loadCustomPublished(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../../config' => base_path('config')
                ],
                'config'
            );
            $this->publishes(
                [
                    __DIR__ . '/../../migration' => database_path('migrations')
                ],
                'migration'
            );
            $this->publishes(
                [
                    __DIR__ . '/../../resources/lang' => app('path.lang')
                ],
                'lang'
            );
        }
    }

    private function loadObservers(): void
    {
        SettingModel::observe(SettingModelObserver::class);
    }
}
