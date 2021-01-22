<?php

namespace App\Helpers;

class TemplateHelper
{
    public static function isActiveRoute(string $route): bool
    {
        return $route === url()->current();
    }

    public static function setClassForActiveRoute(string $route, string $class = 'active'): string
    {
        return self::isActiveRoute($route) ? $class : '';
    }
}
