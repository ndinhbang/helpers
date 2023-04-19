<?php

if (!function_exists('blink')) {
    /**
     * @return \Illuminate\Contracts\Cache\Repository
     */
    function blink(): \Illuminate\Contracts\Cache\Repository
    {
        return cache()->store('array');
    }
}

if (!function_exists('abbreviated')) {
    /**
     * @param $str
     * @return string
     */
    function abbreviated($str): string
    {
        preg_match_all('/\b\w/u', $str, $matches);
        return implode("", $matches[0]);
    }
}

