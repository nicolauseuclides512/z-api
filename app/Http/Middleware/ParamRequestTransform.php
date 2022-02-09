<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Foundation\TransformRequest;

class ParamRequestTransform extends TransformRequest
{
    protected function transform($key, $value)
    {
        if (is_string($value) && $value === '') return null;
        if (is_string($value) && $value === 'null') return null;
        if (is_string($value) && $value === 'true') return (boolean)$value;

        return $value;
    }
}
