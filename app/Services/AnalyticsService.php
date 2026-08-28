<?php

namespace App\Services;

use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use App\Repositories\MockAnalyticsRepository;

class AnalyticsService
{
    protected AnalyticsRepositoryInterface $repository;

    public function __construct(?AnalyticsRepositoryInterface $repository = null)
    {
        $this->repository = $repository ?? new MockAnalyticsRepository();
    }

    /**
     * Get complete dashboard payload ready for Blade rendering and Alpine.js reactive state.
     * Hardcoded test mode: pure dummy data in memory, zero database queries.
     *
     * @return array
     */
    public function getDashboardPayload(): array
    {
        $initialPeriod = 'bulanan';
        $initialSource = 'all';
        $initialGranularity = 'harian';
        $initialParams = ['year' => 2026, 'month' => 8, 'week_offset' => 0];

        $initialTrend = $this->repository->getTrendData($initialPeriod, $initialParams, $initialSource, $initialGranularity);
        $masterData = $this->repository->getAllDashboardData();

        return [
            'initial_period' => $initialPeriod,
            'initial_source' => $initialSource,
            'initial_granularity' => $initialGranularity,
            'initial_trend' => $initialTrend,
            'kpis' => $initialTrend['kpis'],
            'chart_labels' => $initialTrend['labels'],
            'chart_data' => $initialTrend['chart_data'],
            'range_label' => $initialTrend['range_label'],
            'traffic_sources' => $initialTrend['traffic_sources'],
            'top_articles' => $initialTrend['top_articles'],
            'master_data' => $masterData,
        ];
    }

    /**
     * Get specific trend & KPI data slice.
     */
    public function getTrendData(string $period = 'bulanan', array $params = [], string $source = 'all', string $granularity = 'harian'): array
    {
        return $this->repository->getTrendData($period, $params, $source, $granularity);
    }

    /**
     * Get top articles.
     */
    public function getTopArticles(string $period = 'bulanan', string $source = 'all', int $limit = 5): array
    {
        return $this->repository->getTopArticles($period, $source, $limit);
    }
}
