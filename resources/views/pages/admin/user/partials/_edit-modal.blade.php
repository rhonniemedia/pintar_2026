<div x-show="editModalOpen"
    class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
    @click.self="closeEditModal()">

    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-border overflow-hidden flex flex-col"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4">

        <div class="px-6 pt-6 pb-5 border-b border-border flex items-center justify-between shrink-0">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0 border border-blue-100">
                    <i data-lucide="user-cog" class="size-5 text-blue-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-foreground">Edit Hak Akses Akun</h2>
                    <p class="text-sm text-secondary" x-text="activeUser?.email"></p>
                </div>
            </div>
            <button type="button" @click="closeEditModal()" class="w-10 h-10 flex items-center justify-center text-secondary hover:text-foreground rounded-lg transition-colors hover:bg-muted cursor-pointer">
                <i data-lucide="x" class="size-5"></i>
            </button>
        </div>

        <form @submit.prevent="submitEdit()" class="flex flex-col flex-1">
            <div class="p-6 space-y-4">

                <div x-show="errors.length > 0" class="mb-4 rounded-xl border border-red-300 bg-red-50 p-4 flex items-start gap-3">
                    <i data-lucide="alert-circle" class="size-5 text-red-600 shrink-0 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-red-700 mb-1">Terdapat kesalahan:</p>
                        <ul class="space-y-0.5">
                            <template x-for="(err, i) in errors" :key="i">
                                <li class="text-xs text-red-600 flex items-center gap-1.5">
                                    <span class="w-1 h-1 rounded-full bg-red-400 shrink-0"></span>
                                    <span x-text="err"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-foreground mb-1.5">Nama Lengkap</label>
                    <input type="text" x-model="form.name" class="w-full bg-white border border-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-foreground mb-1.5">Alamat Email</label>
                    <input type="email" x-model="form.email" class="w-full bg-white border border-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-foreground mb-1.5">Hak Akses (Role)</label>
                    <select x-model="form.role" class="w-full bg-white border border-border rounded-xl px-4 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all cursor-pointer" required>
                        <option value="superadmin">Superadmin</option>
                        <option value="admin">Admin</option>
                        <option value="verifikator">Verifikator</option>
                        <option value="observator">Observator</option>
                        <option value="user">User (Peserta Biasa)</option>
                    </select>
                </div>

                {{-- TOGGLE RESET PASSWORD --}}
                <div class="mt-4 p-4 rounded-xl border transition-all flex flex-row items-center justify-between gap-3 cursor-pointer select-none"
                    :class="form.reset_password ? 'border-red-300 bg-red-50' : 'border-border bg-gray-50/50 hover:border-red-200'"
                    @click="form.reset_password = !form.reset_password">
                    <div>
                        <p class="text-sm font-bold transition-colors" :class="form.reset_password ? 'text-red-800' : 'text-foreground'">Reset Password</p>
                        <p class="text-[11px] transition-colors mt-0.5" :class="form.reset_password ? 'text-red-600' : 'text-secondary'">
                            Centang untuk mereset password ke <span class="font-mono font-bold">Password123*</span>
                        </p>
                    </div>
                    <div class="relative shrink-0 flex items-center justify-center">
                        <input type="checkbox" x-model="form.reset_password" class="sr-only">
                        <div class="w-11 h-6 rounded-full transition-colors duration-300 ease-in-out"
                            :class="form.reset_password ? 'bg-red-500' : 'bg-gray-300'"></div>
                        <div class="absolute left-1 w-4 h-4 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out"
                            :class="form.reset_password ? 'translate-x-5' : 'translate-x-0'"></div>
                    </div>
                </div>

            </div>

            <div class="px-6 py-5 bg-muted/30 border-t border-border flex justify-end gap-2 shrink-0">
                <button type="button" @click="closeEditModal()" class="px-5 py-2.5 rounded-xl border border-border bg-white text-sm font-bold text-secondary hover:bg-muted transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" :disabled="loading" class="px-6 py-2.5 bg-primary hover:bg-primary-dark disabled:opacity-70 text-white shadow-md cursor-pointer text-sm font-bold rounded-xl transition-all flex items-center gap-2">
                    <i data-lucide="save" class="size-4" x-show="!loading"></i>
                    <i data-lucide="loader-2" class="size-4 animate-spin" x-show="loading" style="display: none;"></i>
                    <span x-text="loading ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                </button>
            </div>
        </form>

    </div>
</div>