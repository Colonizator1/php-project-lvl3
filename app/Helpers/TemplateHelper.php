<?php

namespace App\Helpers;

class TemplateHelper
{
    public static function isActiveRoute($route)
    {
        return $route === url()->current();
    }

    public static function setClassForActiveRoute($route, $class = 'active')
    {
        return self::isActiveRoute($route) ? $class : '';
    }
}
