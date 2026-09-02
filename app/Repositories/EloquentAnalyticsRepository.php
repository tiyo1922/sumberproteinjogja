<?php

namespace App\Repositories;

use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EloquentAnalyticsRepository implements AnalyticsRepositoryInterface
{
    /**
     * Indonesian month names.
     */
    private array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    /**
     * Short Indonesian month names.
     */
    private array $shortMonthNames = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];

    /**
     * Channels metadata definitions.
     */
    private array $channelMeta = [
        'meta_ads' => [
            'name' => 'Meta Ads',
            'color' => '#1F6B45',
            'bg_light' => 'bg-emerald-50 text-emerald-800 border-emerald-200'
        ],
        'google_organic' => [
            'name' => 'Google Organic',
            'color' => '#2563EB',
            'bg_light' => 'bg-blue-50 text-blue-800 border-blue-200'
        ],
        'direct' => [
            'name' => 'Direct',
            'color' => '#D97706',
            'bg_light' => 'bg-amber-50 text-amber-800 border-amber-200'
        ],
        'referral' => [
            'name' => 'Referral / Other',
            'color' => '#7C3AED',
            'bg_light' => 'bg-purple-50 text-purple-800 border-purple-200'
        ],
    ];

    /**
     * Get the master dashboard analytics dataset structured for all periods, granularities, and traffic sources.
     */
    public function getAllDashboardData(): array
    {
        $currentYear = (int) date('Y');
        $availableYears = range(max(2023, $currentYear - 3), $currentYear);

        return [
            'periods' => ['mingguan', 'bulanan', 'tahunan', 'semua_tahun'],
            'available_years' => $availableYears,
            'available_months' => $this->monthNames,
            'sources' => ['all', 'meta_ads', 'google_organic', 'direct', 'referral'],
            'top_articles_master' => $this->getTopArticles('bulanan', 'all', 5),
        ];
    }

    /**
     * Get specific trend and KPI data for a period, date range/navigation parameter, source, and granularity.
     */
    public function getTrendData(string $period, array $params = [], string $source = 'all', string $granularity = 'harian'): array
    {
        // Safe whitelist validation for source & period
        $allowedSources = ['all', 'meta_ads', 'google_organic', 'direct', 'referral'];
        if (!in_array($source, $allowedSources, true)) {
            $source = 'all';
        }

        $now = Carbon::now();
        $labels = [];
        $pengunjung = [];
        $chat_admin = [];
        $pesan_order_wa = [];
        $rangeLabel = '';
        $prevVisitors = 0;

        switch ($period) {
            case 'mingguan':
                $offset = (int)($params['week_offset'] ?? 0);
                $endOfWeek = $now->copy()->addWeeks($offset)->endOfDay();
                $startOfWeek = $endOfWeek->copy()->subDays(6)->startOfDay();

                $startPrev = $startOfWeek->copy()->subDays(7);
                $endPrev = $startOfWeek->copy()->subSecond();

                $startMonthName = $this->monthNames[(int)$startOfWeek->format('n')];
                $endMonthName = $this->monthNames[(int)$endOfWeek->format('n')];

                if ($startOfWeek->format('n') === $endOfWeek->format('n')) {
                    $rangeLabel = "{$startOfWeek->format('j')} – {$endOfWeek->format('j')} {$endMonthName} {$endOfWeek->format('Y')}";
                } else {
                    $rangeLabel = "{$startOfWeek->format('j')} {$startMonthName} – {$endOfWeek->format('j')} {$endMonthName} {$endOfWeek->format('Y')}";
                }

                // Query 7 days
                $records = $this->queryDateRangeAggregation($startOfWeek, $endOfWeek, $source, 'DATE(created_at)');
                $prevVisitors = $this->queryUniqueVisitors($startPrev, $endPrev, $source);

                $cursor = $startOfWeek->copy();
                while ($cursor <= $endOfWeek) {
                    $dateKey = $cursor->format('Y-m-d');
                    $labels[] = $cursor->format('j');
                    $row = $records->get($dateKey);
                    $pengunjung[] = $row ? (int)$row->visitors : 0;
                    $chat_admin[] = $row ? (int)$row->chat : 0;
                    $pesan_order_wa[] = $row ? (int)$row->orders : 0;
                    $cursor->addDay();
                }
                break;

            case 'tahunan':
                $year = (int)($params['year'] ?? $now->year);
                $startOfYear = Carbon::create($year, 1, 1, 0, 0, 0);
                $endOfYear = Carbon::create($year, 12, 31, 23, 59, 59);

                $startPrevYear = Carbon::create($year - 1, 1, 1, 0, 0, 0);
                $endPrevYear = Carbon::create($year - 1, 12, 31, 23, 59, 59);

                $rangeLabel = "Tahun {$year}";

                $records = $this->queryDateRangeAggregation($startOfYear, $endOfYear, $source, 'MONTH(created_at)');
                $prevVisitors = $this->queryUniqueVisitors($startPrevYear, $endPrevYear, $source);

                for ($m = 1; $m <= 12; $m++) {
                    $labels[] = $this->shortMonthNames[$m];
                    $row = $records->get($m);
                    $pengunjung[] = $row ? (int)$row->visitors : 0;
                    $chat_admin[] = $row ? (int)$row->chat : 0;
                    $pesan_order_wa[] = $row ? (int)$row->orders : 0;
                }
                break;

            case 'semua_tahun':
                $currentYear = $now->year;
                $startYear = max(2023, $currentYear - 3);
                $startAll = Carbon::create($startYear, 1, 1, 0, 0, 0);
                $endAll = Carbon::create($currentYear, 12, 31, 23, 59, 59);

                $rangeLabel = "{$startYear} – {$currentYear} (Semua Periode Tercatat)";

                $records = $this->queryDateRangeAggregation($startAll, $endAll, $source, 'YEAR(created_at)');
                $prevVisitors = 0; // Not applicable for all years

                for ($y = $startYear; $y <= $currentYear; $y++) {
                    $labels[] = (string)$y;
                    $row = $records->get($y);
                    $pengunjung[] = $row ? (int)$row->visitors : 0;
                    $chat_admin[] = $row ? (int)$row->chat : 0;
                    $pesan_order_wa[] = $row ? (int)$row->orders : 0;
                }
                break;

            case 'bulanan':
            default:
                $period = 'bulanan';
                $year = (int)($params['year'] ?? $now->year);
                $month = (int)($params['month'] ?? $now->month);

                $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0);
                $endOfMonth = $startOfMonth->copy()->endOfMonth();

                $startPrevMonth = $startOfMonth->copy()->subMonth()->startOfMonth();
                $endPrevMonth = $startPrevMonth->copy()->endOfMonth();

                $monthName = $this->monthNames[$month] ?? $this->monthNames[8];
                $rangeLabel = "{$monthName} {$year}";

                $records = $this->queryDateRangeAggregation($startOfMonth, $endOfMonth, $source, 'DAY(created_at)');
                $prevVisitors = $this->queryUniqueVisitors($startPrevMonth, $endPrevMonth, $source);

                $daysInMonth = $endOfMonth->day;
                $dailyV = [];
                $dailyC = [];
                $dailyO = [];

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $row = $records->get($d);
                    $dailyV[] = $row ? (int)$row->visitors : 0;
                    $dailyC[] = $row ? (int)$row->chat : 0;
                    $dailyO[] = $row ? (int)$row->orders : 0;
                }

                if ($granularity === 'mingguan') {
                    $labels = ['M1 (1–7)', 'M2 (8–14)', 'M3 (15–21)', 'M4 (22–28)'];
                    $pengunjung = [
                        array_sum(array_slice($dailyV, 0, 7)),
                        array_sum(array_slice($dailyV, 7, 7)),
                        array_sum(array_slice($dailyV, 14, 7)),
                        array_sum(array_slice($dailyV, 21, 7)),
                    ];
                    $chat_admin = [
                        array_sum(array_slice($dailyC, 0, 7)),
                        array_sum(array_slice($dailyC, 7, 7)),
                        array_sum(array_slice($dailyC, 14, 7)),
                        array_sum(array_slice($dailyC, 21, 7)),
                    ];
                    $pesan_order_wa = [
                        array_sum(array_slice($dailyO, 0, 7)),
                        array_sum(array_slice($dailyO, 7, 7)),
                        array_sum(array_slice($dailyO, 14, 7)),
                        array_sum(array_slice($dailyO, 21, 7)),
                    ];

                    if ($daysInMonth > 28) {
                        $labels[] = "M5 (29–{$daysInMonth})";
                        $pengunjung[] = array_sum(array_slice($dailyV, 28));
                        $chat_admin[] = array_sum(array_slice($dailyC, 28));
                        $pesan_order_wa[] = array_sum(array_slice($dailyO, 28));
                    }
                } else {
                    $granularity = 'harian';
                    $labels = array_map(fn($d) => (string)$d, range(1, $daysInMonth));
                    $pengunjung = $dailyV;
                    $chat_admin = $dailyC;
                    $pesan_order_wa = $dailyO;
                }
                break;
        }

        $totalPengunjung = array_sum($pengunjung);
        $totalChat = array_sum($chat_admin);
        $totalOrder = array_sum($pesan_order_wa);

        $chatPercent = $totalPengunjung > 0 ? round(($totalChat / $totalPengunjung) * 100, 1) : 0;
        $orderPercent = $totalPengunjung > 0 ? round(($totalOrder / $totalPengunjung) * 100, 1) : 0;

        // Comparison text calculation
        $isPositive = true;
        if ($period === 'semua_tahun') {
            $comparisonText = $totalPengunjung > 0 ? '+100% akumulasi pertumbuhan total' : 'Belum ada data tercatat';
        } elseif ($prevVisitors > 0) {
            $diffPct = round((($totalPengunjung - $prevVisitors) / $prevVisitors) * 100, 1);
            $sign = $diffPct >= 0 ? '+' : '';
            $comparisonText = "{$sign}" . str_replace('.', ',', (string)$diffPct) . "% vs periode sebelumnya";
            $isPositive = ($diffPct >= 0);
        } else {
            $comparisonText = 'Belum ada data periode sebelumnya';
            $isPositive = true;
        }

        $kpis = [
            'pengunjung' => [
                'value' => number_format($totalPengunjung, 0, ',', '.'),
                'raw_value' => $totalPengunjung,
                'metric_label' => 'PENGUNJUNG',
                'subtext' => $comparisonText,
                'is_positive' => $isPositive,
                'concept' => 'DISTINCT visitor_key',
            ],
            'chat_admin' => [
                'value' => number_format($totalChat, 0, ',', '.'),
                'raw_value' => $totalChat,
                'metric_label' => 'CHAT ADMIN',
                'subtext' => str_replace('.', ',', (string)$chatPercent) . '% dari visitor',
                'is_positive' => true,
                'concept' => 'DISTINCT visitor_key with CHAT_ADMIN_CLICK',
            ],
            'pesan_order_wa' => [
                'value' => number_format($totalOrder, 0, ',', '.'),
                'raw_value' => $totalOrder,
                'metric_label' => 'PESAN ORDER WA',
                'subtext' => str_replace('.', ',', (string)$orderPercent) . '% dari visitor',
                'is_positive' => true,
                'concept' => 'DISTINCT visitor_key with ORDER_WHATSAPP_CLICK',
            ],
            'repeat_order' => [
                'value' => '0',
                'raw_value' => 0,
                'metric_label' => 'REPEAT ORDER',
                'subtext' => 'Data belum tersedia',
                'is_positive' => true,
                'concept' => 'Data order konfirmasi belum tersedia',
            ],
        ];

        return [
            'period' => $period,
            'source' => $source,
            'granularity' => $granularity,
            'range_label' => $rangeLabel,
            'labels' => $labels,
            'chart_data' => [
                'pengunjung' => $pengunjung,
                'chat_admin' => $chat_admin,
                'pesan_order_wa' => $pesan_order_wa,
            ],
            'kpis' => $kpis,
            'traffic_sources' => $this->getTrafficSources($period),
            'top_articles' => $this->getTopArticles($period, $source, 5),
        ];
    }

    /**
     * Get traffic sources distribution percentage and visitor count.
     */
    public function getTrafficSources(string $period): array
    {
        if (!Schema::hasTable('traffic_events')) {
            return $this->getEmptyTrafficSources();
        }

        $now = Carbon::now();
        $query = DB::table('traffic_events')->where('event_type', 'pageview');

        // Apply period filter if appropriate
        switch ($period) {
            case 'mingguan':
                $query->where('created_at', '>=', $now->copy()->subDays(7));
                break;
            case 'tahunan':
                $query->whereYear('created_at', $now->year);
                break;
            case 'semua_tahun':
                break;
            case 'bulanan':
            default:
                $query->whereYear('created_at', $now->year)->whereMonth('created_at', $now->month);
                break;
        }

        $sourceCounts = $query->select('source', DB::raw('COUNT(DISTINCT visitor_id) as visitors'))
            ->groupBy('source')
            ->pluck('visitors', 'source')
            ->toArray();

        $totalVisitors = array_sum($sourceCounts);

        $results = [];
        foreach ($this->channelMeta as $srcKey => $meta) {
            $visitors = $sourceCounts[$srcKey] ?? 0;
            $percentage = $totalVisitors > 0 ? (int)round(($visitors / $totalVisitors) * 100) : 0;

            $results[] = [
                'name' => $meta['name'],
                'percentage' => $percentage,
                'visitors' => number_format($visitors, 0, ',', '.'),
                'color' => $meta['color'],
                'bg_light' => $meta['bg_light'],
            ];
        }

        return $results;
    }

    /**
     * Get top articles ranked by distinct unique readers.
     */
    public function getTopArticles(string $period = 'mingguan', string $source = 'all', int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));

        if (!Schema::hasTable('traffic_events')) {
            return [];
        }

        $now = Carbon::now();
        $query = DB::table('traffic_events')
            ->where('event_type', 'pageview')
            ->where('page_path', 'LIKE', '/knowledge%');

        if ($source !== 'all') {
            $query->where('source', $source);
        }

        switch ($period) {
            case 'tahunan':
                $query->whereYear('created_at', $now->year);
                break;
            case 'semua_tahun':
                break;
            case 'bulanan':
                $query->whereYear('created_at', $now->year)->whereMonth('created_at', $now->month);
                break;
            case 'mingguan':
            default:
                $query->where('created_at', '>=', $now->copy()->subDays(7));
                break;
        }

        $articleTraffic = $query->select('page_path', DB::raw('COUNT(DISTINCT visitor_id) as readers'))
            ->groupBy('page_path')
            ->orderByDesc('readers')
            ->limit($limit)
            ->get();

        $results = [];
        $rank = 1;

        // If traffic on specific articles exists
        if ($articleTraffic->isNotEmpty()) {
            foreach ($articleTraffic as $row) {
                $path = trim($row->page_path, '/');
                $slug = str_replace('knowledge/', '', $path);
                $title = 'Edukasi Produk & Daging';
                $category = 'Pusat Edukasi';

                // Lookup title & category from knowledge_articles if exists
                if (Schema::hasTable('knowledge_articles')) {
                    $article = DB::table('knowledge_articles')
                        ->leftJoin('knowledge_categories', 'knowledge_articles.category_id', '=', 'knowledge_categories.id')
                        ->where('knowledge_articles.slug', $slug)
                        ->select('knowledge_articles.title', 'knowledge_categories.name as category_name')
                        ->first();

                    if ($article) {
                        $title = $article->title;
                        $category = $article->category_name ?? $category;
                    }
                }

                $readers = (int)$row->readers;
                $results[] = [
                    'rank' => $rank++,
                    'title' => $title,
                    'unique_readers' => $readers,
                    'unique_readers_formatted' => number_format($readers, 0, ',', '.'),
                    'category' => $category,
                ];
            }
        }

        // If no article traffic is recorded yet, fetch top published articles with 0 readers
        if (empty($results) && Schema::hasTable('knowledge_articles')) {
            $fallbackArticles = DB::table('knowledge_articles')
                ->leftJoin('knowledge_categories', 'knowledge_articles.category_id', '=', 'knowledge_categories.id')
                ->select('knowledge_articles.title', 'knowledge_categories.name as category_name')
                ->limit($limit)
                ->get();

            foreach ($fallbackArticles as $art) {
                $results[] = [
                    'rank' => $rank++,
                    'title' => $art->title,
                    'unique_readers' => 0,
                    'unique_readers_formatted' => '0',
                    'category' => $art->category_name ?? 'Edukasi',
                ];
            }
        }

        return array_slice($results, 0, $limit);
    }

    /**
     * Query distinct count aggregation by grouping expression on date range.
     */
    protected function queryDateRangeAggregation(Carbon $startDate, Carbon $endDate, string $source, string $groupExpression)
    {
        if (!Schema::hasTable('traffic_events')) {
            return collect();
        }

        $query = DB::table('traffic_events')
            ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()]);

        if ($source !== 'all') {
            $query->where('source', $source);
        }

        return $query->selectRaw("
            {$groupExpression} as group_key,
            COUNT(DISTINCT CASE WHEN event_type = 'pageview' THEN visitor_id END) as visitors,
            COUNT(DISTINCT CASE WHEN event_type = 'chat_admin' THEN visitor_id END) as chat,
            COUNT(DISTINCT CASE WHEN event_type = 'pesan_order_wa' THEN visitor_id END) as orders
        ")->groupBy(DB::raw($groupExpression))->get()->keyBy('group_key');
    }

    /**
     * Query total unique visitors for a date range.
     */
    protected function queryUniqueVisitors(Carbon $startDate, Carbon $endDate, string $source): int
    {
        if (!Schema::hasTable('traffic_events')) {
            return 0;
        }

        $query = DB::table('traffic_events')
            ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
            ->where('event_type', 'pageview');

        if ($source !== 'all') {
            $query->where('source', $source);
        }

        return (int) $query->distinct('visitor_id')->count('visitor_id');
    }

    /**
     * Empty state representation for traffic sources.
     */
    protected function getEmptyTrafficSources(): array
    {
        $results = [];
        foreach ($this->channelMeta as $meta) {
            $results[] = [
                'name' => $meta['name'],
                'percentage' => 0,
                'visitors' => '0',
                'color' => $meta['color'],
                'bg_light' => $meta['bg_light'],
            ];
        }
        return $results;
    }
}
