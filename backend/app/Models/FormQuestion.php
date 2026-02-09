<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['vehicle_type_id', 'type', 'label', 'order', 'required', 'options'];

    protected $casts = [
        'required' => 'boolean'
    ];

    const TYPES = [
        'radio' => [
            'options' => true,
            'label' => 'answer',
        ],
        'select' => [
            'options' => true,
            'label' => 'answer',
        ],
        'checkbox' => [
            'options' => false,
            'label' => 'question',
        ],
        'file' => [
            'options' => false,
            'label' => null,
        ],
        'date' => [
            'options' => false,
            'label' => null,
        ],
        'datetime' => [
            'options' => false,
            'label' => null,
        ],
        'range' => [
            'options' => false,
            'label' => "both",
        ],
        'date_range' => [
            'options' => false,
            'label' => null
        ],
        'text' => [
            'options' => false,
            'label' => 'answer'
        ],
        'float' => [
            'options' => false,
            'label' => 'answer'
        ],
        'integer' => [
            'options' => false,
            'label' => 'answer'
        ],
        'link' => [
            'options' => false,
            'label' => null
        ],
        'security' => [
            'options' => false,
            'label' => 'question',
            'default_label' => 'Охрана'
        ],
        'living' => [
            'options' => false,
            'label' => 'question',
            'default_label' => 'Проживание'
        ],
        'orv' => [
            'options' => false,
            'label' => 'question',
            'default_label' => 'Вездеход'
        ],
    ];

    /**
     * Relations
     */

    public function answers()
    {
        return $this->hasMany(FormAnswer::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    /**
     * Accessors, Mutators
     */

    public function setOptionsAttribute($value)
    {
        if ($value) {
            $this->attributes['options'] = json_encode($value);
        }
    }

    public function getOptionsAttribute($value)
    {
        return $value ? json_decode($value) : $value;
    }

    public function setLabelAttribute($value)
    {
        if (!$value && in_array($this->type, self::getTypesWithDefaultLabel())) {
            // set default label
            $this->attributes['label'] = self::getDefaultLabelByType($this->type);
        } else {
            $this->attributes['label'] = $value;
        }
    }

    public function setRequiredAttribute($value)
    {
        $this->attributes['required'] = !!$value;
    }

    /**
     *  Scopes
     */

    public function scopeRequired(Builder $query): Builder
    {
        return $query->where("required", true);
    }

    public static function getTypeInfo(string $type): array|null
    {
        return self::TYPES[$type] ?? null;
    }

    public static function getTypeKeys(): array
    {
        return array_keys(self::TYPES);
    }

    public static function getTypeWithOptionsKeys(): array
    {
        return array_keys(array_filter(self::TYPES, function ($item) {
            return $item['options'];
        }));
    }

    public static function getTypesWithDefaultLabel(): array
    {
        return array_keys(array_filter(self::TYPES, function ($item) {
            return isset($item['default_label']);
        }));
    }

    public static function getDefaultLabelByType(string $type): string|null
    {
        return self::TYPES[$type]['default_label'] ?? null;
    }

}
