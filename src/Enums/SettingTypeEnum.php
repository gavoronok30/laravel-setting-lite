<?php

namespace Crow\LaravelSettingLite\Enums;

enum SettingTypeEnum: string
{
    case TYPE_TEXT = 'text';
    case TYPE_MULTILINE = 'multilinetext';
    case TYPE_BOOLEAN = 'boolean';
    case TYPE_INTEGER = 'integer';
    case TYPE_FLOAT = 'float';
    case TYPE_LIST = 'list';
    case TYPE_MULTILIST = 'multilist';
    case TYPE_PASSWORD = 'password';
    case TYPE_CHECKBOX = 'checkbox';
    case TYPE_RADIO = 'radio';

    public static function values(): array
    {
        $data = [];

        foreach (self::cases() as $value) {
            $data[] = $value->value;
        }

        return $data;
    }
}
