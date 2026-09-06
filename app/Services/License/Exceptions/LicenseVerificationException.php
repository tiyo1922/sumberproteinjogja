<?php

namespace App\Services\License\Exceptions;

use Throwable;

class LicenseVerificationException extends LicenseException
{
    private string $errorCode;
    private int $httpStatus;

    public function __construct(
        string $message = 'License verification failed.',
        string $errorCode = 'VERIFICATION_FAILED',
        int $httpStatus = 422,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $errorCode;
        $this->httpStatus = $httpStatus;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }
}