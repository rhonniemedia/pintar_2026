<div id="user-list-container"
    hx-trigger="refreshTable from:body"
    hx-get="{{ route('admin.users.index', request()->query()) }}"
    hx-target="#user-list-container"
    hx-swap="outerHTML">

    {{-- Wrapper dengan padding bottom kecil agar scrollbar tidak menutupi konten bawah --}}
    <div class="overflow-x-auto w-full pb-2">
        {{-- Hapus table-fixed dan tambahkan min-w-[800px] agar bisa di-scroll horizontal di HP --}}
        <table class="w-full text-left border-collapse min-w-[800px]">
            <thead>
                <tr class="border-b border-border bg-gray-50/30">
                    {{-- Pindahkan pengaturan lebar kolom langsung ke dalam class TH --}}
                    <th class="w-[30%] px-4 py-3 text-sm font-bold text-secondary">
                        Pengguna
                        <div class="text-[11px] font-normal normal-case">Nama | Username</div>
                    </th>
                    <th class="w-[30%] px-4 py-3 text-sm font-bold text-secondary">
                        Kontak
                        <div class="text-[11px] font-normal normal-case">Telepon | Email</div>
                    </th>
                    <th class="w-[25%] px-4 py-3 text-sm font-bold text-secondary">
                        Status
                        <div class="text-[11px] font-normal normal-case">Status | Role</div>
                    </th>
                    <th class="w-[15%] px-4 py-3 text-sm font-bold text-secondary">
                        Aksi
                        <div class="text-[11px] font-normal normal-case">Role | Sandi</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $user)
                @php
                $staff = $user->staff;
                $displayName = $staff->name ?? $user->username;
                $email = optional(optional($staff)->vault)->email;
                $phone = optional(optional($staff)->vault)->phone_number;

                // Memanggil relasi roles
                $roles = $user->roles ?? collect();
                @endphp
                <tr id="row-user-{{ $user->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">

                    {{-- Kolom Pengguna --}}
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3 group">
                            <x-ui.avatar :name="$displayName" :gender="optional($staff)->gender" :index="$loop->index" />

                            <div class="min-w-0">
                                <div class="font-semibold text-foreground text-sm group-hover:text-primary transition-colors truncate">
                                    {{ $displayName }}
                                </div>
                                <div class="text-xs text-secondary mt-0.5 truncate">
                                    {{ $user->username }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Kolom Kontak (Telepon & Email) --}}
                    <td class="px-4 py-4 text-sm">
                        <div class="text-foreground font-medium truncate mb-1">
                            {{ $phone ?? '-' }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-secondary truncate" title="{{ $email }}">
                            <i data-lucide="mail" class="size-3.5 shrink-0"></i>
                            <span class="truncate">{{ $email ?? '-' }}</span>
                        </div>
                    </td>

                    {{-- Kolom Status & Role --}}
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2">

                            {{-- Status (Kiri) --}}
                            @if ($user->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold rounded-full bg-emerald-100 text-emerald-700 shrink-0">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-500 shrink-0">
                                <span class="size-1.5 rounded-full bg-gray-400"></span>
                                Nonaktif
                            </span>
                            @endif

                            {{-- Role (Kanan) --}}
                            <div class="flex flex-wrap items-center gap-1">
                                @forelse ($roles as $role)
                                <span class="inline-block px-2 py-1 text-[11px] font-semibold rounded-full bg-primary/10 text-primary capitalize">
                                    {{ $role->name }}
                                </span>
                                @empty
                                <span class="text-[11px] text-secondary">Belum ada role</span>
                                @endforelse
                            </div>

                        </div>
                    </td>

                    {{-- Kolom Aksi --}}
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2">
                            <button type="button" title="Ubah Hak Akses"
                                hx-get="{{ route('admin.users.edit-role', $user->id) }}"
                                hx-target="#modal-form-container"
                                hx-swap="innerHTML"
                                class="flex items-center justify-center size-8 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer">
                                <i data-lucide="shield-check" class="size-4 pointer-events-none"></i>
                            </button>
                            <button type="button" title="Reset Kata Sandi"
                                hx-get="{{ route('admin.users.edit-password', $user->id) }}"
                                hx-target="#modal-form-container"
                                hx-swap="innerHTML"
                                class="flex items-center justify-center size-8 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer">
                                <i data-lucide="key-round" class="size-4 pointer-events-none"></i>
                            </button>
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-16 text-center text-secondary">
                        <div class="flex flex-col items-center gap-3">
                            <i data-lucide="users-round" class="size-10 text-border"></i>
                            <p class="font-medium text-sm">Belum ada pengguna yang terdaftar</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Memanggil komponen pagination --}}
    <x-ui.pagination :paginator="$data" hxTarget="#user-list-container" />

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>