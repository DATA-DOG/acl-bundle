<?php

namespace AclBundle;

class Util
{
    public static function classToResource($target)
    {
        if (is_object($target)) {
            $target = get_class($target);
        }
        $points = array_map(function($name) {
            return self::underscore($name);
        }, explode('\\', $target));
        return implode('.', $points);
    }

    public static function underscore($camelCased)
    {
        return strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $camelCased));
    }
}
