<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Price implements CastsAttributes
{
    /**
     * @param $model
     * @param string $key
     * @param $value
     * @param array $attributes
     * @return float|int
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return round($value * 100) / 100;
    }

    /**
     * @param $model
     * @param string $key
     * @param $value
     * @param array $attributes
     * @return float|int
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return round($value * 100) / 100;
    }
}
