<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem Akademik</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-base-200 flex items-center justify-center min-h-screen">

    <div class="card w-full max-w-md bg-base-100 shadow-xl border border-gray-200" x-data="{ isLoading: false }">
        <div class="card-body">
            <h2 class="card-title text-2xl font-bold mb-4 text-center justify-center">Masuk ke Portal</h2>

            @if($errors->any())
            <div class="alert alert-error text-sm rounded-lg mb-4">
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" @submit="isLoading = true">
                @csrf

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-semibold">Username / Email / NIP</span>
                    </label>
                    <input type="text" name="login_id" class="input input-bordered w-full" required autofocus />
                </div>

                <div class="form-control mb-6">
                    <label class="label">
                        <span class="label-text font-semibold">Password</span>
                    </label>
                    <input type="password" name="password" class="input input-bordered w-full" required />
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="cursor-pointer label p-0">
                        <input type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary mr-2" />
                        <span class="label-text">Ingat Saya</span>
                    </label>
                </div>

                <div class="form-control mt-2 flex flex-row gap-2">
                    <button type="reset" class="btn btn-outline btn-error w-1/3">Reset</button>

                    <button type="submit" class="btn btn-primary w-2/3" x-bind:disabled="isLoading">
                        <span x-show="isLoading" class="loading loading-spinner loading-sm"></span>
                        <span x-show="!isLoading">Masuk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>