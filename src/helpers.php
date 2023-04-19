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

