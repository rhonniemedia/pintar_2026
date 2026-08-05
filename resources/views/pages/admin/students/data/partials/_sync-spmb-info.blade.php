@if(empty($statistik))
<div class="text-center p-6 text-sm font-medium text-secondary bg-slate-50 rounded-xl border border-dashed border-border">
    Belum ada data pendaftar yang terverifikasi di SPMB.
</div>
@else
{{-- Kontainer Utama mengelola Antrean berdasarkan Indeks Angka --}}
<div class="bg-white border border-border rounded-xl overflow-hidden shadow-sm"
    x-data="{ 
            statuses: {},
            currentIndex: 0,
            // Encode langsung daftar jurusan jadi array JSON yang valid
            queue: {{ json_encode(array_keys($statistik)) }},
            
            init() {
                // Set status awal 'idle' untuk setiap urutan (0, 1, 2, ...)
                for (let i = 0; i < this.queue.length; i++) {
                    this.statuses[i] = 'idle';
                }
            },
            
            async processQueue() {
                if (this.currentIndex >= this.queue.length) {
                    $dispatch('all-sync-finished');
                    return;
                }
                
                // Ubah status indeks saat ini menjadi 'syncing'
                this.statuses[this.currentIndex] = 'syncing';
                let jurusanName = this.queue[this.currentIndex];
                
                try {
                    await fetch('{{ route('admin.integration.spmb.sync.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ jurusan: jurusanName })
                    });
                } catch (e) { 
                    console.error('Gagal sinkron:', e); 
                }
                
                // Selesai, ubah status indeks ke 'finished'
                this.statuses[this.currentIndex] = 'finished';
                this.currentIndex++;
                
                // Lanjut ke antrean berikutnya
                this.processQueue();
            }
         }"
    @start-real-sync.window="processQueue()">

    {{-- Header --}}
    <div class="bg-slate-50/80 px-4 py-4 border-b border-border flex justify-between items-center">
        <span class="font-bold text-sm text-foreground uppercase tracking-wider">Tersedia di SPMB</span>
        <span class="bg-rose-100 text-rose-600 text-xs font-bold px-3 py-1 rounded-md">Total: {{ $totalData }} Siswa</span>
    </div>

    {{-- List --}}
    <div class="divide-y divide-border">
        @foreach($statistik as $jurusan => $stat)

        {{-- Mengambil index (0, 1, 2) dari loop Blade --}}
        @php $idx = $loop->index; @endphp

        <div class="p-4 flex items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">

            <div class="flex-1 min-w-0">
                <h4 class="text-[15px] font-semibold text-foreground leading-snug">{{ $jurusan }}</h4>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-xs text-secondary">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="mars" class="size-3.5 text-blue-500"></i> {{ $stat['laki_laki'] }} Laki-laki
                    </span>
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="venus" class="size-3.5 text-pink-500"></i> {{ $stat['perempuan'] }} Perempuan
                    </span>
                </div>
            </div>

            {{-- Bagian Kanan dikendalikan sepenuhnya oleh Indeks --}}
            <div class="flex flex-col items-center justify-center shrink-0 pl-3 w-[70px]">

                {{-- Default: Tampil angka --}}
                <div x-show="statuses[{{ $idx }}] === 'idle'">
                    <span class="text-xl font-black text-foreground leading-none">{{ $stat['total'] }}</span>
                </div>

                {{-- Loading: Spinner --}}
                <div x-show="statuses[{{ $idx }}] === 'syncing'" x-cloak class="mb-1">
                    <i data-lucide="loader-2" class="size-6 text-amber-500 animate-spin"></i>
                </div>

                {{-- Selesai: Centang Hijau --}}
                <div x-show="statuses[{{ $idx }}] === 'finished'" x-cloak class="flex items-center gap-1.5 text-emerald-600">
                    <i data-lucide="check-circle-2" class="size-5"></i>
                    <span class="text-xl font-black leading-none">{{ $stat['total'] }}</span>
                </div>

                <span class="text-[10px] font-bold uppercase tracking-widest mt-1.5 transition-colors"
                    :class="statuses[{{ $idx }}] === 'finished' ? 'text-emerald-600' : 'text-secondary'">Siswa</span>
            </div>

        </div>
        @endforeach
    </div>
</div>

<script>
    setTimeout(() => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }, 50);
</script>
@endif