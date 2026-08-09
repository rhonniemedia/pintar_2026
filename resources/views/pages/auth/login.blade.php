<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pintar - Masuk</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="font-sans min-h-screen overflow-x-hidden bg-white sm:bg-gray-900" x-data="{ ready: false }" x-init="setTimeout(() => ready = true, 60)">

    {{-- ══════════════════════════════════════════════
         VERSI MOBILE — hero lengkung + sheet, tanpa modal
         ══════════════════════════════════════════════ --}}
    <div class="sm:hidden min-h-screen bg-white" x-data="{ 
        loading: false, 
        showPassword: false,
        loginId: '{{ old('login_id') }}',
        password: '',
        loginIdError: false,
        passwordError: false,
        validate(e) {
            this.loginIdError = this.loginId.trim() === '';
            this.passwordError = this.password === '';
            
            if (this.loginIdError || this.passwordError) {
                e.preventDefault();
            } else {
                this.loading = true;
            }
        }
    }">

        <!-- Hero atas -->
        <div class="relative bg-gradient-to-br from-[#16293f] via-[#1e3a5f] to-[#2c4a6e] pt-16 pb-24 px-6 overflow-hidden">
            <!-- Dekorasi -->
            <div class="absolute -top-10 -right-10 size-44 rounded-full bg-white/10"></div>
            <div class="absolute top-16 -left-14 size-32 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-6 size-16 rounded-full bg-[#ff1443]/20"></div>

            <div class="relative z-10 flex flex-col items-center"
                x-show="ready" x-cloak
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 -translate-y-3"
                x-transition:enter-end="opacity-100 translate-y-0">
                <div class="size-16 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center mb-4 shadow-lg">
                    <i data-lucide="graduation-cap" class="size-8 text-white"></i>
                </div>
                <h1 class="text-white text-xl font-bold tracking-tight">PINTAR</h1>
                <p class="text-white/75 text-xs mt-1 text-center">Platform Informasi Kesiswaan Terintegrasi</p>
            </div>
        </div>

        <!-- Sheet form (menimpa lengkungan hero) -->
        <div class="relative -mt-10 rounded-t-[2rem] bg-white px-6 pt-8 pb-10 min-h-[60vh] shadow-[0_-8px_30px_-15px_rgba(0,0,0,0.15)]"
            x-show="ready" x-cloak
            x-transition:enter="transition ease-out duration-500 delay-100"
            x-transition:enter-start="opacity-0 translate-y-6"
            x-transition:enter-end="opacity-100 translate-y-0">

            <div class="mx-auto mb-6 h-1.5 w-10 rounded-full bg-gray-200"></div>

            <h2 class="text-lg font-semibold text-gray-800 mb-0.5">Masuk ke akun Anda</h2>
            <p class="text-xs text-gray-400 mb-6">Silakan isi email dan kata sandi untuk melanjutkan</p>

            <!-- Alert error umum (Sentralisasi di atas) -->
            @if ($errors->any())
            <div class="mb-5 flex items-start gap-2.5 rounded-xl bg-red-50 border border-red-100 px-3.5 py-2.5 text-red-700 text-xs">
                <i data-lucide="alert-circle" class="size-4 shrink-0 mt-0.5"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            @if (session('status'))
            <div class="mb-5 flex items-start gap-2.5 rounded-xl bg-green-50 border border-green-100 px-3.5 py-2.5 text-green-700 text-xs">
                <i data-lucide="check-circle-2" class="size-4 shrink-0 mt-0.5"></i>
                <span>{{ session('status') }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" @submit="validate($event)" class="space-y-4">
                @csrf

                <!-- Login ID (Username / NIP / Email) -->
                <div>
                    <label for="login_id-m" class="block text-xs font-medium text-gray-500 mb-1.5">Username / NIP / Email</label>
                    <div class="relative">
                        <i data-lucide="user" class="size-4 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input
                            x-model="loginId"
                            @input="loginIdError = false"
                            id="login_id-m"
                            type="text"
                            name="login_id"
                            autofocus
                            autocomplete="username"
                            placeholder="Masukkan identitas..."
                            class="w-full pl-11 pr-4 py-3 rounded-2xl bg-gray-50 border text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#1e3a5f]/25 focus:border-[#1e3a5f] transition"
                            :class="loginIdError ? 'border-red-300' : 'border-transparent'" />
                    </div>
                    <p x-show="loginIdError" x-cloak class="text-xs text-red-600 pt-1.5">Username/email/nip harus diisi!</p>
                </div>

                <!-- Password -->
                <div>
                    <label for="password-m" class="block text-xs font-medium text-gray-500 mb-1.5">Kata Sandi</label>
                    <div class="relative">
                        <i data-lucide="lock" class="size-4 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input
                            x-model="password"
                            @input="passwordError = false"
                            :type="showPassword ? 'text' : 'password'"
                            id="password-m"
                            name="password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full pl-11 pr-11 py-3 rounded-2xl bg-gray-50 border text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#1e3a5f]/25 focus:border-[#1e3a5f] transition"
                            :class="passwordError ? 'border-red-300' : 'border-transparent'" />
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 active:text-gray-600" tabindex="-1">
                            <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="size-4"></i>
                        </button>
                    </div>
                    <p x-show="passwordError" x-cloak class="text-xs text-red-600 pt-1.5">Password harus diisi!</p>
                </div>

                <!-- Ingat saya + lupa password -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-gray-500">
                        <input type="checkbox" name="remember" class="size-3.5 rounded border-gray-300 text-[#1e3a5f] focus:ring-[#1e3a5f]/30" />
                        Ingat saya
                    </label>
                    <a href="{{ Route::has('password.request') ? route('password.request') : '#' }}" class="font-medium text-[#ff1443]">
                        Lupa kata sandi?
                    </a>
                </div>

                <!-- Tombol login -->
                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-br from-[#ff1443] via-[#f0103d] to-[#c70d33] text-white text-sm font-semibold py-3.5 shadow-lg shadow-[#c70d33]/40 transition-all duration-300 ease-out hover:shadow-xl hover:shadow-[#c70d33]/50 hover:brightness-110 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] disabled:opacity-70 disabled:hover:translate-y-0 disabled:hover:brightness-100">
                    <i data-lucide="loader-2" class="size-4 animate-spin" x-show="loading" x-cloak></i>
                    <span x-text="loading ? 'Memproses...' : 'Masuk'"></span>
                </button>

                <!-- Tombol register -->
                @if (Route::has('register'))
                <p class="text-center text-xs text-gray-500 pt-1">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-semibold text-[#1e3a5f]">Daftar di sini</a>
                </p>
                @endif
            </form>

            <div class="mt-8 pt-5 border-t border-gray-100 text-center">
                <p class="text-[11px] text-gray-400">&copy; {{ date('Y') }} Pintar. Seluruh hak cipta dilindungi.</p>
                <p class="text-[11px] text-gray-400 mt-1">
                    Butuh bantuan?
                    <a href="mailto:admin@pintar.sch.id" class="text-[#1e3a5f] font-medium">Hubungi administrator</a>
                </p>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         VERSI DESKTOP — kartu terpusat di atas foto
         ══════════════════════════════════════════════ --}}
    <div class="hidden sm:block relative min-h-screen">

        <!-- Background: ilustrasi perpustakaan bergaya karikatur, sedikit blur -->
        <div class="fixed inset-0 overflow-hidden bg-[#f4efe4]">
            <svg class="absolute inset-0 w-full h-full scale-110 blur-sm" viewBox="0 0 1600 900" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="sky" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#f4efe4" />
                        <stop offset="100%" stop-color="#e7ddc7" />
                    </linearGradient>
                </defs>
                <rect width="1600" height="900" fill="url(#sky)" />

                <!-- Lantai -->
                <rect x="0" y="740" width="1600" height="160" fill="#d8c9a3" />
                <rect x="0" y="740" width="1600" height="10" fill="#c9b98d" />

                <!-- Rak buku kiri -->
                <g>
                    <rect x="40" y="140" width="420" height="620" rx="14" fill="#5c3d2e" />
                    <rect x="60" y="160" width="380" height="150" rx="6" fill="#7a5340" />
                    <rect x="60" y="330" width="380" height="150" rx="6" fill="#7a5340" />
                    <rect x="60" y="500" width="380" height="150" rx="6" fill="#7a5340" />
                    <!-- buku-buku warna-warni -->
                    <g>
                        <rect x="72" y="175" width="26" height="120" fill="#8f5a3c" />
                        <rect x="100" y="180" width="22" height="115" fill="#6b8f71" />
                        <rect x="124" y="172" width="24" height="123" fill="#c9a227" />
                        <rect x="150" y="182" width="22" height="113" fill="#7d8fae" />
                        <rect x="174" y="178" width="26" height="117" fill="#b5651d" />
                        <rect x="202" y="184" width="20" height="111" fill="#5c7d8a" />
                        <rect x="224" y="176" width="24" height="119" fill="#a8577e" />
                        <rect x="250" y="180" width="22" height="115" fill="#6b8f71" />
                        <rect x="274" y="174" width="26" height="121" fill="#1e3a5f" />
                        <rect x="302" y="182" width="20" height="113" fill="#c9a227" />
                        <rect x="324" y="177" width="24" height="118" fill="#7a5340" />
                        <rect x="350" y="181" width="22" height="114" fill="#8f5a3c" />
                        <rect x="374" y="175" width="26" height="120" fill="#5c7d8a" />
                        <rect x="402" y="183" width="20" height="112" fill="#b5651d" />
                    </g>
                    <g>
                        <rect x="72" y="345" width="24" height="120" fill="#1e3a5f" />
                        <rect x="98" y="350" width="22" height="115" fill="#c9a227" />
                        <rect x="122" y="342" width="26" height="123" fill="#7d8fae" />
                        <rect x="150" y="352" width="20" height="113" fill="#a8577e" />
                        <rect x="172" y="348" width="24" height="117" fill="#6b8f71" />
                        <rect x="198" y="354" width="22" height="111" fill="#8f5a3c" />
                        <rect x="222" y="346" width="26" height="119" fill="#5c7d8a" />
                        <rect x="250" y="350" width="20" height="115" fill="#b5651d" />
                        <rect x="272" y="344" width="24" height="121" fill="#c9a227" />
                        <rect x="298" y="352" width="22" height="113" fill="#1e3a5f" />
                        <rect x="322" y="347" width="26" height="118" fill="#6b8f71" />
                        <rect x="350" y="351" width="20" height="114" fill="#7a5340" />
                        <rect x="372" y="345" width="26" height="120" fill="#a8577e" />
                        <rect x="400" y="353" width="20" height="112" fill="#5c7d8a" />
                    </g>
                    <g>
                        <rect x="72" y="515" width="26" height="120" fill="#7d8fae" />
                        <rect x="100" y="520" width="22" height="115" fill="#8f5a3c" />
                        <rect x="124" y="512" width="24" height="123" fill="#c9a227" />
                        <rect x="150" y="522" width="22" height="113" fill="#6b8f71" />
                        <rect x="174" y="518" width="26" height="117" fill="#1e3a5f" />
                        <rect x="202" y="524" width="20" height="111" fill="#a8577e" />
                        <rect x="224" y="516" width="24" height="119" fill="#5c7d8a" />
                        <rect x="250" y="520" width="22" height="115" fill="#b5651d" />
                        <rect x="274" y="514" width="26" height="121" fill="#c9a227" />
                        <rect x="302" y="522" width="20" height="113" fill="#7a5340" />
                        <rect x="324" y="517" width="24" height="118" fill="#6b8f71" />
                        <rect x="350" y="521" width="22" height="114" fill="#1e3a5f" />
                        <rect x="374" y="515" width="26" height="120" fill="#8f5a3c" />
                        <rect x="402" y="523" width="20" height="112" fill="#7d8fae" />
                    </g>
                </g>

                <!-- Rak buku kanan -->
                <g>
                    <rect x="1140" y="180" width="420" height="580" rx="14" fill="#5c3d2e" />
                    <rect x="1160" y="200" width="380" height="150" rx="6" fill="#7a5340" />
                    <rect x="1160" y="370" width="380" height="150" rx="6" fill="#7a5340" />
                    <rect x="1160" y="540" width="380" height="100" rx="6" fill="#7a5340" />
                    <g>
                        <rect x="1172" y="215" width="24" height="120" fill="#6b8f71" />
                        <rect x="1198" y="220" width="22" height="115" fill="#c9a227" />
                        <rect x="1222" y="212" width="26" height="123" fill="#a8577e" />
                        <rect x="1250" y="222" width="20" height="113" fill="#5c7d8a" />
                        <rect x="1272" y="218" width="24" height="117" fill="#1e3a5f" />
                        <rect x="1298" y="224" width="22" height="111" fill="#8f5a3c" />
                        <rect x="1322" y="216" width="26" height="119" fill="#7d8fae" />
                        <rect x="1350" y="220" width="20" height="115" fill="#c9a227" />
                        <rect x="1372" y="214" width="24" height="121" fill="#b5651d" />
                        <rect x="1398" y="222" width="22" height="113" fill="#6b8f71" />
                        <rect x="1422" y="217" width="26" height="118" fill="#1e3a5f" />
                        <rect x="1450" y="221" width="20" height="114" fill="#7a5340" />
                        <rect x="1474" y="215" width="26" height="120" fill="#5c7d8a" />
                        <rect x="1502" y="223" width="20" height="112" fill="#a8577e" />
                    </g>
                    <g>
                        <rect x="1172" y="385" width="24" height="120" fill="#c9a227" />
                        <rect x="1198" y="390" width="22" height="115" fill="#1e3a5f" />
                        <rect x="1222" y="382" width="26" height="123" fill="#6b8f71" />
                        <rect x="1250" y="392" width="20" height="113" fill="#8f5a3c" />
                        <rect x="1272" y="388" width="24" height="117" fill="#a8577e" />
                        <rect x="1298" y="394" width="22" height="111" fill="#5c7d8a" />
                        <rect x="1322" y="386" width="26" height="119" fill="#b5651d" />
                        <rect x="1350" y="390" width="20" height="115" fill="#7d8fae" />
                        <rect x="1372" y="384" width="24" height="121" fill="#1e3a5f" />
                        <rect x="1398" y="392" width="22" height="113" fill="#c9a227" />
                        <rect x="1422" y="387" width="26" height="118" fill="#7a5340" />
                        <rect x="1450" y="391" width="20" height="114" fill="#6b8f71" />
                        <rect x="1474" y="385" width="26" height="120" fill="#8f5a3c" />
                        <rect x="1502" y="393" width="20" height="112" fill="#5c7d8a" />
                    </g>
                </g>

                <!-- Jendela lengkung di tengah belakang -->
                <g opacity="0.9">
                    <path d="M700 620 L700 320 Q700 210 800 210 Q900 210 900 320 L900 620 Z" fill="#fbf6ea" stroke="#c9a227" stroke-width="6" />
                    <line x1="800" y1="210" x2="800" y2="620" stroke="#c9a227" stroke-width="4" />
                    <line x1="700" y1="420" x2="900" y2="420" stroke="#c9a227" stroke-width="4" />
                </g>

                <!-- Meja baca -->
                <rect x="620" y="640" width="360" height="26" rx="8" fill="#7a5340" />
                <rect x="640" y="666" width="18" height="90" fill="#5c3d2e" />
                <rect x="942" y="666" width="18" height="90" fill="#5c3d2e" />

                <!-- Tumpukan buku dengan topi wisuda di atas meja -->
                <g>
                    <rect x="735" y="600" width="150" height="24" rx="4" fill="#1e3a5f" transform="rotate(-2 735 600)" />
                    <rect x="742" y="578" width="140" height="24" rx="4" fill="#b5651d" transform="rotate(1.5 742 578)" />
                    <rect x="738" y="556" width="145" height="24" rx="4" fill="#6b8f71" transform="rotate(-1 738 556)" />
                    <!-- Buku terbuka -->
                    <path d="M745 552 Q800 536 855 552 L855 540 Q800 524 745 540 Z" fill="#fbf6ea" stroke="#c9a227" stroke-width="2" />
                    <!-- Topi wisuda -->
                    <g transform="translate(800 520)">
                        <ellipse cx="0" cy="6" rx="30" ry="10" fill="#1e3a5f" />
                        <polygon points="-46,0 46,0 0,-24" fill="#16293f" />
                        <circle cx="0" cy="-24" r="5" fill="#c9a227" />
                        <line x1="0" y1="-19" x2="24" y2="8" stroke="#c9a227" stroke-width="3" />
                        <circle cx="24" cy="10" r="5" fill="#c9a227" />
                    </g>
                </g>

                <!-- Burung hantu kecil bertengger, maskot edukasi -->
                <g transform="translate(1020 560)">
                    <ellipse cx="0" cy="20" rx="38" ry="46" fill="#7d8fae" />
                    <circle cx="-14" cy="-6" r="16" fill="#fbf6ea" />
                    <circle cx="14" cy="-6" r="16" fill="#fbf6ea" />
                    <circle cx="-14" cy="-6" r="7" fill="#1e3a5f" />
                    <circle cx="14" cy="-6" r="7" fill="#1e3a5f" />
                    <polygon points="-6,6 6,6 0,16" fill="#c9a227" />
                    <polygon points="-10,-26 -2,-26 -6,-40" fill="#7d8fae" />
                    <polygon points="10,-26 2,-26 6,-40" fill="#7d8fae" />
                </g>

                <!-- Tanaman pot -->
                <g transform="translate(560 660)">
                    <path d="M-16 40 L16 40 L10 0 L-10 0 Z" fill="#b5651d" />
                    <ellipse cx="0" cy="-20" rx="30" ry="34" fill="#6b8f71" />
                    <ellipse cx="-18" cy="-4" rx="16" ry="22" fill="#5a7a60" />
                    <ellipse cx="18" cy="-4" rx="16" ry="22" fill="#5a7a60" />
                </g>

                <!-- Bola lampu gantung -->
                <circle cx="500" cy="230" r="20" fill="#c9a227" opacity="0.85" />
                <line x1="500" y1="90" x2="500" y2="212" stroke="#8f5a3c" stroke-width="4" />
            </svg>
        </div>
        <div class="fixed inset-0 bg-gradient-to-b from-[#1e3a5f]/75 via-[#1e3a5f]/68 to-[#16293f]/80"></div>

        <div class="relative z-10 min-h-screen flex flex-col items-center justify-center p-5">
            <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-8" x-data="{ 
                loading: false, 
                showPassword: false,
                loginId: '{{ old('login_id') }}',
                password: '',
                loginIdError: false,
                passwordError: false,
                validate(e) {
                    this.loginIdError = this.loginId.trim() === '';
                    this.passwordError = this.password === '';
                    
                    if (this.loginIdError || this.passwordError) {
                        e.preventDefault();
                    } else {
                        this.loading = true;
                    }
                }
            }">

                <!-- Logo -->
                <div class="flex justify-center mb-4">
                    <div class="size-20 rounded-full bg-white border-4 border-gray-100 shadow-sm flex items-center justify-center">
                        <div class="size-14 rounded-full bg-gradient-to-br from-[#ff1443] to-[#c70d33] flex items-center justify-center">
                            <i data-lucide="graduation-cap" class="size-7 text-white"></i>
                        </div>
                    </div>
                </div>

                <h2 class="text-center text-xl font-bold tracking-tight bg-gradient-to-br from-[#ff1443] to-[#c70d33] bg-clip-text text-transparent">PINTAR</h2>
                <p class="text-center text-xs text-gray-400 mb-6">Platform Informasi Kesiswaan Terintegrasi</p>

                <!-- Alert error umum (Sentralisasi di atas) -->
                @if ($errors->any())
                <div class="mb-5 flex items-start gap-2.5 rounded-lg bg-red-50 border border-red-100 px-3.5 py-2.5 text-red-700 text-xs">
                    <i data-lucide="alert-circle" class="size-4 shrink-0 mt-0.5"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                @if (session('status'))
                <div class="mb-5 flex items-start gap-2.5 rounded-lg bg-green-50 border border-green-100 px-3.5 py-2.5 text-green-700 text-xs">
                    <i data-lucide="check-circle-2" class="size-4 shrink-0 mt-0.5"></i>
                    <span>{{ session('status') }}</span>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" @submit="validate($event)" class="space-y-1">
                    @csrf

                    <!-- Login ID (Username / NIP / Email) -->
                    <div>
                        <label for="login_id" class="block text-xs font-medium text-gray-600 mb-1.5">Username / NIP / Email</label>
                        <div class="relative">
                            <i data-lucide="user" class="size-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input
                                x-model="loginId"
                                @input="loginIdError = false"
                                id="login_id"
                                type="text"
                                name="login_id"
                                autofocus
                                autocomplete="username"
                                placeholder="Masukkan identitas..."
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]/30 focus:border-[#1e3a5f] transition"
                                :class="loginIdError ? 'border-red-300' : 'border-gray-200'" />
                        </div>
                        <p x-show="loginIdError" x-cloak class="text-xs text-red-600 pt-1">Username/email/nip harus diisi!</p>
                    </div>

                    <!-- Password -->
                    <div class="mt-5">
                        <label for="password" class="block text-xs font-medium text-gray-600 mb-1.5">Password</label>
                        <div class="relative">
                            <i data-lucide="lock" class="size-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input
                                x-model="password"
                                @input="passwordError = false"
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                name="password"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full pl-10 pr-11 py-2.5 rounded-xl border text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]/30 focus:border-[#1e3a5f] transition"
                                :class="passwordError ? 'border-red-300' : 'border-gray-200'" />
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" tabindex="-1">
                                <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="size-4"></i>
                            </button>
                        </div>
                        <p x-show="passwordError" x-cloak class="text-xs text-red-600 pt-1">Password harus diisi!</p>
                    </div>

                    <!-- Ingat saya + lupa password -->
                    <div class="flex items-center justify-between pt-4 pb-5 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer select-none text-gray-500">
                            <input type="checkbox" name="remember" class="size-3.5 rounded border-gray-300 text-[#1e3a5f] focus:ring-[#1e3a5f]/30" />
                            Biarkan tetap masuk
                        </label>
                        <a href="{{ Route::has('password.request') ? route('password.request') : '#' }}" class="font-medium text-[#ff1443] hover:underline">
                            Lupa Password?
                        </a>
                    </div>

                    <!-- Tombol login -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full flex items-center justify-center gap-2 rounded-lg bg-gradient-to-br from-[#ff1443] via-[#f0103d] to-[#c70d33] text-white text-sm font-semibold py-2.5 shadow-md shadow-[#c70d33]/40 transition-all duration-300 ease-out hover:shadow-lg hover:shadow-[#c70d33]/50 hover:brightness-110 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:brightness-100">
                        <i data-lucide="loader-2" class="size-4 animate-spin" x-show="loading" x-cloak></i>
                        <span x-text="loading ? 'Memproses...' : 'Login'"></span>
                    </button>
                </form>

                <!-- Footer kartu -->
                <p class="text-center text-xs text-gray-400 mt-6">
                    &copy; {{ date('Y') }} Pintar. Seluruh hak cipta dilindungi.
                </p>
            </div>

            <!-- Footer halaman -->
            <p class="text-center text-xs text-white/60 mt-6">
                Butuh bantuan masuk?
                <a href="mailto:admin@pintar.sch.id" class="text-white/90 font-medium hover:underline">Hubungi administrator</a>
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
            document.addEventListener('alpine:updated', () => {
                if (window.lucide) lucide.createIcons();
            });
        });
    </script>

</body>

</html>