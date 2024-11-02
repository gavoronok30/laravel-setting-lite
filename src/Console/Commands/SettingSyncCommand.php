<?php

namespace Crow\LaravelSettingLite\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Crow\LaravelSettingLite\Model\SettingModel;

class SettingSyncCommand extends Command
{
    protected $signature = 'setting:sync';
    protected $description = 'Synchronization of settings';
    private ?string $dataClassName = null;
    private ?string $dataMethodName = null;
    private ?bool $syncCreate = false;
    private ?bool $syncDelete = false;
    private ?Collection $syncUpdateFields = null;

    public function handle(): void
    {
        $this->setupConfig();

        if (!$this->dataClassName || !$this->dataMethodName) {
            $this->error('Config sync not setup class name or class static method');
            return;
        }

        $data = $this->getData();

        $this->checkData($data);
    }

    private function setupConfig(): void
    {
        $this->dataClassName = Config::get('setting_lite.data.class');
        $this->dataMethodName = Config::get('setting_lite.data.method');
        $this->syncCreate = (bool)Config::get('setting_lite.sync.create');
        $this->syncDelete = (bool)Config::get('setting_lite.sync.delete');
        $this->syncUpdateFields = Collection::make(Config::get('setting_lite.sync.update_fields'));
    }

    private function getData(): array
    {
        return $this->dataClassName::{$this->dataMethodName}();
    }

    private function checkData(array $data): void
    {
        $data = $this->getDataFormatted($data);

        foreach (SettingModel::query()->cursor() as $row) {
            if (!$data->get($row->key)) {
                if ($this->syncDelete && !$row->is_custom) {
                    $row->delete();
                }
                continue;
            }
            if ($data->get($row->key)) {
                $this->updateRow($row, Collection::make($data->get($row->key)));
                $data->offsetUnset($row->key);
            }
        }

        $this->createRows($data);
    }

    private function getDataFormatted(array $data): Collection
    {
        $collect = Collection::make();

        foreach ($data as $row) {
            $collect->put($row['key'], $row);
        }

        return $collect;
    }

    private function updateRow(SettingModel $setting, Collection $data): void
    {
        foreach ($this->syncUpdateFields as $field) {
            if ($field == 'options') {
                $this->prepareFieldOptions($data);
            }
            $setting->$field = $data->get($field);
        }
        $setting->save();
    }

    private function createRows(Collection $data): void
    {
        if (!$this->syncCreate) {
            return;
        }

        foreach ($data as $row) {
            $row = Collection::make($row);
            $row->offsetUnset('id');
            $setting = new SettingModel();
            foreach ($row->keys() as $field) {
                if ($field == 'options') {
                    $this->prepareFieldOptions($row);
                }
                $setting->$field = $row->get($field);
            }
            $setting->save();
        }
    }

    private function prepareFieldOptions(Collection $data): void
    {
        if (!$data->get('options')) {
            $data->put('options', null);
            return;
        } elseif (is_array($data->get('options'))) {
            return;
        }

        $data->put('options', Collection::make(json_decode($data->get('options'), true))->toArray());
    }
}
