<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrafficEventController extends Controller
{
    /**
     * Record public user interaction events (chat_admin, pesan_order_wa) into traffic_events table.
     */
    public function track(Request $request): Response
    {
        try {
            $eventType = (string) $request->input('event_type', '');

            // 1. Strict Whitelist Validation
            if (!in_array($eventType, ['chat_admin', 'pesan_order_wa'], true)) {
                return response()->noContent();
            }

            // 2. Visitor Identification via _sp_vid Cookie
            $cookieName = '_sp_vid';
            $visitorId = $request->cookie($cookieName);
            $isNewVisitor = false;

            if (empty($visitorId) || !is_string($visitorId) || strlen($visitorId) < 16) {
                // If provided as a fallback form parameter
                $paramVid = (string) $request->input('visitor_id', '');
                if (!empty($paramVid) && strlen($paramVid) >= 16) {
                    $visitorId = $paramVid;
                } else {
                    $visitorId = (string) Str::uuid();
                    $isNewVisitor = true;
                }
            }

            // 3. Page Path Normalization & Sanitization
            $rawPagePath = (string) $request->input('page_path', '/');
            $parsedPath = parse_url($rawPagePath, PHP_URL_PATH) ?? '/';
            $pagePath = '/' . ltrim($parsedPath, '/');

            // 4. Source & UTM Classification
            $utmSource = $request->input('utm_source') ?: $request->query('utm_source');
            $utmMedium = $request->input('utm_medium') ?: $request->query('utm_medium');
            $utmCampaign = $request->input('utm_campaign') ?: $request->query('utm_campaign');
            $fbclid = $request->input('fbclid') ?: $request->query('fbclid');
            $gclid = $request->input('gclid') ?: $request->query('gclid');
            $referrer = (string) $request->header('referer', '');

            $source = $this->classifySource((string) $utmSource, (string) $fbclid, (string) $gclid, $referrer, $request->getHost());

            // 5. Device Classification
            $userAgent = (string) $request->header('User-Agent', '');
            $device = $this->classifyDevice($userAgent);

            // 6. Record Event into traffic_events table
            DB::table('traffic_events')->insert([
                'visitor_id' => substr($visitorId, 0, 64),
                'event_type' => $eventType,
                'page_path' => substr($pagePath, 0, 255),
                'source' => $source,
                'utm_source' => $utmSource ? substr((string) $utmSource, 0, 100) : null,
                'utm_medium' => $utmMedium ? substr((string) $utmMedium, 0, 100) : null,
                'utm_campaign' => $utmCampaign ? substr((string) $utmCampaign, 0, 100) : null,
                'device' => $device,
                'created_at' => now(),
            ]);

            $response = response()->noContent();

            // 7. Attach Cookie if new visitor
            if ($isNewVisitor) {
                $response->withCookie(
                    Cookie::make(
                        $cookieName,
                        $visitorId,
                        60 * 24 * 365,
                        '/',
                        null,
                        $request->isSecure(),
                        true,
                        false,
                        'Lax'
                    )
                );
            }

            return $response;
        } catch (\Throwable $e) {
            // Fail-safe: log silently and return 204 No Content
            report($e);
            return response()->noContent();
        }
    }

    /**
     * Classify visitor traffic source into meta_ads | google_organic | direct | referral.
     */
    protected function classifySource(string $utmSource, string $fbclid, string $gclid, string $referrer, string $currentHost): string
    {
        $lowerUtm = strtolower(trim($utmSource));

        // Meta Ads
        if (
            in_array($lowerUtm, ['facebook', 'fb', 'instagram', 'ig', 'meta', 'meta_ads'], true) ||
            !empty($fbclid)
        ) {
            return 'meta_ads';
        }

        // Google Organic
        if (
            in_array($lowerUtm, ['google', 'google_ads'], true) ||
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
}
