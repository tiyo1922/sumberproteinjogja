@extends('layouts.admin', [
    'title' => $moduleName ?? 'Modul CMS',
    'pageTitle' => $moduleName ?? 'Modul CMS'
])

@section('content')
<div class="space-y-6">
    
    <!-- Module Header Banner -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-8 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2.5">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-brand-dark tracking-tight">
                        {{ $moduleName }}
                    </h2>
                    
                    @if(isset($moduleType))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                        {{ $moduleType === 'DYNAMIC' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                        {{ $moduleType === 'HYBRID' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}
                        {{ $moduleType === 'FIXED' ? 'bg-gray-100 text-gray-700 border border-gray-200' : '' }}">
                        {{ $moduleType }}
                    </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed max-w-2xl">
                    {{ $desc ?? 'Modul pengelolaan konten landing page Sumber Protein Jogja.' }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>

        <!-- Placeholder Canvas State -->
        <div class="py-16 text-center max-w-md mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-brand-soft-green border border-brand-soft-green-border flex items-center justify-center text-brand-primary mx-auto mb-4 shadow-2xs">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            
            <h3 class="text-base font-bold text-brand-dark mb-2">
                Prototype UI Modul: {{ $moduleName }}
            </h3>
            
            <p class="text-xs text-gray-500 mb-6 leading-relaxed">
                Prinsip CMS: <span class="font-bold text-brand-dark">"CONTENT FLEXIBLE, LAYOUT LOCKED"</span>. Form editor, preview real-time, dan kontrol konten untuk modul ini akan dibangun pada tahap screen berikutnya.
            </p>

            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-50 border border-gray-200 text-xs text-gray-600 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Siap diimplementasikan di iterasi selanjutnya</span>
            </div>
        </div>

    </div>

</div>
@endsection
