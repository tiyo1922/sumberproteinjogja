<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand & Website Identity
    |--------------------------------------------------------------------------
    |
    | Canonical brand information, site naming, logo, favicon, and tagline.
    |
    */
    'brand' => [
        'site_name' => 'Sumber Protein Jogja',
        'name' => 'Sumber Protein Jogja',
        'short_name' => 'Sumber Protein',
        'tagline' => 'Penyedia Bahan Segar & Frozen Food Terpercaya di Jogja',
        'description' => 'Penyedia bahan makanan mentah, frozen food, dan olahan ready-to-cook berkualitas di Yogyakarta. Melayani kebutuhan konsumsi harian keluarga dan suplai horeka/curah.',
        'tab_title_pattern' => '{page_title} — Sumber Protein Jogja',
        'logo_url' => 'storage/media/hero-1.jpg',
        'favicon_url' => 'storage/media/hero-1.jpg',
    ],

    /*
    |--------------------------------------------------------------------------
    | Centralized Contact & WhatsApp Destinations
    |--------------------------------------------------------------------------
    |
    | Single source of truth for WhatsApp order numbers, admin chat, hotline,
    | and official customer care email.
    |
    */
    'contact' => [
        'whatsapp' => '6281234567890',
        'order_whatsapp' => '6281234567891',
        'admin_whatsapp' => '6281234567890',
        'cs_whatsapp' => '6281234567892',
        'phone' => '+62 812-3456-7890',
        'office_phone' => '(0274) 889977',
        'email' => 'halo@sumberproteinjogja.id',
        'status' => 'Aktif',
    ],

    /*
    |--------------------------------------------------------------------------
    | Master Contact Registry
    |--------------------------------------------------------------------------
    |
    | Centralized registry of contact entities, divisions, and channels.
    | Other sections and CTA builders reference these contacts by key.
    |
    */
    'contacts' => [
        [
            'id' => 'order_wa',
            'key' => 'order_wa',
            'name' => 'Pemesanan & Order Produk',
            'division' => 'Pemesanan & Kasir',
            'type' => 'whatsapp',
            'value' => '6281234567891',
            'description' => 'Kanal WhatsApp untuk transaksi checkout keranjang belanja produk.',
            'active' => true,
            'is_system' => true,
        ],
        [
            'id' => 'admin_wa',
            'key' => 'admin_wa',
            'name' => 'Customer Service & Konsultasi Admin',
            'division' => 'Customer Care',
            'type' => 'whatsapp',
            'value' => '6281234567890',
            'description' => 'Kanal WhatsApp untuk konsultasi bahan masak, tanya stok, dan informasi toko.',
            'active' => true,
            'is_system' => true,
        ],
        [
            'id' => 'cs_care',
            'key' => 'cs_care',
            'name' => 'WhatsApp Layanan Pelanggan (CS Care)',
            'division' => 'Customer Care',
            'type' => 'whatsapp',
            'value' => '6281234567892',
            'description' => 'Kanal alternatif bantuan dan komplain pelanggan.',
            'active' => true,
            'is_system' => false,
        ],
        [
            'id' => 'main_phone',
            'key' => 'main_phone',
            'name' => 'Hotline Outlet Yogyakarta',
            'division' => 'Toko & Outlet',
            'type' => 'phone',
            'value' => '+62 812-3456-7890',
            'description' => 'Nomor telepon hotline outlet untuk panggilan langsung.',
            'active' => true,
            'is_system' => false,
        ],
        [
            'id' => 'office_phone',
            'key' => 'office_phone',
            'name' => 'Telepon Kantor & Gudang',
            'division' => 'Kantor Operasional',
            'type' => 'phone',
            'value' => '(0274) 889977',
            'description' => 'Telepon tetap kantor & cold storage.',
            'active' => true,
            'is_system' => false,
        ],
        [
            'id' => 'official_email',
            'key' => 'official_email',
            'name' => 'Email Resmi Customer Care',
            'division' => 'Manajemen & CS',
            'type' => 'email',
            'value' => 'halo@sumberproteinjogja.id',
            'description' => 'Alamat email resmi untuk surat bisnis dan layanan umum.',
            'active' => true,
            'is_system' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Official Social Media Channels
    |--------------------------------------------------------------------------
    |
    | Social media profiles for Instagram, TikTok, and Facebook.
    |
    */
    'social' => [
        'instagram' => 'https://instagram.com/sumberproteinjogja',
        'tiktok' => 'https://tiktok.com/@sumberproteinjogja',
        'facebook' => 'https://facebook.com/sumberproteinjogja',
    ],

    /*
    |--------------------------------------------------------------------------
    | Website URL & Legal/Copyright Notice
    |--------------------------------------------------------------------------
    |
    | Canonical domain URL and copyright string for public footer.
    |
    */
    'website' => [
        'url' => 'https://sumberproteinjogja.com',
        'copyright' => 'Sumber Protein Jogja. Hak Cipta Dilindungi.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel CMS Display Settings
    |--------------------------------------------------------------------------
    |
    | Branding strings specific to the administrative control panel.
    |
    */
    'admin_panel' => [
        'panel_name' => 'Sumber Protein CMS',
        'badge_tag' => 'CMS Panel v1.0',
        'footer_note' => 'Sumber Protein Jogja © 2026 • Layout Locked • Content Flexible',
    ],

    /*
    |--------------------------------------------------------------------------
    | Administrator Profile Mock Data
    |--------------------------------------------------------------------------
    |
    | Profile attributes for the active super administrator.
    |
    */
    'admin_user' => [
        'name' => 'Admin Sumber Protein',
        'role' => 'Super Admin',
        'email' => 'admin@sumberproteinjogja.com',
        'phone' => '0812-3456-7890',
        'avatar_text' => 'SP',
        'avatar_image' => 'storage/media/hero-1.jpg',
    ],

];
