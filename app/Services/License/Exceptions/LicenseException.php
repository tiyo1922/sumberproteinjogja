<?php

namespace App\Services\License\Exceptions;

use RuntimeException;

abstract class LicenseException extends RuntimeException
{
    // Base exception for all license-related operational & cryptographic errors
}
