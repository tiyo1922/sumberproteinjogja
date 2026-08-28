@extends('layouts.admin', [
    'title' => 'Dashboard Overview',
    'pageTitle' => 'Dashboard Overview'
])

@section('content')
<script>
window.analyticsInitialPayload = {!! json_encode($analytics, JSON_UNESCAPED_UNICODE) !!};

window.salesAnalyticsDashboard = function(initialPayload) {
    const payload = initialPayload || window.analyticsInitialPayload || {};
    // Chart.js instances are class objects with their own mutable internal state.
    // Keep it outside Alpine's reactive proxy so Chart.js always receives its
    // original instance when the selected period updates.
    let chartInstance = null;
    
    // =========================================================================
    // ANALYTICS ENGINE (Adopted from trafik_line_chart_v2.html logic)
    // Continuous multi-year deterministic daily generator with seasonal curves & noise
    // =========================================================================
    const monthNamesID = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const fullMonthNamesID = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    // Anchor timeline from 2019-01-01 to end of 2026
    const startDate = new Date('2019-01-01T00:00:00');
    const endDate = new Date('2026-12-31T23:59:59');
    const dayCount = Math.floor((endDate - startDate) / 86400000) + 1;

    function seededRandom(seed) {
        let x = Math.sin(seed) * 10000;
        return x - Math.floor(x);
    }

    function genSeries(base, amp, seedOffset, growthPerYear) {
        const arr = [];
        for (let i = 0; i < dayCount; i++) {
            const years = i / 365;
            const growth = base * growthPerYear * years;
            const noise = (seededRandom(i * 12.9898 + seedOffset) - 0.5) * amp;
            const seasonal = Math.sin((i % 365) / 365 * Math.PI * 2 + seedOffset) * amp * 0.3;
            const val = Math.max(0, base + growth + noise + seasonal);
            arr.push(Math.round(val));
        }
        return arr;
    }

    // Generate continuous raw series from 2019
    const rawAdmin = genSeries(30, 20, 1, 0.35);
    const rawOrder = genSeries(45, 28, 2, 0.45);
    const rawVisit = genSeries(220, 120, 3, 0.5);

    // Build timeline dates array
    const timelineDates = [];
    for (let i = 0; i < dayCount; i++) {
        const d = new Date(startDate);
        d.setDate(d.getDate() + i);
        timelineDates.push(d);
    }

    function getDayIndex(d) {
        return Math.floor((d - startDate) / 86400000);
    }

    function sliceStats(fromIdx, toIdxExclusive) {
        return { 
            fromIdx: Math.max(0, fromIdx), 
            toIdx: Math.min(dayCount, toIdxExclusive) 
        };
    }

    function avg(arr) {
        if (!arr.length) return 0;
        return Math.round(arr.reduce((a, b) => a + b, 0) / arr.length);
    }

    function sum(arr) {
        if (!arr.length) return 0;
        return arr.reduce((a, b) => a + b, 0);
    }

    // Exact Verified High-Contrast User Datasets for 2026 Q3
    const explicitMonths2026 = {
        7: { // Juli 2026 (Peak 142)
            v: [60, 72, 55, 81, 67, 92, 74, 88, 63, 79, 95, 71, 86, 102, 77, 91, 108, 82, 97, 115, 89, 104, 118, 94, 123, 101, 129, 110, 136, 116, 142],
            c: [14, 17, 13, 20, 16, 22, 18, 21, 15, 19, 23, 17, 21, 25, 19, 22, 27, 20, 24, 29, 22, 26, 30, 23, 31, 25, 32, 27, 34, 29, 36],
            o: [6, 8, 5, 10, 7, 11, 9, 10, 7, 9, 12, 8, 11, 13, 9, 11, 14, 10, 12, 15, 11, 13, 16, 12, 17, 14, 18, 15, 19, 16, 20]
        },
        8: { // Agustus 2026 (Peak 310)
            v: [120, 138, 151, 142, 165, 178, 192, 155, 149, 171, 183, 176, 194, 205, 188, 213, 221, 198, 207, 225, 241, 230, 218, 252, 267, 248, 274, 289, 271, 295, 310],
            c: [34, 39, 42, 40, 47, 51, 56, 45, 43, 49, 53, 50, 57, 61, 54, 63, 66, 59, 62, 68, 73, 69, 65, 76, 81, 74, 83, 88, 82, 91, 96],
            o: [18, 21, 24, 22, 26, 29, 32, 25, 24, 28, 30, 28, 32, 35, 31, 36, 38, 34, 36, 39, 43, 41, 38, 45, 48, 44, 50, 53, 48, 55, 58]
        },
        9: { // September 2026 (Peak 420)
            v: [180, 205, 190, 220, 235, 210, 248, 225, 260, 242, 275, 290, 268, 301, 280, 315, 298, 325, 310, 342, 330, 350, 365, 340, 378, 355, 390, 372, 405, 420],
            c: [48, 55, 51, 59, 63, 57, 68, 61, 72, 65, 77, 82, 74, 85, 79, 88, 83, 91, 87, 96, 92, 99, 103, 95, 108, 101, 112, 106, 116, 121],
            o: [24, 28, 26, 30, 33, 29, 35, 32, 38, 34, 41, 44, 39, 46, 42, 48, 45, 51, 48, 54, 52, 56, 59, 53, 62, 57, 65, 61, 68, 72]
        }
    };

    // Splice explicit datasets into continuous timeline
    Object.keys(explicitMonths2026).forEach(mStr => {
        const m = parseInt(mStr, 10);
        const firstDay = new Date(2026, m - 1, 1);
        const startIdx = getDayIndex(firstDay);
        const data = explicitMonths2026[m];
        for (let j = 0; j < data.v.length; j++) {
            if (startIdx + j < dayCount) {
                rawVisit[startIdx + j] = data.v[j];
                rawAdmin[startIdx + j] = data.c[j];
                rawOrder[startIdx + j] = data.o[j];
            }
        }
    });

    const sourceRatiosMap = {
        all: { ratio: 1.0, chat_ratio: 0.310, order_ratio: 0.170, repeat_ratio: 0.028, label: 'Semua Traffic' },
        meta_ads: { ratio: 0.52, chat_ratio: 0.365, order_ratio: 0.205, repeat_ratio: 0.026, label: 'Meta Ads' },
        google_organic: { ratio: 0.31, chat_ratio: 0.285, order_ratio: 0.128, repeat_ratio: 0.032, label: 'Google Organic' },
        direct: { ratio: 0.12, chat_ratio: 0.270, order_ratio: 0.120, repeat_ratio: 0.040, label: 'Direct' },
        referral: { ratio: 0.05, chat_ratio: 0.260, order_ratio: 0.115, repeat_ratio: 0.020, label: 'Referral / Other' }
    };

    return {
        // Active Selection State (Default: Bulanan, Agustus 2026, Harian, Semua Traffic)
        activePeriod: payload.initial_period || 'bulanan',
        activeSource: payload.initial_source || 'all',
        activeGranularity: payload.initial_granularity || 'harian',
        
        // Navigation States
        weekOffset: 0,
        selectedYear: 2026,
        selectedMonth: 8, // 1 to 12

        // Slicing Logic from trafik_line_chart_v2.html
        getDailySlice(year, month) {
            const first = new Date(year, month - 1, 1);
            const last = new Date(year, month, 0);
            const fromIdx = Math.max(0, getDayIndex(first));
            const toIdx = Math.min(dayCount, getDayIndex(last) + 1);

            const labels = [];
            const admin = [];
            const order = [];
            const visit = [];

            for (let i = fromIdx; i < toIdx; i++) {
                labels.push(timelineDates[i].getDate() + '');
                admin.push(rawAdmin[i]);
                order.push(rawOrder[i]);
                visit.push(rawVisit[i]);
            }
            return { labels, admin, order, visit };
        },

        getWeeklySlice(weekOff) {
            // Anchor reference week (Offset 0 = 18–24 Aug 2026)
            const anchorEnd = new Date(2026, 7, 24);
            const targetEnd = new Date(anchorEnd);
            targetEnd.setDate(anchorEnd.getDate() + (weekOff * 7));
            const targetStart = new Date(targetEnd);
            targetStart.setDate(targetEnd.getDate() - 6);

            const fromIdx = Math.max(0, getDayIndex(targetStart));
            const toIdx = Math.min(dayCount, getDayIndex(targetEnd) + 1);

            const labels = [];
            const admin = [];
            const order = [];
            const visit = [];

            for (let i = fromIdx; i < toIdx; i++) {
                labels.push(timelineDates[i].getDate() + '');
                admin.push(rawAdmin[i]);
                order.push(rawOrder[i]);
                visit.push(rawVisit[i]);
            }

            const rangeStr = `${targetStart.getDate()} – ${targetEnd.getDate()} ${fullMonthNamesID[targetEnd.getMonth() + 1]} ${targetEnd.getFullYear()}`;
            return { range: rangeStr, labels, admin, order, visit };
        },

        getMonthlySlice(year) {
            const labels = [];
            const admin = [];
            const order = [];
            const visit = [];

            for (let m = 0; m < 12; m++) {
                const first = new Date(year, m, 1);
                const last = new Date(year, m + 1, 0);
                const fromIdx = getDayIndex(first);
                const toIdx = getDayIndex(last) + 1;
                const { fromIdx: f, toIdx: t } = sliceStats(fromIdx, toIdx);

                labels.push(monthNamesID[m]);
                admin.push(t > f ? sum(rawAdmin.slice(f, t)) : 0);
                order.push(t > f ? sum(rawOrder.slice(f, t)) : 0);
                visit.push(t > f ? sum(rawVisit.slice(f, t)) : 0);
            }
            return { labels, admin, order, visit };
        },

        getYearlySlice(fromYear, toYear) {
            const labels = [];
            const admin = [];
            const order = [];
            const visit = [];

            for (let y = fromYear; y <= toYear; y++) {
                const first = new Date(y, 0, 1);
                const last = new Date(y, 11, 31);
                const fromIdx = getDayIndex(first);
                const toIdx = getDayIndex(last) + 1;
                const { fromIdx: f, toIdx: t } = sliceStats(fromIdx, toIdx);

                labels.push(y + '');
                admin.push(t > f ? sum(rawAdmin.slice(f, t)) : 0);
                order.push(t > f ? sum(rawOrder.slice(f, t)) : 0);
                visit.push(t > f ? sum(rawVisit.slice(f, t)) : 0);
            }
            return { labels, admin, order, visit };
        },

        // Computed Properties for Current Selection
        get currentTrendData() {
            const sourceConfig = sourceRatiosMap[this.activeSource] || sourceRatiosMap.all;
            const ratio = sourceConfig.ratio;
            let labels = [];
            let pengunjung = [];
            let chat_admin = [];
            let pesan_order_wa = [];
            let rangeLabel = '';

            if (this.activePeriod === 'mingguan') {
                const weekData = this.getWeeklySlice(this.weekOffset);
                rangeLabel = weekData.range;
                labels = weekData.labels;

                pengunjung = weekData.visit.map(v => Math.round(v * ratio));
                chat_admin = weekData.admin.map(c => Math.round(c * (this.activeSource === 'all' ? 1 : (sourceConfig.chat_ratio / 0.310 * ratio))));
                pesan_order_wa = weekData.order.map(o => Math.round(o * (this.activeSource === 'all' ? 1 : (sourceConfig.order_ratio / 0.170 * ratio))));
            }
            else if (this.activePeriod === 'bulanan') {
                const monthData = this.getDailySlice(this.selectedYear, this.selectedMonth);
                const mName = fullMonthNamesID[this.selectedMonth];
                rangeLabel = `${mName} ${this.selectedYear}`;

                const dailyV = monthData.visit.map(v => Math.round(v * ratio));
                const dailyC = monthData.admin.map(c => Math.round(c * (this.activeSource === 'all' ? 1 : (sourceConfig.chat_ratio / 0.310 * ratio))));
                const dailyO = monthData.order.map(o => Math.round(o * (this.activeSource === 'all' ? 1 : (sourceConfig.order_ratio / 0.170 * ratio))));

                if (this.activeGranularity === 'mingguan') {
                    const daysCount = dailyV.length;
                    labels = ['M1 (1–7)', 'M2 (8–14)', 'M3 (15–21)', 'M4 (22–28)'];
                    pengunjung = [
                        dailyV.slice(0, 7).reduce((a, b) => a + b, 0),
                        dailyV.slice(7, 14).reduce((a, b) => a + b, 0),
                        dailyV.slice(14, 21).reduce((a, b) => a + b, 0),
                        dailyV.slice(21, 28).reduce((a, b) => a + b, 0)
                    ];
                    chat_admin = [
                        dailyC.slice(0, 7).reduce((a, b) => a + b, 0),
                        dailyC.slice(7, 14).reduce((a, b) => a + b, 0),
                        dailyC.slice(14, 21).reduce((a, b) => a + b, 0),
                        dailyC.slice(21, 28).reduce((a, b) => a + b, 0)
                    ];
                    pesan_order_wa = [
                        dailyO.slice(0, 7).reduce((a, b) => a + b, 0),
                        dailyO.slice(7, 14).reduce((a, b) => a + b, 0),
                        dailyO.slice(14, 21).reduce((a, b) => a + b, 0),
                        dailyO.slice(21, 28).reduce((a, b) => a + b, 0)
                    ];

                    if (daysCount > 28) {
                        labels.push(`M5 (29–${daysCount})`);
                        pengunjung.push(dailyV.slice(28, daysCount).reduce((a, b) => a + b, 0));
                        chat_admin.push(dailyC.slice(28, daysCount).reduce((a, b) => a + b, 0));
                        pesan_order_wa.push(dailyO.slice(28, daysCount).reduce((a, b) => a + b, 0));
                    }
                } else {
                    labels = monthData.labels;
                    pengunjung = dailyV;
                    chat_admin = dailyC;
                    pesan_order_wa = dailyO;
                }
            }
            else if (this.activePeriod === 'tahunan') {
                rangeLabel = `Tahun ${this.selectedYear}`;
                const yrData = this.getMonthlySlice(this.selectedYear);
                labels = yrData.labels;

                pengunjung = yrData.visit.map(v => Math.round(v * ratio));
                chat_admin = yrData.admin.map(c => Math.round(c * (this.activeSource === 'all' ? 1 : (sourceConfig.chat_ratio / 0.310 * ratio))));
                pesan_order_wa = yrData.order.map(o => Math.round(o * (this.activeSource === 'all' ? 1 : (sourceConfig.order_ratio / 0.170 * ratio))));
            }
            else if (this.activePeriod === 'semua_tahun') {
                rangeLabel = '2023 – 2026 (Semua Periode Tercatat)';
                const allYrData = this.getYearlySlice(2023, 2026);
                labels = allYrData.labels;

                pengunjung = allYrData.visit.map(v => Math.round(v * ratio));
                chat_admin = allYrData.admin.map(c => Math.round(c * (this.activeSource === 'all' ? 1 : (sourceConfig.chat_ratio / 0.310 * ratio))));
                pesan_order_wa = allYrData.order.map(o => Math.round(o * (this.activeSource === 'all' ? 1 : (sourceConfig.order_ratio / 0.170 * ratio))));
            }

            // Summary Totals calculated from active dataset
            const totalV = pengunjung.reduce((a, b) => a + b, 0);
            const totalC = chat_admin.reduce((a, b) => a + b, 0);
            const totalO = pesan_order_wa.reduce((a, b) => a + b, 0);
            const totalR = Math.max(1, Math.round(totalV * (sourceConfig.repeat_ratio || 0.028)));

            const chatPct = totalV > 0 ? ((totalC / totalV) * 100).toFixed(1).replace('.', ',') : '0,0';
            const orderPct = totalV > 0 ? ((totalO / totalV) * 100).toFixed(1).replace('.', ',') : '0,0';

            const compV = this.activePeriod === 'tahunan' ? '+22,4% vs tahun lalu' : 
                         (this.activePeriod === 'semua_tahun' ? '+89,1% pertumbuhan total' : '+18,4% vs periode sebelumnya');
            const compR = this.activePeriod === 'tahunan' ? '+25,2% vs tahun lalu' : 
                         (this.activePeriod === 'semua_tahun' ? '+114% akumulasi pembeli loyal' : '+15,2% vs periode sebelumnya');

            return {
                rangeLabel: rangeLabel,
                labels: labels,
                chart_data: {
                    pengunjung: pengunjung,
                    chat_admin: chat_admin,
                    pesan_order_wa: pesan_order_wa
                },
                kpis: {
                    pengunjung: {
                        value: new Intl.NumberFormat('id-ID').format(totalV),
                        raw_value: totalV,
                        subtext: compV
                    },
                    chat_admin: {
                        value: new Intl.NumberFormat('id-ID').format(totalC),
                        raw_value: totalC,
                        subtext: `${chatPct}% dari visitor`
                    },
                    pesan_order_wa: {
                        value: new Intl.NumberFormat('id-ID').format(totalO),
                        raw_value: totalO,
                        subtext: `${orderPct}% dari visitor`
                    },
                    repeat_order: {
                        value: new Intl.NumberFormat('id-ID').format(totalR),
                        raw_value: totalR,
                        subtext: compR
                    }
                }
            };
        },

        get kpis() {
            return this.currentTrendData.kpis;
        },

        get trafficSources() {
            const totalVisitors = this.currentTrendData.kpis.pengunjung.raw_value;

            const metaV = Math.round(totalVisitors * 0.52);
            const googleV = Math.round(totalVisitors * 0.31);
            const directV = Math.round(totalVisitors * 0.12);
            const referralV = totalVisitors - metaV - googleV - directV;

            return [
                { name: 'Meta Ads', percentage: 52, visitors: new Intl.NumberFormat('id-ID').format(metaV), color: '#1F6B45' },
                { name: 'Google Organic', percentage: 31, visitors: new Intl.NumberFormat('id-ID').format(googleV), color: '#2563EB' },
                { name: 'Direct', percentage: 12, visitors: new Intl.NumberFormat('id-ID').format(directV), color: '#D97706' },
                { name: 'Referral / Other', percentage: 5, visitors: new Intl.NumberFormat('id-ID').format(referralV), color: '#7C3AED' }
            ];
        },

        get topArticles() {
            const totalVisitors = this.currentTrendData.kpis.pengunjung.raw_value;
            const m = Math.max(0.5, totalVisitors / 1500);

            const baseArticles = [
                { rank: 1, title: 'Cara Memilih Ayam Segar', base: 824, category: 'Edukasi Ayam' },
                { rank: 2, title: 'Manfaat Protein Ayam', base: 617, category: 'Nutrisi & Gizi' },
                { rank: 3, title: 'Cara Menyimpan Daging', base: 491, category: 'Cold Chain Storage' },
                { rank: 4, title: 'Protein untuk Diet', base: 386, category: 'Kesehatan' },
                { rank: 5, title: 'Tips Memasak Ayam', base: 244, category: 'Tips Kuliner' }
            ];

            return baseArticles.map(a => {
                const count = Math.round(a.base * m);
                return {
                    rank: a.rank,
                    title: a.title,
                    category: a.category,
                    unique_readers_formatted: new Intl.NumberFormat('id-ID').format(count)
                };
            });
        },

        getSourceLabel(s) {
            return (sourceRatiosMap[s] || sourceRatiosMap.all).label;
        },

        getMonthName(m) {
            return fullMonthNamesID[m] || 'Agustus';
        },

        init() {
            this.$nextTick(() => {
                this.renderChart();
            });
        },

        // Navigation Handlers
        setPeriod(period) {
            this.activePeriod = period;
            this.scheduleChartUpdate();
        },

        setSource(source) {
            this.activeSource = source;
            this.scheduleChartUpdate();
        },

        setGranularity(granularity) {
            this.activeGranularity = granularity;
            this.scheduleChartUpdate();
        },

        scheduleChartUpdate() {
            this.$nextTick(() => this.updateChart());
        },

        prevWeek() {
            if (this.weekOffset > -2) {
                this.weekOffset--;
                this.scheduleChartUpdate();
            }
        },

        nextWeek() {
            if (this.weekOffset < 1) {
                this.weekOffset++;
                this.scheduleChartUpdate();
            }
        },

        prevMonth() {
            if (this.selectedMonth > 1) {
                this.selectedMonth--;
            } else {
                this.selectedMonth = 12;
                if (this.selectedYear > 2023) this.selectedYear--;
            }
            this.scheduleChartUpdate();
        },

        nextMonth() {
            if (this.selectedMonth < 12) {
                this.selectedMonth++;
            } else {
                this.selectedMonth = 1;
                if (this.selectedYear < 2026) this.selectedYear++;
            }
            this.scheduleChartUpdate();
        },

        prevYear() {
            if (this.selectedYear > 2023) {
                this.selectedYear--;
                this.scheduleChartUpdate();
            }
        },

        nextYear() {
            if (this.selectedYear < 2026) {
                this.selectedYear++;
                this.scheduleChartUpdate();
            }
        },

        // Chart Rendering
        renderChart() {
            const canvas = document.getElementById('salesTrafficChart');
            if (!canvas) return;

            const ChartLib = window.Chart || (typeof Chart !== 'undefined' ? Chart : null);
            if (!ChartLib) {
                setTimeout(() => this.renderChart(), 100);
                return;
            }

            const trend = this.currentTrendData;
            if (!trend || !trend.chart_data) return;

            const labels = trend.labels;
            const chartData = trend.chart_data;

            if (chartInstance) {
                try {
                    chartInstance.destroy();
                } catch (e) {}
                chartInstance = null;
            }

            const ctx = canvas.getContext('2d');

            chartInstance = new ChartLib(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Pengunjung',
                            data: chartData.pengunjung,
                            borderColor: '#1F6B45',
                            backgroundColor: 'rgba(31, 107, 69, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: labels.length > 15 ? 2 : 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#1F6B45',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 1.5,
                        },
                        {
                            label: 'Chat Admin',
                            data: chartData.chat_admin,
                            borderColor: '#D97706',
                            backgroundColor: 'transparent',
                            fill: false,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: labels.length > 15 ? 2 : 3.5,
                            pointHoverRadius: 5.5,
                            pointBackgroundColor: '#D97706',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 1.5,
                        },
                        {
                            label: 'Pesan Order WhatsApp',
                            data: chartData.pesan_order_wa,
                            borderColor: '#0D9488',
                            backgroundColor: 'transparent',
                            fill: false,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: labels.length > 15 ? 2 : 3.5,
                            pointHoverRadius: 5.5,
                            pointBackgroundColor: '#0D9488',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 1.5,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 600,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: '#17231D',
                            titleFont: { family: 'Poppins', size: 12, weight: 'bold' },
                            bodyFont: { family: 'Poppins', size: 11 },
                            padding: 10,
                            cornerRadius: 8,
                            boxPadding: 4,
                            callbacks: {
                                title: (tooltipItems) => {
                                    if (!tooltipItems.length) return '';
                                    const rawLabel = tooltipItems[0].label;
                                    if (this.activePeriod === 'mingguan') {
                                        return `${rawLabel} Agustus 2026`;
                                    } else if (this.activePeriod === 'bulanan') {
                                        if (this.activeGranularity === 'mingguan') {
                                            return `${rawLabel} (${this.getMonthName(this.selectedMonth)} ${this.selectedYear})`;
                                        }
                                        return `${rawLabel} ${this.getMonthName(this.selectedMonth)} ${this.selectedYear}`;
                                    } else if (this.activePeriod === 'tahunan') {
                                        return `Bulan ${rawLabel} ${this.selectedYear}`;
                                    } else {
                                        return `Tahun ${rawLabel}`;
                                    }
                                },
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false,
                            },
                            ticks: {
                                font: { family: 'Poppins', size: 11 },
                                color: '#6B7280',
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 14
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#F3F4F6',
                                drawBorder: false,
                            },
                            ticks: {
                                font: { family: 'Poppins', size: 11 },
                                color: '#6B7280',
                                callback: function(value) {
                                    return new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        }
                    }
                }
            });
        },

        updateChart() {
            if (!chartInstance || !chartInstance.data) {
                this.renderChart();
                return;
            }

            const trend = this.currentTrendData;
            const labels = trend.labels;
            const chartData = trend.chart_data;

            chartInstance.data.labels = labels;
            if (chartInstance.data.datasets && chartInstance.data.datasets.length >= 3) {
                chartInstance.data.datasets[0].data = chartData.pengunjung;
                chartInstance.data.datasets[0].pointRadius = labels.length > 15 ? 2 : 4;
                chartInstance.data.datasets[1].data = chartData.chat_admin;
                chartInstance.data.datasets[1].pointRadius = labels.length > 15 ? 2 : 3.5;
                chartInstance.data.datasets[2].data = chartData.pesan_order_wa;
                chartInstance.data.datasets[2].pointRadius = labels.length > 15 ? 2 : 3.5;
            }

            if (typeof chartInstance.update === 'function') {
                chartInstance.update();
            }
        }
    };
};
</script>

