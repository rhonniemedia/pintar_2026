@php
$isEdit = isset($classGroup);
$actionUrl = $isEdit ? route('admin.students.group.update', $classGroup->id) : route('admin.students.group.store');
$title = $isEdit ? 'Edit Data Rombel' : 'Tambah Data Rombel';
$subtitle = $isEdit ? 'Perbarui informasi rombongan belajar' : 'Tambahkan rombongan belajar baru ke sistem';
@endphp

{{-- Bungkus dalam satu parent div penuh --}}
<div class="flex flex-col w-full max-h-[90vh]">
    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-5 border-b border-border shrink-0 bg-gray-50/50 rounded-t-2xl">
        <div>
            <h3 class="font-bold text-foreground text-lg">{{ $title }}</h3>
            <p class="text-xs text-secondary mt-0.5">{{ $subtitle }}</p>
        </div>
        <button @click="formModalOpen = false" type="button" class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors">
            <i data-lucide="x" class="size-4 text-secondary"></i>
        </button>
    </div>

    {{-- Form Body (Hapus h-full, gunakan flex-1) --}}
    <form hx-{{ $isEdit ? 'put' : 'post' }}="{{ $actionUrl }}"
        class="flex flex-col flex-1 overflow-hidden">
        @csrf

        <div class="flex-1 overflow-y-auto p-6 space-y-4">
            <div>
                <label class="block text-sm font-bold text-foreground mb-2">Rombongan Belajar</label>
                <input type="text" name="name" value="{{ $classGroup->name ?? '' }}" placeholder="Contoh: X DPIB 1"
                    class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
            </div>

            <div>
                <label class="block text-sm font-bold text-foreground mb-2">Kelas</label>
                <select name="grade_level" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                    <option value="10" @selected(($classGroup->grade_level ?? '') == '10')>Kelas X</option>
                    <option value="11" @selected(($classGroup->grade_level ?? '') == '11')>Kelas XI</option>
                    <option value="12" @selected(($classGroup->grade_level ?? '') == '12')>Kelas XII</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-foreground mb-2">Jurusan</label>
                <select name="concentration_id" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                    <option value="">Pilih Jurusan</option>
                    @foreach($concentrationOptions as $id => $name)
                    <option value="{{ $id }}" @selected(($classGroup->concentration_id ?? '') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-foreground mb-2">Wali Kelas</label>
                <select name="homeroom_teacher_id" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                    <option value="">Pilih Wali Kelas (Opsional)</option>
                    @foreach($teacherOptions as $id => $name)
                    <option value="{{ $id }}" @selected(($classGroup->homeroom_teacher_id ?? '') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-border bg-gray-50/50 flex items-center justify-end gap-3 shrink-0 rounded-b-2xl">
            <button type="button" @click="formModalOpen = false"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border bg-white text-secondary hover:bg-muted font-medium text-sm transition-colors cursor-pointer">
                <i data-lucide="x" class="size-4"></i> Batal
            </button>

            <button type="submit"
                class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 shadow-md text-white font-bold text-sm rounded-xl transition-all cursor-pointer">
                <i data-lucide="save" class="size-4"></i> Simpan
            </button>
        </div>
    </form>
</div>

<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>