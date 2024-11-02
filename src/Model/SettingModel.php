<?php

namespace Crow\LaravelSettingLite\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\LazyCollection;
use ReflectionClass;

/**
 * @property-read int $id
 * @property string $key
 * @property string|null $description
 * @property string|null $description_default
 * @property string|null $value
 * @property array|null $options
 * @property string $type
 * @property string|null $group
 * @property bool $is_public
 * @property bool $is_custom
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static Builder|static query()
 * @method static Builder|static find(int $id, array $columns = ['*'])
 * @method static Builder|static findOrFail(int $id, array $columns = ['*'])
 * @method static Builder|static first(array $columns = ['*'])
 * @method static Builder|static firstOrFail(array $columns = ['*'])
 * @method static Collection|static[] get()
 * @method static LazyCollection|static[] cursor()
 */
class SettingModel extends Model
{
    public const CACHE_PREFIX = 'laravel-setting-lite/setting/';

    protected $fillable = [
        'key',
        'description',
        'value',
        'options',
        'type',
        'group',
        'is_public',
        'is_custom',
    ];
    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean',
        'is_custom' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = Config::get('setting_lite.table');

        parent::__construct($attributes);
    }

    protected function getDescriptionAttribute(): ?string
    {
        if (
            isset($this->attributes['description']) && $this->attributes['description']
            || !Config::get('setting_lite.description_field_from_lexicon')
        ) {
            return $this->attributes['description'] ?? null;
        }

        return $this->getAttribute('description_default');
    }

    protected function getDescriptionDefaultAttribute(): ?string
    {
        $key = sprintf(
            '%s.setting.%s',
            Config::get('setting_lite.lexicon'),
            $this->getAttribute('key'),
        );

        if (Lang::has($key)) {
            return Lang::get($key);
        }

        return null;
    }

    public static function getTypes(): array
    {
        $class = new ReflectionClass(new static());

        $data = [];

        foreach ($class->getReflectionConstants() as $constant) {
            if (!str_starts_with($constant->getName(), 'TYPE_')) {
                continue;
            }

            $data[] = $constant->getValue();
        }

        return $data;
    }
}
