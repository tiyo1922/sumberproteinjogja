<?php

namespace App\Services\License\Exceptions;

use Throwable;

class LicenseNetworkException extends LicenseException{
    public function __construct(
        string $message = 'Central license server is unreachable.',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
