<?php

namespace App\Repositories;

use App\Repositories\Contracts\AnalyticsRepositoryInterface;

class MockAnalyticsRepository implements AnalyticsRepositoryInterface
{
    /**
     * Month names in Indonesian.
     */
    private array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    /**
     * Short month names in Indonesian.
     */
    private array $shortMonthNames = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];

    /**
     * Days in each month for non-leap years.
     */
    private array $daysInMonth = [
        1 => 31, 2 => 28, 3 => 31, 4 => 30,
        5 => 31, 6 => 30, 7 => 31, 8 => 31,
        9 => 30, 10 => 31, 11 => 30, 12 => 31
    ];

    /**
     * Source split ratios.
     */
    private array $sourceRatios = [
        'all' => ['ratio' => 1.0, 'chat_ratio' => 0.310, 'order_ratio' => 0.170, 'repeat_ratio' => 0.028, 'label' => 'Semua Traffic'],
        'meta_ads' => ['ratio' => 0.52, 'chat_ratio' => 0.365, 'order_ratio' => 0.205, 'repeat_ratio' => 0.026, 'label' => 'Meta Ads'],
        'google_organic' => ['ratio' => 0.31, 'chat_ratio' => 0.285, 'order_ratio' => 0.128, 'repeat_ratio' => 0.032, 'label' => 'Google Organic'],
        'direct' => ['ratio' => 0.12, 'chat_ratio' => 0.270, 'order_ratio' => 0.120, 'repeat_ratio' => 0.040, 'label' => 'Direct'],
        'referral' => ['ratio' => 0.05, 'chat_ratio' => 0.260, 'order_ratio' => 0.115, 'repeat_ratio' => 0.020, 'label' => 'Referral / Other'],
    ];

    /**
     * Weekly ranges list for navigation.
     */
    private array $weeklyRanges = [
        -2 => ['range' => '4 – 10 Agustus 2026', 'labels' => ['4', '5', '6', '7', '8', '9', '10'], 'v' => [125, 142, 138, 160, 175, 190, 150], 'c' => [35, 41, 40, 48, 52, 59, 44], 'o' => [19, 23, 22, 26, 30, 34, 25]],
        -1 => ['range' => '11 – 17 Agustus 2026', 'labels' => ['11', '12', '13', '14', '15', '16', '17'], 'v' => [140, 165, 152, 185, 210, 235, 175], 'c' => [40, 47, 43, 54, 64, 75, 52], 'o' => [21, 27, 24, 30, 36, 42, 28]],
        0  => ['range' => '18 – 24 Agustus 2026', 'labels' => ['18', '19', '20', '21', '22', '23', '24'], 'v' => [145, 172, 158, 194, 221, 249, 185], 'c' => [41, 49, 45, 57, 68, 79, 55], 'o' => [22, 28, 25, 31, 38, 45, 30]],
        1  => ['range' => '25 – 31 Agustus 2026', 'labels' => ['25', '26', '27', '28', '29', '30', '31'], 'v' => [150, 180, 165, 205, 230, 260, 195], 'c' => [43, 52, 48, 60, 72, 82, 58], 'o' => [24, 30, 27, 33, 40, 48, 32]],
    ];

    /**
     * Distinct Base Daily Patterns for all 12 months of 2026.
     * juli, agustus, and september use EXACT REQUIRED USER DATASETS with distinct peaks (142 vs 310 vs 420).
     */
    private array $monthlyPatterns2026 = [
        1 => [ // Januari 2026 (31 days)
            'v' => [78, 85, 92, 88, 104, 115, 110, 98, 92, 106, 114, 108, 122, 128, 118, 115, 134, 140, 145, 136, 150, 156, 148, 162, 170, 165, 176, 185, 174, 190, 198],
            'c' => [21, 24, 26, 25, 30, 33, 31, 28, 26, 30, 33, 31, 35, 37, 34, 33, 39, 41, 42, 39, 44, 45, 43, 47, 49, 48, 51, 54, 50, 55, 58],
            'o' => [10, 12, 13, 12, 15, 17, 16, 14, 13, 15, 17, 16, 18, 19, 17, 17, 20, 21, 22, 20, 23, 24, 22, 25, 26, 25, 27, 29, 27, 30, 31]
        ],
        2 => [ // Februari 2026 (28 days)
            'v' => [85, 94, 102, 98, 112, 125, 118, 106, 100, 115, 122, 116, 130, 138, 126, 122, 144, 150, 158, 147, 164, 170, 162, 176, 185, 180, 192, 202],
            'c' => [24, 27, 29, 28, 32, 36, 34, 30, 29, 33, 35, 33, 38, 40, 36, 35, 42, 44, 46, 43, 48, 50, 47, 51, 54, 52, 56, 59],
            'o' => [11, 13, 15, 14, 16, 18, 17, 15, 14, 16, 18, 17, 19, 20, 18, 18, 22, 23, 24, 22, 25, 26, 24, 27, 28, 27, 30, 32]
        ],
        3 => [ // Maret 2026 (31 days)
            'v' => [92, 104, 115, 110, 126, 138, 132, 120, 114, 128, 136, 130, 145, 154, 142, 138, 160, 168, 174, 162, 180, 188, 178, 194, 205, 198, 210, 222, 208, 226, 235],
            'c' => [26, 30, 33, 32, 37, 40, 38, 35, 33, 37, 40, 38, 42, 45, 41, 40, 47, 49, 51, 47, 53, 55, 52, 57, 60, 58, 62, 65, 61, 66, 69],
            'o' => [12, 15, 17, 16, 18, 20, 19, 17, 16, 19, 20, 19, 22, 23, 21, 20, 24, 25, 26, 24, 27, 28, 26, 29, 31, 30, 32, 34, 32, 35, 37]
        ],
        4 => [ // April 2026 (30 days)
            'v' => [88, 98, 108, 104, 118, 130, 124, 112, 106, 120, 128, 122, 136, 145, 134, 130, 150, 158, 164, 152, 170, 176, 168, 184, 192, 186, 198, 208, 195, 212],
            'c' => [25, 28, 31, 30, 34, 38, 36, 32, 31, 35, 37, 35, 40, 42, 39, 38, 44, 46, 48, 44, 50, 51, 49, 54, 56, 54, 58, 61, 57, 62],
            'o' => [12, 14, 16, 15, 17, 19, 18, 16, 15, 18, 19, 18, 20, 21, 20, 19, 22, 23, 24, 22, 25, 26, 25, 27, 28, 27, 30, 31, 29, 32]
        ],
        5 => [ // Mei 2026 (31 days)
            'v' => [105, 118, 130, 124, 142, 155, 148, 135, 128, 145, 154, 148, 165, 175, 160, 156, 180, 190, 198, 184, 205, 214, 202, 220, 232, 225, 238, 252, 236, 258, 268],
            'c' => [30, 34, 38, 36, 41, 45, 43, 39, 37, 42, 45, 43, 48, 51, 46, 45, 53, 56, 58, 54, 60, 63, 59, 65, 68, 66, 70, 74, 69, 76, 79],
            'o' => [15, 17, 19, 18, 21, 23, 22, 20, 19, 21, 23, 22, 24, 26, 23, 23, 27, 28, 29, 27, 30, 32, 30, 33, 35, 34, 36, 38, 36, 39, 41]
        ],
        6 => [ // Juni 2026 (30 days)
            'v' => [82, 96, 104, 101, 115, 128, 121, 109, 103, 118, 125, 119, 134, 141, 130, 126, 148, 155, 162, 151, 168, 174, 166, 181, 190, 185, 198, 207, 196, 214],
            'c' => [22, 27, 29, 28, 32, 36, 34, 31, 29, 33, 36, 34, 39, 41, 38, 37, 43, 46, 48, 45, 50, 52, 49, 54, 58, 56, 60, 63, 59, 65],
            'o' => [9, 12, 14, 13, 16, 18, 17, 15, 14, 17, 19, 18, 21, 23, 21, 20, 24, 26, 27, 25, 29, 30, 28, 32, 35, 33, 36, 38, 35, 40]
        ],
        7 => [ // Juli 2026 (31 days) - EXACT REQUIRED USER DATASET (Peak 142)
            'v' => [60, 72, 55, 81, 67, 92, 74, 88, 63, 79, 95, 71, 86, 102, 77, 91, 108, 82, 97, 115, 89, 104, 118, 94, 123, 101, 129, 110, 136, 116, 142],
            'c' => [14, 17, 13, 20, 16, 22, 18, 21, 15, 19, 23, 17, 21, 25, 19, 22, 27, 20, 24, 29, 22, 26, 30, 23, 31, 25, 32, 27, 34, 29, 36],
            'o' => [6, 8, 5, 10, 7, 11, 9, 10, 7, 9, 12, 8, 11, 13, 9, 11, 14, 10, 12, 15, 11, 13, 16, 12, 17, 14, 18, 15, 19, 16, 20]
        ],
        8 => [ // Agustus 2026 (31 days) - EXACT REQUIRED USER DATASET (Peak 310)
            'v' => [120, 138, 151, 142, 165, 178, 192, 155, 149, 171, 183, 176, 194, 205, 188, 213, 221, 198, 207, 225, 241, 230, 218, 252, 267, 248, 274, 289, 271, 295, 310],
            'c' => [34, 39, 42, 40, 47, 51, 56, 45, 43, 49, 53, 50, 57, 61, 54, 63, 66, 59, 62, 68, 73, 69, 65, 76, 81, 74, 83, 88, 82, 91, 96],
            'o' => [18, 21, 24, 22, 26, 29, 32, 25, 24, 28, 30, 28, 32, 35, 31, 36, 38, 34, 36, 39, 43, 41, 38, 45, 48, 44, 50, 53, 48, 55, 58]
        ],
        9 => [ // September 2026 (30 days) - EXACT REQUIRED USER DATASET (Peak 420)
            'v' => [180, 205, 190, 220, 235, 210, 248, 225, 260, 242, 275, 290, 268, 301, 280, 315, 298, 325, 310, 342, 330, 350, 365, 340, 378, 355, 390, 372, 405, 420],
            'c' => [48, 55, 51, 59, 63, 57, 68, 61, 72, 65, 77, 82, 74, 85, 79, 88, 83, 91, 87, 96, 92, 99, 103, 95, 108, 101, 112, 106, 116, 121],
            'o' => [24, 28, 26, 30, 33, 29, 35, 32, 38, 34, 41, 44, 39, 46, 42, 48, 45, 51, 48, 54, 52, 56, 59, 53, 62, 57, 65, 61, 68, 72]
        ],
        10 => [ // Oktober 2026 (31 days)
            'v' => [125, 142, 155, 148, 170, 185, 176, 162, 154, 174, 186, 178, 198, 210, 194, 190, 218, 228, 238, 224, 248, 258, 245, 268, 282, 274, 290, 305, 288, 312, 325],
            'c' => [36, 41, 45, 43, 50, 54, 51, 47, 45, 51, 54, 52, 58, 61, 57, 56, 64, 67, 70, 66, 73, 76, 72, 79, 83, 80, 85, 90, 85, 92, 96],
            'o' => [19, 22, 24, 23, 27, 29, 28, 25, 24, 27, 29, 28, 31, 33, 30, 30, 34, 36, 38, 35, 39, 41, 39, 42, 45, 43, 46, 48, 46, 49, 52]
        ],
        11 => [ // November 2026 (30 days)
            'v' => [135, 152, 166, 158, 182, 198, 188, 174, 165, 186, 198, 190, 212, 225, 208, 204, 234, 245, 256, 240, 265, 276, 262, 288, 302, 294, 312, 328, 310, 335],
            'c' => [39, 44, 48, 46, 53, 58, 55, 51, 48, 54, 58, 55, 62, 66, 61, 60, 69, 72, 75, 70, 78, 81, 77, 85, 89, 86, 92, 96, 91, 98],
            'o' => [21, 24, 26, 25, 29, 31, 30, 27, 26, 29, 31, 30, 33, 35, 33, 32, 37, 39, 41, 38, 42, 44, 41, 46, 48, 47, 49, 52, 49, 53]
        ],
        12 => [ // Desember 2026 (31 days)
            'v' => [145, 165, 180, 172, 198, 215, 205, 190, 180, 204, 218, 208, 232, 248, 228, 224, 256, 268, 280, 264, 290, 304, 288, 316, 332, 324, 342, 360, 340, 368, 385],
            'c' => [42, 48, 53, 50, 58, 63, 60, 56, 53, 60, 64, 61, 68, 73, 67, 66, 75, 79, 82, 78, 85, 89, 85, 93, 98, 95, 101, 106, 100, 108, 113],
            'o' => [23, 26, 29, 27, 31, 34, 32, 30, 28, 32, 35, 33, 37, 39, 36, 35, 41, 43, 45, 42, 46, 48, 46, 50, 53, 51, 54, 57, 54, 59, 61]
        ],
    ];

    /**
     * Yearly datasets for 2023–2026.
     */
    private array $yearlyDatasets = [
        2023 => [
            'visitors' => [1100, 1250, 1320, 1280, 1450, 1520, 1600, 1720, 1680, 1750, 1820, 1910],
            'chat'     => [320, 360, 390, 375, 430, 450, 480, 510, 495, 515, 540, 565],
            'order'    => [145, 160, 180, 170, 195, 210, 220, 235, 225, 240, 255, 275]
        ],
        2024 => [
            'visitors' => [2450, 2680, 3100, 2950, 3420, 3650, 3820, 4100, 3950, 4120, 4310, 4050],
            'chat'     => [710, 780, 910, 860, 1010, 1080, 1130, 1210, 1160, 1220, 1270, 1190],
            'order'    => [340, 370, 430, 410, 480, 510, 540, 580, 550, 580, 610, 570]
        ],
        2025 => [
            'visitors' => [3620, 3950, 4420, 4180, 4980, 5310, 5620, 5950, 6210, 6450, 6720, 6790],
            'chat'     => [1050, 1150, 1290, 1220, 1450, 1550, 1640, 1740, 1810, 1880, 1960, 1980],
            'order'    => [510, 560, 620, 590, 700, 750, 790, 840, 870, 910, 950, 960]
        ],
        2026 => [
            'visitors' => [3820, 4120, 4580, 4210, 5120, 5480, 5760, 6120, 6480, 6720, 7120, 7580],
            'chat'     => [1120, 1280, 1390, 1270, 1580, 1720, 1840, 1950, 2060, 2180, 2310, 2480],
            'order'    => [540, 610, 680, 625, 760, 815, 872, 930, 985, 1040, 1120, 1210]
        ],
    ];

    /**
     * All years requested dataset.
     */
    private array $allYears = [
        'labels'   => ['2023', '2024', '2025', '2026'],
        'visitors' => [18400, 42600, 64200, 81200],
        'chat'     => [4820, 11280, 21310, 26900],
        'order'    => [2110, 5420, 10720, 13800],
    ];

    /**
     * Available years.
     */
    private array $availableYears = [2023, 2024, 2025, 2026];

    /**
     * Get the master dashboard analytics dataset structured for all periods, granularities, and traffic sources.
     */
    public function getAllDashboardData(): array
    {
        return [
            'periods' => ['mingguan', 'bulanan', 'tahunan', 'semua_tahun'],
            'available_years' => $this->availableYears,
            'available_months' => $this->monthNames,
            'weekly_ranges' => $this->weeklyRanges,
            'days_in_month' => $this->daysInMonth,
            'monthly_patterns_2026' => $this->monthlyPatterns2026,
            'yearly_datasets' => $this->yearlyDatasets,
            'all_years_dataset' => $this->allYears,
            'sources' => array_keys($this->sourceRatios),
            'top_articles_master' => $this->getTopArticles('bulanan', 'all', 5),
        ];
    }

    /**
     * Get specific trend and KPI data for a period, date range/navigation parameter, source, and granularity.
     */
    public function getTrendData(string $period, array $params = [], string $source = 'all', string $granularity = 'harian'): array
    {
        $sourceConfig = $this->sourceRatios[$source] ?? $this->sourceRatios['all'];
        $ratio = $sourceConfig['ratio'];

        switch ($period) {
            case 'mingguan':
                $offset = (int)($params['week_offset'] ?? 0);
                if (!isset($this->weeklyRanges[$offset])) {
                    $offset = 0;
                }
                $weekMeta = $this->weeklyRanges[$offset];
                $vSlice = $weekMeta['v'];
                $cSlice = $weekMeta['c'];
                $oSlice = $weekMeta['o'];

                $pengunjung = array_map(fn($v) => (int)round($v * $ratio), $vSlice);
                $chat_admin = array_map(fn($c) => (int)round($c * ($source === 'all' ? 1 : $sourceConfig['chat_ratio'] / 0.310 * $ratio)), $cSlice);
                $pesan_order_wa = array_map(fn($o) => (int)round($o * ($source === 'all' ? 1 : $sourceConfig['order_ratio'] / 0.170 * $ratio)), $oSlice);

                $labels = $weekMeta['labels'];
                $rangeLabel = $weekMeta['range'];
                break;

            case 'bulanan':
                $year = (int)($params['year'] ?? 2026);
                $month = (int)($params['month'] ?? 8);
                if ($month < 1 || $month > 12) $month = 8;
                $daysCount = $this->daysInMonth[$month] ?? 31;
                $monthName = $this->monthNames[$month] ?? 'Agustus';
                $rangeLabel = "{$monthName} {$year}";

                $yearFactorMap = [2023 => 0.55, 2024 => 0.70, 2025 => 0.85, 2026 => 1.00];
                $yrFactor = $yearFactorMap[$year] ?? 1.00;

                $basePattern = $this->monthlyPatterns2026[$month] ?? $this->monthlyPatterns2026[8];
                $rawV = array_slice($basePattern['v'], 0, $daysCount);
                $rawC = array_slice($basePattern['c'], 0, $daysCount);
                $rawO = array_slice($basePattern['o'], 0, $daysCount);

                $dailyV = array_map(fn($v) => (int)round($v * $yrFactor * $ratio), $rawV);
                $dailyC = array_map(fn($c) => (int)round($c * $yrFactor * ($source === 'all' ? 1 : $sourceConfig['chat_ratio'] / 0.310 * $ratio)), $rawC);
                $dailyO = array_map(fn($o) => (int)round($o * $yrFactor * ($source === 'all' ? 1 : $sourceConfig['order_ratio'] / 0.170 * $ratio)), $rawO);

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

                    if ($daysCount > 28) {
                        $labels[] = "M5 (29–{$daysCount})";
                        $pengunjung[] = array_sum(array_slice($dailyV, 28, $daysCount - 28));
                        $chat_admin[] = array_sum(array_slice($dailyC, 28, $daysCount - 28));
                        $pesan_order_wa[] = array_sum(array_slice($dailyO, 28, $daysCount - 28));
                    }
                } else {
                    $labels = array_map('strval', range(1, $daysCount));
                    $pengunjung = $dailyV;
                    $chat_admin = $dailyC;
                    $pesan_order_wa = $dailyO;
                }
                break;

            case 'tahunan':
                $year = (int)($params['year'] ?? 2026);
                $yearDataset = $this->yearlyDatasets[$year] ?? $this->yearlyDatasets[2026];
                $rangeLabel = "Tahun {$year}";
                $labels = array_values($this->shortMonthNames);

                $pengunjung = array_map(fn($v) => (int)round($v * $ratio), $yearDataset['visitors']);
                $chat_admin = array_map(fn($c) => (int)round($c * ($source === 'all' ? 1 : $sourceConfig['chat_ratio'] / 0.310 * $ratio)), $yearDataset['chat']);
                $pesan_order_wa = array_map(fn($o) => (int)round($o * ($source === 'all' ? 1 : $sourceConfig['order_ratio'] / 0.170 * $ratio)), $yearDataset['order']);
                break;

            case 'semua_tahun':
            default:
                $rangeLabel = "2023 – 2026 (Semua Periode Tercatat)";
                $labels = $this->allYears['labels'];
                $pengunjung = array_map(fn($v) => (int)round($v * $ratio), $this->allYears['visitors']);
                $chat_admin = array_map(fn($c) => (int)round($c * ($source === 'all' ? 1 : $sourceConfig['chat_ratio'] / 0.310 * $ratio)), $this->allYears['chat']);
                $pesan_order_wa = array_map(fn($o) => (int)round($o * ($source === 'all' ? 1 : $sourceConfig['order_ratio'] / 0.170 * $ratio)), $this->allYears['order']);
                break;
        }

        $totalPengunjung = array_sum($pengunjung);
        $totalChat = array_sum($chat_admin);
        $totalOrder = array_sum($pesan_order_wa);
        $repeatRate = $sourceConfig['repeat_ratio'] ?? 0.028;
        $totalRepeat = max(1, (int)round($totalPengunjung * $repeatRate));

        $chatPercent = $totalPengunjung > 0 ? round(($totalChat / $totalPengunjung) * 100, 1) : 0;
        $orderPercent = $totalPengunjung > 0 ? round(($totalOrder / $totalPengunjung) * 100, 1) : 0;

        $comparisonText = match($period) {
            'tahunan' => '+22,4% vs tahun lalu',
            'semua_tahun' => '+89,1% pertumbuhan total',
            default => '+18,4% vs periode sebelumnya',
        };

        $repeatComparisonText = match($period) {
            'tahunan' => '+25,2% vs tahun lalu',
            'semua_tahun' => '+114% akumulasi pembeli loyal',
            default => '+15,2% vs periode sebelumnya',
        };

        $kpis = [
            'pengunjung' => [
                'value' => number_format($totalPengunjung, 0, ',', '.'),
                'raw_value' => $totalPengunjung,
                'metric_label' => 'PENGUNJUNG',
                'subtext' => $comparisonText,
                'is_positive' => true,
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
                'value' => number_format($totalRepeat, 0, ',', '.'),
                'raw_value' => $totalRepeat,
                'metric_label' => 'REPEAT ORDER',
                'subtext' => $repeatComparisonText,
                'is_positive' => true,
                'concept' => 'DISTINCT customer_key with ORDER_CONFIRMED >= 2',
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
        return [
            [
                'name' => 'Meta Ads',
                'percentage' => 52,
                'visitors' => match($period) {
                    'mingguan' => '688',
                    'bulanan' => '3.346',
                    'tahunan' => '34.377',
                    'semua_tahun' => '107.432',
                    default => '3.346'
                },
                'color' => '#1F6B45',
                'bg_light' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            ],
            [
                'name' => 'Google Organic',
                'percentage' => 31,
                'visitors' => match($period) {
                    'mingguan' => '410',
                    'bulanan' => '1.995',
                    'tahunan' => '20.494',
                    'semua_tahun' => '64.047',
                    default => '1.995'
                },
                'color' => '#2563EB',
                'bg_light' => 'bg-blue-50 text-blue-800 border-blue-200',
            ],
            [
                'name' => 'Direct',
                'percentage' => 12,
                'visitors' => match($period) {
                    'mingguan' => '159',
                    'bulanan' => '772',
                    'tahunan' => '7.933',
                    'semua_tahun' => '24.792',
                    default => '772'
                },
                'color' => '#D97706',
                'bg_light' => 'bg-amber-50 text-amber-800 border-amber-200',
            ],
            [
                'name' => 'Referral / Other',
                'percentage' => 5,
                'visitors' => match($period) {
                    'mingguan' => '67',
                    'bulanan' => '321',
                    'tahunan' => '3.305',
                    'semua_tahun' => '10.330',
                    default => '321'
                },
                'color' => '#7C3AED',
                'bg_light' => 'bg-purple-50 text-purple-800 border-purple-200',
            ],
        ];
    }

    /**
     * Get top articles ranked by distinct unique readers.
     */
    public function getTopArticles(string $period = 'mingguan', string $source = 'all', int $limit = 5): array
    {
        $multiplier = match($period) {
            'mingguan' => 1,
            'bulanan' => 4.15,
            'tahunan' => 50.0,
            'semua_tahun' => 150.0,
            default => 1,
        };

        $baseArticles = [
            ['rank' => 1, 'title' => 'Cara Memilih Ayam Segar', 'base_readers' => 824, 'category' => 'Edukasi Ayam'],
            ['rank' => 2, 'title' => 'Manfaat Protein Ayam', 'base_readers' => 617, 'category' => 'Nutrisi & Gizi'],
            ['rank' => 3, 'title' => 'Cara Menyimpan Daging', 'base_readers' => 491, 'category' => 'Cold Chain Storage'],
            ['rank' => 4, 'title' => 'Protein untuk Diet', 'base_readers' => 386, 'category' => 'Kesehatan'],
            ['rank' => 5, 'title' => 'Tips Memasak Ayam', 'base_readers' => 244, 'category' => 'Tips Kuliner'],
        ];

        $results = [];
        foreach ($baseArticles as $art) {
            $readers = (int)round($art['base_readers'] * $multiplier);
            $results[] = [
                'rank' => $art['rank'],
                'title' => $art['title'],
                'unique_readers' => $readers,
                'unique_readers_formatted' => number_format($readers, 0, ',', '.'),
                'category' => $art['category'],
            ];
        }

        return array_slice($results, 0, $limit);
    }
}
