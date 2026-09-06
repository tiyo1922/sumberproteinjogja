<?php

namespace Tests\Unit;

use App\Services\License\DomainCanonicalizer;
use App\Services\License\Exceptions\InvalidDomainException;
use PHPUnit\Framework\TestCase;

class DomainCanonicalizerTest extends TestCase
{
    /**
     * Test valid standard hostname canonicalization.
     */
    public function test_canonicalizes_bare_hostname_and_case(): void
    {
        $this->assertSame('example.com', DomainCanonicalizer::canonicalize('example.com'));
        $this->assertSame('example.com', DomainCanonicalizer::canonicalize('EXAMPLE.COM'));
        $this->assertSame('sumberproteinjogja.com', DomainCanonicalizer::canonicalize('  sumberproteinjogja.com  '));
    }

    /**
     * Test URL schemes, paths, queries, fragments, ports, and userinfo stripping.
     */
    public function test_canonicalizes_urls_and_strips_metadata(): void
    {
        $this->assertSame('example.com', DomainCanonicalizer::canonicalize('https://example.com'));
        $this->assertSame('example.com', DomainCanonicalizer::canonicalize('http://example.com/catalog/product?sort=desc#review'));
        $this->assertSame('example.com', DomainCanonicalizer::canonicalize('example.com:8080'));
        $this->assertSame('example.com', DomainCanonicalizer::canonicalize('https://admin:secret123@example.com:8443/dashboard'));
    }

    /**
     * Test trailing dot stripping (FQDN notation).
     */
    public function test_strips_single_trailing_dot(): void
    {
        $this->assertSame('example.com', DomainCanonicalizer::canonicalize('example.com.'));
        $this->assertSame('sumberproteinjogja.com', DomainCanonicalizer::canonicalize('https://sumberproteinjogja.com./'));
    }

    /**
     * Test strict www distinction (www is NOT stripped or made equivalent).
     */
    public function test_preserves_strict_www_distinction(): void
    {
        $bare = DomainCanonicalizer::canonicalize('sumberproteinjogja.com');
        $www = DomainCanonicalizer::canonicalize('www.sumberproteinjogja.com');

        $this->assertSame('sumberproteinjogja.com', $bare);
        $this->assertSame('www.sumberproteinjogja.com', $www);
        $this->assertNotSame($bare, $www);
    }

    /**
     * Test internationalized domain names (IDN / UTS#46) conversion to ASCII Punycode.
     */
    public function test_canonicalizes_idn_domains_to_punycode(): void
    {
        if (!function_exists('idn_to_ascii')) {
            $this->markTestSkipped('ext-intl is not enabled.');
        }

        $this->assertSame('xn--mnchen-3ya.de', DomainCanonicalizer::canonicalize('münchen.de'));
        $this->assertSame('xn--0zwm56d.xn--fiqs8s', DomainCanonicalizer::canonicalize('https://测试.中国/'));
    }

    /**
     * Test IP addresses and localhost.
     */
    public function test_canonicalizes_ip_addresses_and_localhost(): void
    {
        $this->assertSame('127.0.0.1', DomainCanonicalizer::canonicalize('127.0.0.1'));
        $this->assertSame('192.168.1.100', DomainCanonicalizer::canonicalize('http://192.168.1.100:8000/api'));
        $this->assertSame('localhost', DomainCanonicalizer::canonicalize('http://localhost:8000'));
        $this->assertSame('localhost', DomainCanonicalizer::canonicalize('LOCALHOST'));

        // IPv6
        $this->assertSame('::1', DomainCanonicalizer::canonicalize('http://[::1]:8080/'));
        $this->assertSame('2001:db8::1', DomainCanonicalizer::canonicalize('http://[2001:db8::1]:443/'));
    }

    /**
     * Test rejection of empty or whitespace-only input.
     */
    public function test_rejects_empty_input(): void
    {
        $this->expectException(InvalidDomainException::class);
        DomainCanonicalizer::canonicalize('   ');
    }

    /**
     * Test rejection of control characters.
     */
    public function test_rejects_control_characters(): void
    {
        $this->expectException(InvalidDomainException::class);
        DomainCanonicalizer::canonicalize("example\x00.com");
    }

    /**
     * Test rejection of inputs exceeding 2048 bytes.
     */
    public function test_rejects_excessive_input_length(): void
    {
        $longInput = 'http://' . str_repeat('a', 2050) . '.com';
        $this->expectException(InvalidDomainException::class);
        DomainCanonicalizer::canonicalize($longInput);
    }

    /**
     * Test rejection of invalid hostnames (bad labels, bad characters, hyphen placement, emails).
     */
    public function test_rejects_malformed_hostnames(): void
    {
        $invalidSamples = [
            '-example.com',
            'example-.com',
            'exam ple.com',
            'example..com',
            'example@domain.com',
            'http://:8080',
        ];

        foreach ($invalidSamples as $invalid) {
            try {
                DomainCanonicalizer::canonicalize($invalid);
                $this->fail("Expected InvalidDomainException for [{$invalid}]");
            } catch (InvalidDomainException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }
    }

    /**
     * Test rejection of unsupported URL schemes (e.g. ftp, file, javascript).
     */
    public function test_rejects_unsupported_url_schemes(): void
    {
        $unsupportedSchemes = [
            'ftp://example.com',
            'sftp://example.com',
            'file:///etc/passwd',
            'ssh://example.com',
            'javascript://alert(1)',
        ];

        foreach ($unsupportedSchemes as $url) {
            try {
                DomainCanonicalizer::canonicalize($url);
                $this->fail("Expected InvalidDomainException for [{$url}]");
            } catch (InvalidDomainException $e) {
                $this->assertStringContainsString('Unsupported URL scheme', $e->getMessage());
            }
        }
    }
}