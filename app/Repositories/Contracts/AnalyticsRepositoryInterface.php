<?php

namespace App\Repositories\Contracts;

interface AnalyticsRepositoryInterface
{
    /**
     * Get the master dashboard analytics dataset structured for all periods, granularities, and traffic sources.
     *
     * @return array
     */
    public function getAllDashboardData(): array;

    /**
     * Get specific trend and KPI data for a period, date range/navigation parameter, source, and granularity.
     *
     * @param string $period 'mingguan' | 'bulanan' | 'tahunan' | 'semua_tahun'
     * @param array $params e.g. ['week_offset' => 0] or ['year' => 2026, 'month' => 8]
     * @param string $source 'all' | 'meta_ads' | 'google_organic' | 'direct' | 'referral'
     * @param string $granularity 'harian' | 'mingguan'
     * @return array
     */
    public function getTrendData(string $period, array $params = [], string $source = 'all', string $granularity = 'harian'): array;

    /**
     * Get traffic sources distribution percentage and visitor count.
     *
     * @param string $period 'mingguan' | 'bulanan' | 'tahunan' | 'semua_tahun'
     * @return array
     */
    public function getTrafficSources(string $period): array;

    /**
     * Get top articles ranked by distinct unique readers.
     *
     * @param string $period 'mingguan' | 'bulanan' | 'tahunan' | 'semua_tahun'
     * @param string $source 'all' | 'meta_ads' | 'google_organic' | 'direct' | 'referral'
     * @param int $limit
     * @return array
     */
    public function getTopArticles(string $period = 'mingguan', string $source = 'all', int $limit = 5): array;
}
