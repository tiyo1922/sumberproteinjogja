<?php

namespace App\Services\License;

use App\Services\License\Contracts\ClockInterface;

class SystemClock implements ClockInterface
{
    public function now(): int
    {
        return time();
    }
}
