<?php

namespace App\Services\License\Contracts;

interface ClockInterface
{
    /**
     * Get the current Unix timestamp in seconds.
     */
    public function now(): int;
}
