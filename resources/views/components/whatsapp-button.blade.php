@php
    $site = $site ?? config('site');
    $cleanAdminWa = preg_replace('/[^0-9]/', '', $site['contact']['admin_whatsapp'] ?? '6281234567890');
    $brandName = $site['brand']['name'] ?? 'Sumber Protein Jogja';
@endphp
<div class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-40">
    <a href="https://wa.me/{{ $cleanAdminWa }}?text=Halo%20{{ urlencode($brandName) }},%20saya%20mau%20tanya%20produk%20dan%20order" 
       target="_blank" 
       rel="noopener noreferrer"
       class="group relative inline-flex items-center gap-2.5 bg-[#25D366] hover:bg-[#1EBE5D] text-white p-3 sm:px-5 sm:py-3.5 rounded-full shadow-floating hover:shadow-2xl active:scale-95 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-[#25D366]/40"
       aria-label="Hubungi Admin WhatsApp {{ $brandName }}">
       
        <!-- Pulse Glow Effect -->
        <span class="absolute -inset-1 rounded-full bg-[#25D366] opacity-40 group-hover:opacity-75 blur-sm animate-pulse-slow"></span>

        <!-- WhatsApp Icon -->
        <svg class="relative w-6 h-6 sm:w-6 sm:h-6 fill-current shrink-0 transform group-hover:scale-110 transition-transform duration-200" viewBox="0 0 24 24">
            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
        </svg>

        <!-- Desktop Text with Online Indicator -->
        <span class="relative hidden sm:inline-block text-sm font-bold tracking-wide">
            Chat WhatsApp
        </span>

        <!-- Online Green Dot -->
        <span class="relative hidden sm:flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-200"></span>
        </span>
    </a>
</div>
