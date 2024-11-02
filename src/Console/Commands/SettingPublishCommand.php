<?php

namespace Crow\LaravelSettingLite\Console\Commands;

use Crow\LaravelSettingLite\Providers\SettingServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class SettingPublishCommand extends Command
{
    protected $signature = 'setting:publish {--tag=* : Tag for published}';
    protected $description = 'Publish files for setting lite package';
    private array $files = [];
    private array $fileTags = [
        'config',
        'migration',
        'lang',
    ];

    public function handle(): void
    {
        $option = is_array($this->option('tag')) && !empty($this->option('tag')) ? $this->option('tag')[0] : '';

        $this->parsePublishedFiles();

        switch ($option) {
            case 'config':
                $this->copyConfig();
                break;
            case 'migration':
                $this->copyMigration();
                break;
            case 'lang':
                $this->copyLang();
                break;
            default:
                $this->error('Not selected tag');
                break;
        }
    }

    private function parsePublishedFiles(): void
    {
        $index = 0;
        foreach (SettingServiceProvider::pathsToPublish(SettingServiceProvider::class) as $k => $v) {
            $this->files[$this->fileTags[$index]] = [
                'from' => $k,
                'to' => $v,
            ];
            $index++;
        }
    }

    private function copyConfig(): void
    {
        $this->copyFiles($this->files['config']['from'], $this->files['config']['to']);
    }

    private function copyMigration(): void
    {
        $filename = sprintf(
            '%s_create_%s.php',
            now()->format('Y_m_d_His'),
            Config::get('setting_lite.table')
        );

        $this->copyFile(
            $this->files['migration']['from'] . DIRECTORY_SEPARATOR . 'create_settings.stub',
            $this->files['migration']['to'] . DIRECTORY_SEPARATOR . $filename,
            Config::get('setting_lite.table')
        );
    }

    private function copyLang(): void
    {
        $this->copyFiles(
            $this->files['lang']['from'] . '/en/',
            $this->files['lang']['to'] . '/en/'
        );
    }

    private function copyFiles(string $from, string $to): void
    {
        if (!file_exists($to)) {
            mkdir($to, 0755, true);
        }
        $from = rtrim($from, '/') . '/';
        $to = rtrim($to, '/') . '/';
        foreach (scandir($from) as $file) {
            if (!is_file($from . $file)) {
                continue;
            }

            $path = strtr(
                $to . $file,
                [
                    base_path() => ''
                ]
            );

            if (file_exists($to . $file)) {
                $this->info(
                    sprintf(
                        'File "%s" skipped',
                        $path
                    )
                );
                continue;
            }

            copy(
                $from . $file,
                $to . $file
            );

            $content = file_get_contents($to . $file);
            $content = strtr($content, ['{{TABLE_NAME}}' => Config::get('setting_lite.table')]);
            file_put_contents($to . $file, $content);

            $this->info(
                sprintf(
                    'File "%s" copied',
                    $path
                )
            );
        }
    }

    private function copyFile(string $from, string $to, ?string $table = null): void
    {
        copy(
            $from,
            $to
        );

        $content = file_get_contents($to);
        $content = strtr($content, [
            '{{TABLE_NAME}}' => $table,
        ]);
        file_put_contents($to, $content);

        $path = strtr(
            $to,
            [
                base_path() => ''
            ]
        );

        $this->info(
            sprintf(
                'File "%s" copied',
                $path
            )
        );
    }
}
