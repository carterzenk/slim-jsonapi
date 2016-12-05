<?php

namespace CarterZenk\JsonApi\Model;

use Illuminate\Support\Str;

class StringHelper
{
    /**
     * @param $name
     * @return string
     */
    public static function slugCase($name)
    {
        return Str::slug(Str::snake(ucwords($name)));
    }
}