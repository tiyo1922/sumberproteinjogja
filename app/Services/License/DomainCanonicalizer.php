<?php

namespace App\Services\License;

use App\Services\License\Exceptions\InvalidDomainException;

class DomainCanonicalizer
{
    /**
     * Canonicalize a hostname, URL, or domain input string to a standardized domain identity.
     *
     * @param string $input
     * @return string
     * @throws InvalidDomainException
     */
    public static function canonicalize(string $input): string
    {
        // 1. Check input length maximum 2048 bytes
        if (strlen($input) > 2048) {
            throw new InvalidDomainException('Domain input exceeds maximum length of 2048 bytes.');
        }

        // 2. Reject control characters
        if (preg_match('/[\x00-\x1F\x7F]/', $input)) {
            throw new InvalidDomainException('Domain input contains illegal control characters.');
        }

        // 3. Trim ASCII whitespace
        $trimmed = trim($input);
        if ($trimmed === '') {
            throw new InvalidDomainException('Domain input cannot be empty.');
        }

        // 4. Reject email addresses passed without URL scheme
        if (str_contains($trimmed, '@') && !preg_match('#^[a-zA-Z][a-zA-Z0-9+\-.]*://#', $trimmed)) {
            throw new InvalidDomainException('Domain input cannot be an email address or contain unescaped userinfo.');
        }

        // 5. Reject arbitrary URL schemes (only HTTP, HTTPS, or bare hostnames are allowed)
        if (preg_match('#^([a-zA-Z][a-zA-Z0-9+\-.]*)://#', $trimmed, $matches)) {
            $scheme = strtolower($matches[1]);
            if (!in_array($scheme, ['http', 'https'], true)) {
                throw new InvalidDomainException("Unsupported URL scheme [{$scheme}]. Only HTTP and HTTPS schemes or bare hostnames are allowed.");
            }
            $candidate = $trimmed;
        } else {
            $candidate = 'https://' . $trimmed;
        }

        $parts = parse_url($candidate);
        if ($parts === false || empty($parts['host'])) {
            throw new InvalidDomainException('Unable to extract a valid host from domain input.');
        }

        $host = $parts['host'];

        // 6. Handle IPv6 enclosed in brackets
        if (str_starts_with($host, '[') && str_ends_with($host, ']')) {
            $rawIpv6 = substr($host, 1, -1);
            if (filter_var($rawIpv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
                $packed = inet_pton($rawIpv6);
                if ($packed !== false) {
                    return strtolower(inet_ntop($packed));
                }
            }
            throw new InvalidDomainException('Invalid IPv6 host representation.');
        }

        // 7. Strip port if somehow retained in host
        if (str_contains($host, ':') && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $hostParts = explode(':', $host);
            $host = $hostParts[0];
        }

        // 8. Strip single trailing dot (FQDN root notation)
        if (str_ends_with($host, '.')) {
            $host = substr($host, 0, -1);
        }

        if ($host === '') {
            throw new InvalidDomainException('Host cannot be empty after stripping trailing dot.');
        }

        // 9. Lowercase normalization
        $host = strtolower($host);

        // 10. IDN / UTS#46 Punycode conversion
        if (function_exists('idn_to_ascii') && !filter_var($host, FILTER_VALIDATE_IP)) {
            $asciiHost = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if ($asciiHost === false || $asciiHost === '') {
                throw new InvalidDomainException("Failed to convert internationalized domain [{$host}] to ASCII Punycode.");
            }
            $host = strtolower($asciiHost);
        }

        // 11. IPv4 validation & normalization
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $long = ip2long($host);
            if ($long !== false) {
                return long2ip($long);
            }
        }

        // 12. Bare IPv6 validation & normalization
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            $packed = inet_pton($host);
            if ($packed !== false) {
                return strtolower(inet_ntop($packed));
            }
        }

        // 13. Localhost special handling
        if ($host === 'localhost') {
            return 'localhost';
        }

        // 14. Validate RFC 1034 / RFC 1123 Hostname format and length limits
        if (strlen($host) > 253) {
            throw new InvalidDomainException('Canonical domain name exceeds maximum allowed length of 253 characters.');
        }

        // Validate each label (must be 1-63 characters, start/end with alphanumeric, contain only alphanumeric and hyphens)
        $labels = explode('.', $host);
        foreach ($labels as $label) {
            if (strlen($label) < 1 || strlen($label) > 63) {
                throw new InvalidDomainException("Domain label [{$label}] length must be between 1 and 63 characters.");
            }

            if (!preg_match('/^[a-z0-9]([a-z0-9\-]*[a-z0-9])?$/', $label)) {
                throw new InvalidDomainException("Domain label [{$label}] contains invalid characters or illegal hyphen placement.");
            }
        }

        return $host;
    }
}