<div x-data="window.salesAnalyticsDashboard(window.analyticsInitialPayload)" 
     class="space-y-6">

    <!-- ======================================================= -->
    <!-- 1. DASHBOARD HEADER & PERIOD SELECTOR (4 MODES)         -->
    <!-- ======================================================= -->
    <div class="flex items-center justify-between pb-2 border-b border-gray-200/80">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h2 class="text-xl font-extrabold text-brand-dark tracking-tight">
                    Dashboard Overview
                </h2>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live Analytics</span>
                </span>
            </div>
            <p class="text-xs text-gray-500 font-medium">
                Pantau traffic dan performa penjualan website
            </p>
        </div>

        <!-- 4 Period Selector Modes: Mingguan, Bulanan, Tahunan, Semua Tahun -->
        <div class="flex items-center gap-1 p-1 bg-white rounded-modern border border-gray-200/80 shadow-2xs">
            <button type="button"
                    @click="setPeriod('mingguan')"
                    :class="activePeriod === 'mingguan' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-600 hover:text-brand-dark hover:bg-gray-100 font-semibold'"
                    class="px-3.5 py-1.5 text-xs rounded-modern-sm transition-all duration-150 cursor-pointer">
                Mingguan
            </button>
            <button type="button"
                    @click="setPeriod('bulanan')"
                    :class="activePeriod === 'bulanan' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-600 hover:text-brand-dark hover:bg-gray-100 font-semibold'"
                    class="px-3.5 py-1.5 text-xs rounded-modern-sm transition-all duration-150 cursor-pointer">
                Bulanan
            </button>
            <button type="button"
                    @click="setPeriod('tahunan')"
                    :class="activePeriod === 'tahunan' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-600 hover:text-brand-dark hover:bg-gray-100 font-semibold'"
                    class="px-3.5 py-1.5 text-xs rounded-modern-sm transition-all duration-150 cursor-pointer">
                Tahunan
            </button>
            <button type="button"
                    @click="setPeriod('semua_tahun')"
                    :class="activePeriod === 'semua_tahun' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-600 hover:text-brand-dark hover:bg-gray-100 font-semibold'"
                    class="px-3.5 py-1.5 text-xs rounded-modern-sm transition-all duration-150 cursor-pointer">
                Semua Tahun
            </button>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 2. PRIMARY KPI CARDS (4 CARDS GRID)                     -->
    <!-- Focus: Unique Visitors, Unique Chat, Unique Order, Repeat-->
    <!-- ======================================================= -->
    <div class="grid grid-cols-4 gap-5">
        
        <!-- KPI 1: PENGUNJUNG (Unique Visitors) -->
        <div class="bg-white p-5 rounded-modern-xl border border-gray-200/80 shadow-2xs hover:border-brand-primary/40 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                    PENGUNJUNG
                </span>
                <div class="w-8 h-8 rounded-modern-sm bg-emerald-50 text-brand-primary flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
            <div class="space-y-1">
                <div class="text-3xl font-extrabold text-brand-dark tracking-tight" x-text="kpis.pengunjung.value">
                    {{ $analytics['kpis']['pengunjung']['value'] }}
                </div>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span x-text="kpis.pengunjung.subtext">{{ $analytics['kpis']['pengunjung']['subtext'] }}</span>
                </div>
            </div>
        </div>

        <!-- KPI 2: CHAT ADMIN (Unique Chat Interest) -->
        <div class="bg-white p-5 rounded-modern-xl border border-gray-200/80 shadow-2xs hover:border-brand-primary/40 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                    CHAT ADMIN
                </span>
                <div class="w-8 h-8 rounded-modern-sm bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
            </div>
            <div class="space-y-1">
                <div class="text-3xl font-extrabold text-brand-dark tracking-tight" x-text="kpis.chat_admin.value">
                    {{ $analytics['kpis']['chat_admin']['value'] }}
                </div>
                <div class="text-xs font-semibold text-gray-500" x-text="kpis.chat_admin.subtext">
                    {{ $analytics['kpis']['chat_admin']['subtext'] }}
                </div>
            </div>
        </div>

        <!-- KPI 3: PESAN ORDER WA (Unique Order Intent) -->
        <div class="bg-white p-5 rounded-modern-xl border border-gray-200/80 shadow-2xs hover:border-brand-primary/40 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                    PESAN ORDER WA
                </span>
                <div class="w-8 h-8 rounded-modern-sm bg-teal-50 text-teal-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="space-y-1">
                <div class="text-3xl font-extrabold text-brand-dark tracking-tight" x-text="kpis.pesan_order_wa.value">
                    {{ $analytics['kpis']['pesan_order_wa']['value'] }}
                </div>
                <div class="text-xs font-semibold text-gray-500" x-text="kpis.pesan_order_wa.subtext">
                    {{ $analytics['kpis']['pesan_order_wa']['subtext'] }}
                </div>
            </div>
        </div>

        <!-- KPI 4: REPEAT ORDER (Repeat Buying Customers) -->
        <div class="bg-white p-5 rounded-modern-xl border border-gray-200/80 shadow-2xs hover:border-brand-primary/40 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                    REPEAT ORDER
                </span>
                <div class="w-8 h-8 rounded-modern-sm bg-blue-50 text-blue-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
            </div>
            <div class="space-y-1">
                <div class="text-3xl font-extrabold text-brand-dark tracking-tight" x-text="kpis.repeat_order.value">
                    {{ $analytics['kpis']['repeat_order']['value'] }}
                </div>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span x-text="kpis.repeat_order.subtext">{{ $analytics['kpis']['repeat_order']['subtext'] }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- ======================================================= -->
    <!-- 3. MAIN ANALYTICS GRAPH: TRAFFIC & SALES INTENT         -->
    <!-- ======================================================= -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-5">
        
        <!-- Graph Header & Source Filters -->
        <div class="flex items-center justify-between flex-wrap gap-4 pb-4 border-b border-gray-100">
            <div>
                <h3 class="text-base font-extrabold text-brand-dark">
                    Traffic & Sales Intent
                </h3>
                <p class="text-xs text-gray-500 font-medium">
                    Perbandingan pengunjung dengan aktivitas menuju WhatsApp.
                </p>
            </div>

            <!-- Traffic Source Filter Pills -->
            <div class="flex items-center gap-1.5 p-1 bg-gray-100/90 rounded-modern border border-gray-200/60">
                <button type="button"
                        @click="setSource('all')"
                        :class="activeSource === 'all' ? 'bg-white text-brand-dark font-bold shadow-xs' : 'text-gray-600 hover:text-brand-dark font-medium'"
                        class="px-3 py-1 text-xs rounded-modern-sm transition-all cursor-pointer">
                    Semua Traffic
                </button>
                <button type="button"
                        @click="setSource('meta_ads')"
                        :class="activeSource === 'meta_ads' ? 'bg-white text-brand-dark font-bold shadow-xs' : 'text-gray-600 hover:text-brand-dark font-medium'"
                        class="px-3 py-1 text-xs rounded-modern-sm transition-all cursor-pointer">
                    Meta Ads
                </button>
                <button type="button"
                        @click="setSource('google_organic')"
                        :class="activeSource === 'google_organic' ? 'bg-white text-brand-dark font-bold shadow-xs' : 'text-gray-600 hover:text-brand-dark font-medium'"
                        class="px-3 py-1 text-xs rounded-modern-sm transition-all cursor-pointer">
                    Google Organic
                </button>
                <button type="button"
                        @click="setSource('direct')"
                        :class="activeSource === 'direct' ? 'bg-white text-brand-dark font-bold shadow-xs' : 'text-gray-600 hover:text-brand-dark font-medium'"
                        class="px-3 py-1 text-xs rounded-modern-sm transition-all cursor-pointer">
                    Direct
                </button>
                <button type="button"
                        @click="setSource('referral')"
                        :class="activeSource === 'referral' ? 'bg-white text-brand-dark font-bold shadow-xs' : 'text-gray-600 hover:text-brand-dark font-medium'"
                        class="px-3 py-1 text-xs rounded-modern-sm transition-all cursor-pointer">
                    Referral / Other
                </button>
            </div>
        </div>

        <!-- Dynamic Contextual Navigation & Granularity Bar -->
        <div class="flex items-center justify-between p-2.5 bg-gray-50/80 rounded-modern border border-gray-200/60 text-xs">
            
            <!-- Left: Range Navigator based on active period -->
            <div class="flex items-center gap-2.5">
                
                <!-- Mingguan Navigation -->
                <template x-if="activePeriod === 'mingguan'">
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="prevWeek()" 
                                :disabled="weekOffset <= -2"
                                :class="weekOffset <= -2 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white hover:text-brand-dark cursor-pointer'"
                                class="w-7 h-7 rounded-modern-sm border border-gray-200 bg-white/70 flex items-center justify-center font-bold text-gray-600 transition-colors">
                            ‹
                        </button>
                        <span class="font-extrabold text-brand-dark px-2" x-text="currentTrendData.rangeLabel"></span>
                        <button type="button" 
                                @click="nextWeek()" 
                                :disabled="weekOffset >= 1"
                                :class="weekOffset >= 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white hover:text-brand-dark cursor-pointer'"
                                class="w-7 h-7 rounded-modern-sm border border-gray-200 bg-white/70 flex items-center justify-center font-bold text-gray-600 transition-colors">
                            ›
                        </button>
                    </div>
                </template>

                <!-- Bulanan Navigation -->
                <template x-if="activePeriod === 'bulanan'">
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="prevMonth()" 
                                class="w-7 h-7 rounded-modern-sm border border-gray-200 bg-white/70 hover:bg-white hover:text-brand-dark flex items-center justify-center font-bold text-gray-600 transition-colors cursor-pointer">
                            ‹
                        </button>
                        <span class="font-extrabold text-brand-dark px-2" x-text="currentTrendData.rangeLabel"></span>
                        <button type="button" 
                                @click="nextMonth()" 
                                class="w-7 h-7 rounded-modern-sm border border-gray-200 bg-white/70 hover:bg-white hover:text-brand-dark flex items-center justify-center font-bold text-gray-600 transition-colors cursor-pointer">
                            ›
                        </button>
                    </div>
                </template>

                <!-- Tahunan Navigation -->
                <template x-if="activePeriod === 'tahunan'">
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="prevYear()" 
                                :disabled="selectedYear <= 2023"
                                :class="selectedYear <= 2023 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white hover:text-brand-dark cursor-pointer'"
                                class="w-7 h-7 rounded-modern-sm border border-gray-200 bg-white/70 flex items-center justify-center font-bold text-gray-600 transition-colors">
                            ‹
                        </button>
                        <span class="font-extrabold text-brand-dark px-2" x-text="currentTrendData.rangeLabel"></span>
                        <button type="button" 
                                @click="nextYear()" 
                                :disabled="selectedYear >= 2026"
                                :class="selectedYear >= 2026 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white hover:text-brand-dark cursor-pointer'"
                                class="w-7 h-7 rounded-modern-sm border border-gray-200 bg-white/70 flex items-center justify-center font-bold text-gray-600 transition-colors">
                            ›
                        </button>
                    </div>
                </template>

                <!-- Semua Tahun Display -->
                <template x-if="activePeriod === 'semua_tahun'">
                    <div class="flex items-center gap-2 px-1">
                        <span class="w-2 h-2 rounded-full bg-brand-primary"></span>
                        <span class="font-extrabold text-brand-dark" x-text="currentTrendData.rangeLabel"></span>
                    </div>
                </template>

            </div>

            <!-- Right: Secondary Granularity Switch (For Bulanan Mode) -->
            <div class="flex items-center gap-3">
                <template x-if="activePeriod === 'bulanan'">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] text-gray-500 font-semibold">Tampilan:</span>
                        <div class="flex items-center p-0.5 bg-white rounded-modern-sm border border-gray-200 shadow-2xs">
                            <button type="button"
                                    @click="setGranularity('harian')"
                                    :class="activeGranularity === 'harian' ? 'bg-brand-primary text-white font-bold' : 'text-gray-600 hover:text-brand-dark font-medium'"
                                    class="px-2.5 py-1 text-[11px] rounded transition-all cursor-pointer">
                                Harian
                            </button>
                            <button type="button"
                                    @click="setGranularity('mingguan')"
                                    :class="activeGranularity === 'mingguan' ? 'bg-brand-primary text-white font-bold' : 'text-gray-600 hover:text-brand-dark font-medium'"
                                    class="px-2.5 py-1 text-[11px] rounded transition-all cursor-pointer">
                                Mingguan
                            </button>
                        </div>
                    </div>
                </template>

                <span class="text-[11px] text-gray-500 font-medium">
                    Sumber: <span class="font-bold text-brand-dark" x-text="getSourceLabel(activeSource)"></span>
                </span>
            </div>
        </div>

        <!-- Custom Clean Legend -->
        <div class="flex items-center justify-between text-xs pt-1">
            <div class="flex items-center gap-6">
                <!-- 1. Pengunjung -->
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#1F6B45]"></span>
                    <span class="font-bold text-gray-700">Pengunjung</span>
                    <span class="text-gray-400 font-normal">(Website dikunjungi)</span>
                </div>
                <!-- 2. Chat Admin -->
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#D97706]"></span>
                    <span class="font-bold text-gray-700">Chat Admin</span>
                    <span class="text-gray-400 font-normal">(Menghubungi admin)</span>
                </div>
                <!-- 3. Pesan Order WhatsApp -->
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#0D9488]"></span>
                    <span class="font-bold text-gray-700">Pesan Order WhatsApp</span>
                    <span class="text-gray-400 font-normal">(Order Intent)</span>
                </div>
            </div>

            <!-- Intent Funnel Ratio Badge -->
            <div class="text-[11px] text-gray-500 font-medium bg-gray-50 px-3 py-1 rounded-full border border-gray-200/60">
                <span>Konversi WhatsApp Intent: </span>
                <span class="font-bold text-brand-primary" x-text="kpis.pesan_order_wa.subtext">{{ $analytics['kpis']['pesan_order_wa']['subtext'] }}</span>
            </div>
        </div>

        <!-- Line Chart Container with Guaranteed Height -->
        <div class="relative w-full" style="height: 320px; min-height: 320px;">
            <canvas id="salesTrafficChart" style="width: 100%; height: 100%; display: block;"></canvas>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 4. SUPPORTING SECTION (2 COLUMNS GRID)                  -->
    <!-- Left: Sumber Traffic | Right: Artikel Favorit           -->
    <!-- ======================================================= -->
    <div class="grid grid-cols-2 gap-6">
        
        <!-- Column 1: Sumber Traffic -->
        <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-extrabold text-brand-dark">Sumber Traffic</h3>
                        <p class="text-xs text-gray-500 font-medium">Distribusi asal kanal pengunjung website</p>
                    </div>
                    <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 capitalize" x-text="activePeriod.replace('_', ' ')">
                        Bulanan
                    </span>
                </div>

                <!-- Combined Horizontal Distribution Multi-bar -->
                <div class="my-4">
                    <div class="w-full h-3 rounded-full bg-gray-100 overflow-hidden flex shadow-inner">
                        <template x-for="(source, idx) in trafficSources" :key="source.name">
                            <div :style="`width: ${source.percentage}%; background-color: ${source.color};`"
                                 :title="`${source.name}: ${source.percentage}%`"
                                 class="h-full transition-all duration-300"></div>
                        </template>
                    </div>
                </div>

                <!-- Detailed Rows for Each Channel -->
                <div class="space-y-3 pt-1">
                    <template x-for="source in trafficSources" :key="source.name">
                        <div class="flex items-center justify-between py-1.5 px-2 rounded-lg hover:bg-gray-50/80 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="`background-color: ${source.color}`"></span>
                                <span class="text-xs font-bold text-gray-700" x-text="source.name"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-400 font-medium" x-text="`${source.visitors} visitor`"></span>
                                <span class="text-xs font-extrabold text-brand-dark w-10 text-right" x-text="`${source.percentage}%`"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer summary note -->
            <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                <span>Dominasi traffic dari iklan digital berbayar</span>
                <span class="font-bold text-brand-primary">Meta Ads 52%</span>
            </div>
        </div>

        <!-- Column 2: Artikel Favorit (Supporting Content) -->
        <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-extrabold text-brand-dark">Artikel Favorit</h3>
                        <p class="text-xs text-gray-500 font-medium">Artikel dengan pembaca unik terbanyak.</p>
                    </div>
                    <span class="text-[11px] font-semibold text-gray-400">Top 5 Edukasi</span>
                </div>

                <!-- Top 5 Articles Ranking List -->
                <div class="divide-y divide-gray-100 pt-1">
                    <template x-for="(article, idx) in topArticles" :key="article.rank">
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <!-- Rank Badge -->
                                <span :class="article.rank === 1 ? 'bg-amber-100 text-amber-800 font-extrabold' : 'bg-gray-100 text-gray-600 font-bold'"
                                      class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] shrink-0">
                                    <span x-text="article.rank"></span>
                                </span>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-brand-dark truncate" x-text="article.title"></div>
                                    <div class="text-[10px] text-gray-400 truncate" x-text="article.category"></div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 shrink-0">
                                <div class="text-right">
                                    <span class="text-xs font-extrabold text-brand-dark" x-text="article.unique_readers_formatted"></span>
                                    <span class="text-[10px] text-gray-400 block -mt-0.5">pembaca unik</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer note clarifying supporting status -->
            <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                <span>Konten pendukung edukasi & SEO</span>
                <a href="{{ route('admin.knowledge') }}" class="font-semibold text-brand-primary hover:text-brand-primary-dark transition-colors">
                    Kelola Artikel →
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
