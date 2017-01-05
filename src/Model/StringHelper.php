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

    /**
     * @param $name
     * @return string
     */
    public static function camelCase($name)
    {
        return Str::camel($name);
    }

    /**
     * @param $word
     * @return string
     */
    public static function pluralize($word)
    {
        return Str::plural($word);
    }
}
