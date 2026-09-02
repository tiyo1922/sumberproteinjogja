<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitorTraffic
{
    /**
     * Handle an incoming request and track public landing pageviews safely.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Fail-safe execution: tracking must never break or delay the HTTP response
        try {
            $this->trackPageview($request, $response);
        } catch (\Throwable $e) {
            // Silently report exception to logs without affecting the visitor
            report($e);
        }

        return $response;
    }

    /**
     * Record eligible pageview event into traffic_events table.
     */
    protected function trackPageview(Request $request, Response $response): void
    {
        // 1. Eligibility Check: Only HTTP GET HTML page requests on public routes
        if (!$request->isMethod('GET') || $request->expectsJson() || $request->isXmlHttpRequest()) {
            return;
        }

        $path = $request->path(); // e.g. '/', 'produk', 'knowledge', etc.
        $normalizedPath = '/' . ltrim($path, '/');

        // Exclude admin, auth, API, and static assets
        if (
            $request->is('admin*') ||
            $request->is('login*') ||
            $request->is('logout*') ||
            $request->is('forgot-password*') ||
            $request->is('reset-password*') ||
            $request->is('api*') ||
            $request->is('up') ||
            $request->is('robots.txt') ||
            $request->is('sitemap.xml') ||
            $request->is('storage*') ||
            $request->is('images*') ||
            $request->is('build*')
        ) {
            return;
        }

        // 2. Bot & Crawler Filtering
        $userAgent = (string) $request->header('User-Agent', '');
        if ($this->isBot($userAgent)) {
            return;
        }

        // 3. Visitor Identification via First-Party Cookie (_sp_vid)
        $cookieName = '_sp_vid';
        $visitorId = $request->cookie($cookieName);
        $isNewVisitor = false;

        if (empty($visitorId) || !is_string($visitorId) || strlen($visitorId) < 16) {
            $visitorId = (string) Str::uuid();
            $isNewVisitor = true;
        }

        // 4. Source & UTM Classification
        $utmSource = $request->query('utm_source');
        $utmMedium = $request->query('utm_medium');
        $utmCampaign = $request->query('utm_campaign');
        $fbclid = $request->query('fbclid');
        $gclid = $request->query('gclid');
        $referrer = (string) $request->header('referer', '');

        $source = $this->classifySource($utmSource, $fbclid, $gclid, $referrer, $request->getHost());

        // 5. Device Classification
        $device = $this->classifyDevice($userAgent);

        // 6. Record Event into traffic_events table
        DB::table('traffic_events')->insert([
            'visitor_id' => substr($visitorId, 0, 64),
            'event_type' => 'pageview',
            'page_path' => substr($normalizedPath, 0, 255),
            'source' => $source,
            'utm_source' => $utmSource ? substr((string) $utmSource, 0, 100) : null,
            'utm_medium' => $utmMedium ? substr((string) $utmMedium, 0, 100) : null,
            'utm_campaign' => $utmCampaign ? substr((string) $utmCampaign, 0, 100) : null,
            'device' => $device,
            'created_at' => now(),
        ]);

        // 7. Attach Cookie to Response if new (1 year lifetime, Lax SameSite)
        if ($isNewVisitor) {
            $response->headers->setCookie(
                Cookie::make(
                    $cookieName,
                    $visitorId,
                    60 * 24 * 365, // 1 year in minutes
                    '/',
                    null,
                    $request->isSecure(),
                    true, // HttpOnly
                    false,
                    'Lax'
                )
            );
        }
    }

    /**
     * Classify visitor traffic source into meta_ads | google_organic | direct | referral.
     */
    protected function classifySource(?string $utmSource, ?string $fbclid, ?string $gclid, string $referrer, string $currentHost): string
    {
        $lowerUtm = strtolower(trim($utmSource ?? ''));

        // Meta Ads
        if (
            in_array($lowerUtm, ['facebook', 'fb', 'instagram', 'ig', 'meta', 'meta_ads']) ||
            !empty($fbclid)
        ) {
            return 'meta_ads';
        }

        // Google Organic
        if (
            in_array($lowerUtm, ['google', 'google_ads']) ||
            !empty($gclid) ||
            ($referrer !== '' && (str_contains(strtolower($referrer), 'google.com') || str_contains(strtolower($referrer), 'google.co.id')))
        ) {
            return 'google_organic';
        }

        // Referrer analysis
        if ($referrer !== '') {
            $refHost = parse_url($referrer, PHP_URL_HOST);
            if ($refHost && strtolower($refHost) !== strtolower($currentHost)) {
                $lowerRefHost = strtolower($refHost);
                if (str_contains($lowerRefHost, 'facebook.com') || str_contains($lowerRefHost, 'instagram.com')) {
                    return 'meta_ads';
                }
                return 'referral';
            }
        }

        return 'direct';
    }

    /**
     * Classify device into mobile | tablet | desktop | unknown.
     */
    protected function classifyDevice(string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'unknown';
        }

        $ua = strtolower($userAgent);

        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
            return 'tablet';
        }

        if (preg_match('/(mobi|iphone|ipod|blackberry|opera mini|opera mobi|windows phone|iemobile|mobile)/i', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Detect automated bots, crawlers, and headful/headless test suites.
     */
    protected function isBot(string $userAgent): bool
    {
        if (empty($userAgent)) {
            return false;
        }

        $botPattern = '/(bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|headless|lighthouse|python|curl|wget|postman)/i';
        return (bool) preg_match($botPattern, $userAgent);
    }
}
