<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F4F6F4]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Admin Panel Sumber Protein Jogja</title>
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    
    <!-- Local Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#F4F6F4] text-brand-dark font-sans antialiased selection:bg-brand-primary selection:text-white flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        
        <!-- Brand Header -->
        <div class="flex flex-col items-center text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-brand-primary flex items-center justify-center text-white shadow-lg shadow-brand-primary/25 mb-4">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2a5 5 0 0 1 5 5v1a5 5 0 0 1-5 5 5 5 0 0 1-5-5V7a5 5 0 0 1 5-5z" fill="currentColor" fill-opacity="0.2"/>
                    <path d="M12 13v9"/>
                    <path d="M7 17l5 5 5-5"/>
                    <circle cx="12" cy="7" r="3"/>
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-brand-dark tracking-tight">
                Sumber Protein Jogja
            </h1>
            <p class="text-xs font-bold text-emerald-700 uppercase tracking-wider mt-1 flex items-center gap-1.5">
                <span>CMS Management Panel</span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white py-8 px-6 sm:px-10 shadow-xl shadow-gray-200/60 rounded-2xl border border-gray-100">
            
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900 tracking-tight">
                    Masuk ke Akun Admin
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Masukkan kredensial Anda untuk mengelola konten dan katalog toko.
                </p>
            </div>

            <!-- Error Banner -->
            @if (isset($errors) && $errors->any())
                <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm space-y-1" role="alert">
                    <div class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span>Gagal Masuk</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-xs text-red-700 pl-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @elseif (session('error'))
                <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm flex items-start gap-2" role="alert">
                    <svg class="w-4 h-4 shrink-0 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login.authenticate') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Alamat Email
                    </label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           autocomplete="email" 
                           required 
                           autofocus
                           value="{{ old('email') }}" 
                           placeholder="admin@sumberproteinjogja.com"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-900 bg-white placeholder-gray-400 focus:outline-hidden focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all">
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Kata Sandi
                    </label>
                    <input id="password" 
                           name="password" 
                           type="password" 
                           autocomplete="current-password" 
                           required 
                           placeholder="••••••••"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-900 bg-white placeholder-gray-400 focus:outline-hidden focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all">
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-3 px-4 bg-brand-primary hover:bg-brand-primary/90 text-white font-bold text-sm rounded-xl shadow-md shadow-brand-primary/20 hover:shadow-lg hover:shadow-brand-primary/30 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer">
                        <span>Masuk ke Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Card Footer -->
            <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col items-center gap-3 text-center">
                <a href="{{ route('home') }}" class="text-xs text-brand-primary hover:text-brand-primary/80 font-semibold inline-flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Halaman Utama</span>
                </a>
            </div>

        </div>

        <!-- Copyright -->
        <p class="mt-6 text-center text-[11px] text-gray-400">
            Sumber Protein Jogja &copy; {{ date('Y') }} — Internal Administrative System
        </p>

    </div>

</body>
</html>
