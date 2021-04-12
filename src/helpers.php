<?php

namespace FancyHttp;

/**
 * Dot notation for get data inside array
 *
 * @param array  $_
 * @param string $path
 * @return mixed
 * @internal
 */
function array_get(array &$_, string $path): mixed
{
    $propNames = explode('.', $path);
    $nested = &$_;
    foreach ($propNames as $propName) {
        $nested = &$nested[$propName];
    }
    return $nested;
}

/**
 * Dot notation for set data inside array.
 *
 * @param array  $_
 * @param string $path
 * @param mixed  $value
 * @internal
 */
function array_set(array &$_, string $path, mixed $value): void
{
    $propNames = explode('.', $path);
    $paths = array_slice($propNames, 0, -1);
    $nested = &$_;
    foreach ($paths as $propName) {
        if (is_array($nested[$propName])) $nested = &$nested[$propName];
        else {
            $nested[$propName] = [];
            $nested = &$nested[$propName];
        }
    }
    $target = array_slice($propNames, -1)[0];
    $nested[$target] = $value;
}