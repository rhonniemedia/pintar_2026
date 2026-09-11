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
         VERSI MOBILE — mengikuti referensi (ilustrasi atas + kartu putih)
         ══════════════════════════════════════════════ --}}
    <div class="sm:hidden min-h-screen relative overflow-hidden bg-slate-50" x-data="{ 
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

        <!-- ═══ BACKGROUND DECOR (shape biru di kanan, seperti referensi) ═══ -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-[210px] -right-24 w-72 h-[520px] bg-[#1e3a5f] rounded-[2.5rem] rotate-[14deg]"></div>
            <div class="absolute top-[240px] -right-16 w-64 h-[480px] bg-[#24466f] rounded-[2.5rem] rotate-[14deg] opacity-70"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 rounded-full bg-[#1e3a5f]/5"></div>
            <div class="absolute top-1/2 -left-16 w-32 h-32 rounded-full bg-[#ff1443]/8 blur-2xl"></div>
        </div>

        <!-- ═══ CONTENT ═══ -->
        <div class="relative z-10 flex flex-col px-6 pt-12 pb-8"
            x-show="ready" x-cloak
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0">

            <!-- ═══ TOP: ILUSTRASI + HEADING ═══ -->
            <div class="text-center">
                <!-- Ilustrasi: lock (navy) + person (red) -->
                <div class="flex justify-center mb-5">
                    <svg viewBox="0 0 220 130" class="w-40 h-auto" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <!-- Ground shadow -->
                        <ellipse cx="110" cy="120" rx="82" ry="4.5" fill="#cbd5e1" opacity="0.55" />

                        <!-- Person (right, behind lock slightly) -->
                        <g>
                            <circle cx="150" cy="70" r="18" fill="#ff1443" />
                            <path d="M120 118 Q150 84 180 118 Z" fill="#ff1443" />
                        </g>

                        <!-- Lock body -->
                        <rect x="42" y="58" width="82" height="60" rx="12" fill="#1e3a5f" />
                        <!-- Subtle top highlight -->
                        <rect x="42" y="58" width="82" height="12" rx="12" fill="#ffffff" opacity="0.06" />

                        <!-- Keyhole -->
                        <circle cx="83" cy="86" r="8" fill="#ffffff" />
                        <path d="M79 86 h8 v16 a1.5 1.5 0 0 1 -1.5 1.5 h-5 a1.5 1.5 0 0 1 -1.5 -1.5 z" fill="#ffffff" />

                        <!-- Shackle -->
                        <path d="M58 58 V44 a25 25 0 0 1 50 0 V58" stroke="#1e3a5f" stroke-width="9" fill="none" stroke-linecap="round" />
                    </svg>
                </div>

                <h1 class="text-[26px] font-extrabold text-slate-900 tracking-tight leading-tight">
                    Selamat Datang!
                </h1>
                <p class="text-[13px] text-slate-500 mt-2 mx-auto leading-relaxed max-w-[290px]">
                    Masuk ke PINTAR — Platform Informasi Kesiswaan Terintegrasi
                </p>
            </div>

            <!-- ═══ KARTU FORM ═══ -->
            <div class="mt-8 bg-white rounded-2xl shadow-[0_10px_40px_-12px_rgba(15,23,42,0.18)] ring-1 ring-slate-900/[0.04] p-6">

                <h2 class="text-center text-lg font-bold text-slate-900 tracking-tight mb-6">
                    Login Akun
                </h2>

                <!-- Alert error umum -->
                @if ($errors->any())
                <div class="mb-4 flex items-start gap-2.5 rounded-xl bg-red-50 border border-red-100 px-3.5 py-2.5 text-red-700 text-xs">
                    <i data-lucide="alert-circle" class="size-4 shrink-0 mt-0.5"></i>
                    <span class="leading-relaxed">{{ $errors->first() }}</span>
                </div>
                @endif

                @if (session('status'))
                <div class="mb-4 flex items-start gap-2.5 rounded-xl bg-emerald-50 border border-emerald-100 px-3.5 py-2.5 text-emerald-700 text-xs">
                    <i data-lucide="check-circle-2" class="size-4 shrink-0 mt-0.5"></i>
                    <span class="leading-relaxed">{{ session('status') }}</span>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" @submit="validate($event)" class="space-y-4">
                    @csrf

                    <!-- Login ID -->
                    <div>
                        <label for="login_id-m" class="sr-only">Username / NIP / Email</label>
                        <div class="relative">
                            <i data-lucide="mail" class="size-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            <input
                                x-model="loginId"
                                @input="loginIdError = false"
                                id="login_id-m"
                                type="text"
                                name="login_id"
                                autofocus
                                autocomplete="username"
                                placeholder="Email Address"
                                class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-slate-50 border text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#1e3a5f]/20 focus:border-[#1e3a5f] transition-all"
                                :class="loginIdError ? 'border-red-300 bg-red-50/40' : 'border-slate-200'" />
                        </div>
                        <p x-show="loginIdError" x-cloak class="text-xs text-red-600 pt-1.5 pl-1">
                            Username/email/nip harus diisi!
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password-m" class="sr-only">Password</label>
                        <div class="relative">
                            <i data-lucide="lock" class="size-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            <input
                                x-model="password"
                                @input="passwordError = false"
                                :type="showPassword ? 'text' : 'password'"
                                id="password-m"
                                name="password"
                                autocomplete="current-password"
                                placeholder="Password"
                                class="w-full pl-11 pr-11 py-3.5 rounded-xl bg-slate-50 border text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#1e3a5f]/20 focus:border-[#1e3a5f] transition-all"
                                :class="passwordError ? 'border-red-300 bg-red-50/40' : 'border-slate-200'" />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600 active:text-slate-700 transition-colors"
                                tabindex="-1">
                                <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="size-4"></i>
                            </button>
                        </div>
                        <p x-show="passwordError" x-cloak class="text-xs text-red-600 pt-1.5 pl-1">
                            Password harus diisi!
                        </p>
                    </div>

                    <!-- Save Password + Forgot Password -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none text-slate-600">
                            <input type="checkbox" name="remember" class="size-4 rounded border-slate-300 text-[#1e3a5f] focus:ring-[#1e3a5f]/30" />
                            Simpan sandi
                        </label>
                        <a href="{{ Route::has('password.request') ? route('password.request') : '#' }}" class="font-semibold text-[#ff1443] hover:underline">
                            Lupa sandi?
                        </a>
                    </div>

                    <!-- CTA -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full flex items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-[#ff2450] to-[#c70d33] text-white text-sm font-bold py-3.5 shadow-lg shadow-[#c70d33]/30 transition-all duration-300 ease-out hover:shadow-xl hover:shadow-[#c70d33]/40 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-lg">
                        <i data-lucide="loader-2" class="size-4 animate-spin" x-show="loading" x-cloak></i>
                        <span x-text="loading ? 'Memproses...' : 'Login Akun'"></span>
                    </button>
                </form>

                <!-- Divider -->
                <div class="flex items-center gap-3 mt-6 mb-4">
                    <div class="flex-1 h-px bg-slate-200"></div>
                    <span class="text-[11px] text-slate-400 font-medium">Atau, masuk dengan</span>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>

                <!-- Social login -->
                <div class="flex justify-center gap-4">
                    <!-- Google -->
                    <button type="button" aria-label="Masuk dengan Google"
                        class="size-11 rounded-full bg-white ring-1 ring-slate-200 shadow-sm flex items-center justify-center transition-all hover:shadow-md hover:-translate-y-0.5 active:translate-y-0">
                        <svg viewBox="0 0 24 24" class="size-5" aria-hidden="true">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.56c2.08-1.92 3.28-4.74 3.28-8.1z" />
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.56-2.77c-.99.66-2.25 1.05-3.72 1.05-2.86 0-5.28-1.93-6.15-4.52H2.18v2.84A11 11 0 0 0 12 23z" />
                            <path fill="#FBBC05" d="M5.85 14.1A6.6 6.6 0 0 1 5.5 12c0-.73.13-1.44.35-2.1V7.07H2.18A11 11 0 0 0 1 12c0 1.77.43 3.45 1.18 4.93l3.67-2.83z" />
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15A11 11 0 0 0 12 1 11 11 0 0 0 2.18 7.07l3.67 2.83C6.72 7.31 9.14 5.38 12 5.38z" />
                        </svg>
                    </button>

                    <!-- Facebook -->
                    <button type="button" aria-label="Masuk dengan Facebook"
                        class="size-11 rounded-full bg-[#1877F2] shadow-sm flex items-center justify-center transition-all hover:shadow-md hover:-translate-y-0.5 active:translate-y-0">
                        <svg viewBox="0 0 24 24" class="size-5 fill-white" aria-hidden="true">
                            <path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z" />
                        </svg>
                    </button>

                    <!-- Apple -->
                    <button type="button" aria-label="Masuk dengan Apple"
                        class="size-11 rounded-full bg-slate-900 shadow-sm flex items-center justify-center transition-all hover:shadow-md hover:-translate-y-0.5 active:translate-y-0">
                        <svg viewBox="0 0 24 24" class="size-5 fill-white" aria-hidden="true">
                            <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09zM12 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- ═══ FOOTER ═══ -->
            <div class="mt-8 text-center">
                @if (Route::has('register'))
                <p class="text-xs text-slate-500">
                    Belum punya akun?
                </p>
                <a href="{{ route('register') }}" class="inline-block mt-1.5 text-sm font-extrabold text-[#1e3a5f] hover:text-[#ff1443] transition-colors tracking-tight">
                    Daftar Sekarang
                </a>
                @else
                <p class="text-[11px] text-slate-400">&copy; {{ date('Y') }} Pintar. Seluruh hak cipta dilindungi.</p>
                @endif

                <p class="text-[11px] text-slate-400 mt-4">
                    Butuh bantuan?
                    <a href="mailto:admin@pintar.sch.id" class="text-[#1e3a5f] font-semibold hover:underline">Hubungi administrator</a>
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