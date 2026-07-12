@extends('layouts.main.admin')

@section('title', 'Beranda')
@section('page_title', 'Beranda')
@section('page_subtitle', 'Beranda Admin')

@section('content')

<div class="flex-1 overflow-y-auto p-5 md:p-8 bg-white">

    <div class="max-w-4xl">
        <div class="rounded-3xl border border-border bg-white p-8">

            <div class="flex items-center gap-5">

                <div class="size-16 rounded-2xl bg-primary/10 flex items-center justify-center">
                    <i data-lucide="shield-check" class="size-8 text-primary"></i>
                </div>

                <div>
                    <h1 class="text-3xl font-bold text-foreground">
                        Selamat Datang, Admin
                    </h1>

                    <p class="mt-2 text-secondary leading-relaxed">
                        Selamat datang di <span class="font-semibold text-foreground">Pintar</span>.
                        Gunakan menu di sebelah kiri untuk mengelola data peserta,
                        melakukan observasi, melihat hasil seleksi, mengelola daftar ulang,
                        serta mengelola akun pengguna.
                    </p>
                </div>

            </div>

        </div>
    </div>

</div>

@endsection