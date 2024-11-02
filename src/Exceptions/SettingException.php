<?php

namespace Crow\LaravelSettingLite\Exceptions;

use Exception;
use Illuminate\Support\Facades\Lang;
use Crow\LaravelSettingLite\Model\SettingModel;

class SettingException extends Exception
{
    public static function keyDuplicate(SettingModel $model, ?int $code = null): static
    {
        $model->setHidden(['options']);

        $key = 'setting_lite.exception.key_duplicate';
        $text = Lang::has($key)
            ? Lang::get($key, $model->toArray())
            : 'Key ' . $model->key . ' exists ';

        return new static($text, $code ?: 422);
    }
}
