<!-- ── Tabel Siswa Terbaru ── -->
<div class="flex flex-col rounded-2xl border border-border p-6 bg-white gap-5">
    {{-- Header: judul + aksi --}}
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-lg text-foreground">Siswa Terbaru</h3>
            <p class="text-sm text-secondary">Perubahan data siswa terakhir</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <form action="#" method="GET"
                class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto"
                onsubmit="event.preventDefault();">
                <div class="relative w-full sm:w-auto">
                    <i data-lucide="search"
                        class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-secondary pointer-events-none"></i>
                    <input type="text" name="search" placeholder="Cari siswa / NISN..."
                        class="pl-9 pr-9 py-2 h-10 rounded-xl border border-border bg-white text-sm focus:ring-1 focus:ring-primary outline-none w-full sm:w-[220px] transition-all" />
                </div>

                <a href="#"
                    class="flex items-center justify-center gap-1.5 px-5 h-10 bg-primary text-white rounded-xl sm:rounded-full font-bold text-xs hover:bg-primary-hover transition-all cursor-pointer w-full sm:w-auto">
                    <i data-lucide="users" class="size-3.5"></i>
                    Kelola Siswa
                </a>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div id="latest-students-container">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-border">
                        <th class="w-[35%] px-5 py-3 text-sm font-bold text-secondary tracking-wider">
                            Siswa
                            <div class="text-[11px] font-normal normal-case">Nama | NISN</div>
                        </th>
                        <th class="w-[25%] px-5 py-3 text-sm font-bold text-secondary tracking-wider">
                            TTL
                            <div class="text-[11px] font-normal normal-case">Tempat, Tanggal Lahir</div>
                        </th>
                        <th class="w-[25%] px-5 py-3 text-sm font-bold text-secondary tracking-wider">
                            Rombel
                            <div class="text-[11px] font-normal normal-case">Kelas | Jurusan</div>
                        </th>
                        <th class="w-[15%] px-5 py-3 text-sm font-bold text-secondary tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data->students as $s)
                    <tr class="border-b border-border hover:bg-muted/50 transition-colors">
                        {{-- Siswa --}}
                        <td class="px-5 py-4 min-w-[260px]">
                            <a href="#" class="flex items-center gap-3 group transition-all">
                                <div class="relative size-10 shrink-0 rounded-full overflow-hidden">
                                    <x-ui.avatar :name="$s->name" :index="$loop->index" class="size-10 absolute inset-0" />
                                    <div class="absolute inset-0 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity bg-black/20">
                                        <i data-lucide="user" class="size-5 stroke-[2] pointer-events-none"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold text-foreground text-sm uppercase group-hover:text-primary transition-colors whitespace-nowrap">
                                        {{ $s->name }}
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-secondary mt-0.5 whitespace-nowrap">
                                        {{ $s->nisn }}
                                    </div>
                                </div>
                            </a>
                        </td>

                        {{-- TTL --}}
                        <td class="px-5 py-4 min-w-[180px]">
                            <div class="text-sm font-semibold text-foreground whitespace-nowrap">
                                {{ $s->birth_place }}
                            </div>
                            <div class="text-xs text-secondary mt-0.5 whitespace-nowrap">
                                {{ $s->birth_date }}
                            </div>
                        </td>

                        {{-- Rombel --}}
                        <td class="px-5 py-4 min-w-[150px]">
                            <div class="text-sm font-semibold text-foreground whitespace-nowrap">
                                {{ $s->class_group_name }}
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-secondary mt-0.5 whitespace-nowrap">
                                <span class="inline-block size-1.5 rounded-full shrink-0"
                                    @style(['background-color: ' . $s->concentration_color])></span>
                                    {{ $s->concentration_alias }}
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4 min-w-[110px]">
                                <div class="size-8 rounded-full {{ $s->icon_class }} flex items-center justify-center" title="{{ $s->status_label }}">
                                    <i data-lucide="{{ $s->icon_name }}" class="size-4"></i>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-secondary">
                                <p class="font-medium">Tidak ada data siswa terbaru</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>