<!-- ══ SIDEBAR ══ -->
<aside
    class="flex flex-col w-[280px] shrink-0 h-screen fixed inset-y-0 left-0 z-50 bg-white border-r border-border overflow-hidden transition-transform duration-300 ease-in-out"
    :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0 lg:shadow-none'">

    <!-- Logo -->
    <div class="flex items-center justify-between border-b border-border h-[90px] px-5 gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 shrink-0 rounded-xl flex items-center justify-center bg-gradient-to-br from-red-500 to-purple-700 shadow-sm">
                <i data-lucide="graduation-cap" class="size-5 text-white"></i>
            </div>
            <div>
                <h1 class="font-bold text-base text-foreground leading-tight">PINTAR</h1>
                <p class="text-xs text-secondary">SMK Negeri 1 Rejang Lebong</p>
            </div>
        </div>

        <!-- Tombol Close (X) ditambahkan @click -->
        <button @click="sidebarOpen = false" class="lg:hidden size-11 flex shrink-0 bg-white rounded-xl p-[10px] items-center justify-center ring-1 ring-border hover:ring-primary transition-all duration-300 cursor-pointer">
            <i data-lucide="x" class="size-6 text-secondary"></i>
        </button>
    </div>

    <!-- Nav -->
    <div class="flex flex-col p-5 pb-28 gap-6 overflow-y-auto flex-1 scrollbar-hide">

        <!-- Dashboard -->
        <div class="flex flex-col gap-4">
            <h3 class="font-medium text-sm text-secondary">Dashboard</h3>

            <div class="flex flex-col gap-1">
                @php $isHome = request()->is('admin/home'); @endphp
                <a href="{{ url('/admin/home') }}" class="group cursor-pointer {{ $isHome ? 'active' : '' }}">
                    <div class="flex items-center rounded-xl p-3 gap-3 transition-all duration-300 {{ $isHome ? 'bg-muted' : 'hover:bg-muted' }}">
                        <i data-lucide="square-dashed-mouse-pointer" class="size-5 transition-all duration-300 {{ $isHome ? 'text-foreground' : 'text-secondary group-hover:text-foreground' }}"></i>
                        <span class="text-sm transition-all duration-300 {{ $isHome ? 'font-semibold text-foreground' : 'font-medium text-secondary group-hover:text-foreground' }}">
                            Beranda
                        </span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Data Master -->
        <div class="flex flex-col gap-4">
            <h3 class="font-medium text-sm text-secondary">Data Master</h3>

            <div class="flex flex-col gap-1">

                <a href="#"
                    class="group {{ request()->routeIs('admin.master-data.index') ? 'active' : '' }}">
                    <div
                        class="flex items-center rounded-xl p-3 gap-3 transition-all duration-300 {{ request()->routeIs('admin.master-data.index') ? 'bg-muted' : 'hover:bg-muted' }}">
                        <i data-lucide="school-2"
                            class="size-5 {{ request()->routeIs('admin.master-data.index') ? 'text-foreground' : 'text-secondary group-hover:text-foreground' }}"></i>

                        <span
                            class="text-sm {{ request()->routeIs('admin.master-data.index') ? 'font-semibold text-foreground' : 'font-medium text-secondary group-hover:text-foreground' }}">
                            Data Sekolah
                        </span>
                    </div>
                </a>

                <a href="{{ route('admin.master-data.academic') }}"
                    class="group {{ request()->routeIs('admin.master-data.academic') ? 'active' : '' }}">
                    <div
                        class="flex items-center rounded-xl p-3 gap-3 transition-all duration-300 {{ request()->routeIs('admin.master-data.academic') ? 'bg-muted' : 'hover:bg-muted' }}">
                        <i data-lucide="book-open-text"
                            class="size-5 {{ request()->routeIs('admin.master-data.academic') ? 'text-foreground' : 'text-secondary group-hover:text-foreground' }}"></i>

                        <span
                            class="text-sm {{ request()->routeIs('admin.master-data.academic') ? 'font-semibold text-foreground' : 'font-medium text-secondary group-hover:text-foreground' }}">
                            Akademik
                        </span>
                    </div>
                </a>

                <!-- Menu Baru: Data SPMB -->
                <a href="{{ route('admin.integration.spmb.sync.preview') }}"
                    class="group {{ request()->routeIs('admin.integration.spmb.sync.preview') ? 'active' : '' }}">
                    <div
                        class="flex items-center rounded-xl p-3 gap-3 transition-all duration-300 {{ request()->routeIs('admin.integration.spmb.sync.preview') ? 'bg-muted' : 'hover:bg-muted' }}">
                        <i data-lucide="database"
                            class="size-5 {{ request()->routeIs('admin.integration.spmb.sync.preview') ? 'text-foreground' : 'text-secondary group-hover:text-foreground' }}"></i>

                        <span
                            class="text-sm {{ request()->routeIs('admin.integration.spmb.sync.preview') ? 'font-semibold text-foreground' : 'font-medium text-secondary group-hover:text-foreground' }}">
                            Data SPMB
                        </span>
                    </div>
                </a>

            </div>
        </div>

        <!-- Manajemen Peserta -->
        <div class="flex flex-col gap-4">
            <h3 class="font-medium text-sm text-secondary">Peserta Didik</h3>

            <div class="flex flex-col gap-1">

                {{-- 1. Menu Data Peserta Didik --}}
                @php $isData = request()->routeIs('admin.students.data.*'); @endphp
                <a href="{{ route('admin.students.data.index') }}" class="group cursor-pointer {{ $isData ? 'active' : '' }}">
                    <div class="flex items-center rounded-xl p-3 gap-3 transition-all duration-300 {{ $isData ? 'bg-muted' : 'hover:bg-muted' }}">
                        <i data-lucide="users" class="size-5 transition-all duration-300 {{ $isData ? 'text-foreground' : 'text-secondary group-hover:text-foreground' }}"></i>
                        <span class="text-sm transition-all duration-300 {{ $isData ? 'font-semibold text-foreground' : 'font-medium text-secondary group-hover:text-foreground' }}">
                            Data Peserta Didik
                        </span>
                    </div>
                </a>

                {{-- 2. Menu Rombongan Belajar --}}
                @php $isRombel = request()->routeIs('admin.students.group.*'); @endphp
                <a href="{{ route('admin.students.group.index') }}" class="group cursor-pointer {{ $isRombel ? 'active' : '' }}">
                    <div class="flex items-center rounded-xl p-3 gap-3 transition-all duration-300 {{ $isRombel ? 'bg-muted' : 'hover:bg-muted' }}">
                        <i data-lucide="component" class="size-5 transition-all duration-300 {{ $isRombel ? 'text-foreground' : 'text-secondary group-hover:text-foreground' }}"></i>
                        <span class="text-sm transition-all duration-300 {{ $isRombel ? 'font-semibold text-foreground' : 'font-medium text-secondary group-hover:text-foreground' }}">
                            Rombongan Belajar
                        </span>
                    </div>
                </a>

                {{-- 3. Menu Mutasi Peserta Didik --}}
                @php $isMutasi = request()->routeIs('admin.students.transfers.*'); @endphp
                <a href="{{ route('admin.students.transfer.in.index') }}" class="group cursor-pointer {{ $isMutasi ? 'active' : '' }}">
                    <div class="flex items-center justify-between rounded-xl p-3 transition-all duration-300 {{ $isMutasi ? 'bg-muted' : 'hover:bg-muted' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="shuffle" class="size-5 transition-all duration-300 {{ $isMutasi ? 'text-foreground' : 'text-secondary group-hover:text-foreground' }}"></i>
                            <span class="text-sm transition-all duration-300 {{ $isMutasi ? 'font-semibold text-foreground' : 'font-medium text-secondary group-hover:text-foreground' }}">
                                Mutasi Peserta Didik
                            </span>
                        </div>
                    </div>
                </a>

                {{-- 4. Menu Data Alumni (Kelulusan) --}}
                @php $isAlumni = request()->routeIs('admin.students.alumni.*'); @endphp
                <a href="{{ route('admin.students.graduates.index') }}" class="group cursor-pointer {{ $isAlumni ? 'active' : '' }}">
                    <div class="flex items-center rounded-xl p-3 gap-3 transition-all duration-300 {{ $isAlumni ? 'bg-muted' : 'hover:bg-muted' }}">
                        <i data-lucide="graduation-cap" class="size-5 transition-all duration-300 {{ $isAlumni ? 'text-foreground' : 'text-secondary group-hover:text-foreground' }}"></i>
                        <span class="text-sm transition-all duration-300 {{ $isAlumni ? 'font-semibold text-foreground' : 'font-medium text-secondary group-hover:text-foreground' }}">
                            Data Kelulusan
                        </span>
                    </div>
                </a>

                {{-- 5. Menu Riwayat Peserta Didik --}}
                @php $isRiwayat = request()->routeIs('admin.students.history.*'); @endphp
                <a href="{{ route('admin.students.history.index') }}" class="group cursor-pointer {{ $isRiwayat ? 'active' : '' }}">
                    <div class="flex items-center rounded-xl p-3 gap-3 transition-all duration-300 {{ $isRiwayat ? 'bg-muted' : 'hover:bg-muted' }}">
                        <i data-lucide="history" class="size-5 transition-all duration-300 {{ $isRiwayat ? 'text-foreground' : 'text-secondary group-hover:text-foreground' }}"></i>
                        <span class="text-sm transition-all duration-300 {{ $isRiwayat ? 'font-semibold text-foreground' : 'font-medium text-secondary group-hover:text-foreground' }}">
                            Riwayat Peserta Didik
                        </span>
                    </div>
                </a>

            </div>
        </div>

        <!-- Pengguna -->
        <div class="flex flex-col gap-4">
            <h3 class="font-medium text-sm text-secondary">Pengguna</h3>

            <div class="flex flex-col gap-1">

                <a href="#" class="group cursor-pointer">
                    <div class="flex items-center rounded-xl p-3 gap-3 hover:bg-muted transition-all duration-300">
                        <i data-lucide="users-round" class="size-5 text-secondary group-hover:text-foreground transition-all duration-300"></i>
                        <span class="font-medium text-sm text-secondary group-hover:text-foreground transition-all duration-300">
                            Daftar Pengguna
                        </span>
                    </div>
                </a>

                <a href="#" class="group cursor-pointer">
                    <div class="flex items-center rounded-xl p-3 gap-3 hover:bg-muted transition-all duration-300">
                        <i data-lucide="circle-user" class="size-5 text-secondary group-hover:text-foreground transition-all duration-300"></i>
                        <span class="font-medium text-sm text-secondary group-hover:text-foreground transition-all duration-300">
                            Akun Pengguna
                        </span>
                    </div>
                </a>

            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="absolute bottom-0 left-0 w-[280px]">
        <div class="flex items-center justify-between border-t bg-white border-border p-5 gap-3">
            <div class="min-w-0">
                <p class="font-semibold text-foreground text-sm">Admin</p>
                <p class="text-xs text-secondary mt-0.5">Pintar 2026</p>
            </div>

            <div class="size-11 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="circle-power" class="size-6 text-primary"></i>
            </div>
        </div>
    </div>

</aside>