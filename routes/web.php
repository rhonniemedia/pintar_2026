<?php

use App\Http\Controllers\Admin\Master\MasterDataController;
use App\Http\Controllers\Admin\Students\ClassGroupAttendanceController;
use App\Http\Controllers\Admin\Students\ClassGroupController;
use App\Http\Controllers\Admin\Students\ClassGroupPromotionController;
use App\Http\Controllers\Admin\Students\StudentController;
use App\Http\Controllers\Admin\Students\StudentGraduationController;
use App\Http\Controllers\Admin\Students\StudentHistoryController;
use App\Http\Controllers\Admin\Students\StudentMutationInController;
use App\Http\Controllers\Admin\Students\StudentMutationOutController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Integration\SpmbSyncController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::prefix('admin')->name('admin.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Beranda
    |--------------------------------------------------------------------------
    */

    Route::get('/home', function () {
        return view('pages.admin.home.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Integrasi Sistem
    |--------------------------------------------------------------------------
    */
    Route::prefix('integration')->name('integration.')->group(function () {
        // Rute untuk menampilkan halaman pratinjau
        Route::get('/spmb/sync/preview', [SpmbSyncController::class, 'preview'])->name('spmb.sync.preview');

        // Rute untuk mengeksekusi penyimpanan
        Route::post('/spmb/sync/store', [SpmbSyncController::class, 'store'])->name('spmb.sync.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Peserta Didik
    |--------------------------------------------------------------------------
    */

    Route::prefix('students')->name('students.')->group(function () {

        // Data Peserta Didik
        Route::get('/data', [StudentController::class, 'index'])->name('data.index');
        Route::get('/floating', [StudentController::class, 'floating'])->name('floating.index');
        Route::delete('/data/{id}', [StudentController::class, 'destroy'])->name('data.destroy');

        // Detail
        Route::get('/{id}/detail/personal', [StudentController::class, 'show'])->name('detail.personal');
        Route::get('/{id}/detail/guardian', [StudentController::class, 'showGuardian'])->name('detail.guardian');

        // Edit
        Route::get('/{id}/edit/personal', [StudentController::class, 'edit'])->name('edit.personal');
        Route::put('/{id}/edit/personal', [StudentController::class, 'update'])->name('edit.personal.update');

        // --- RUTE BARU UNTUK FOTO ---
        Route::get('/{id}/edit/photo', [StudentController::class, 'editPhoto'])->name('edit.photo');
        Route::put('/{id}/edit/photo', [StudentController::class, 'updatePhoto'])->name('edit.photo.update');

        Route::get('/{id}/edit/guardian', fn($id) => null)->name('edit.guardian');

        // Kelompok / Rombel
        Route::get('/group', [ClassGroupController::class, 'index'])->name('group.index');
        Route::get('/group/create', [ClassGroupController::class, 'create'])->name('group.create');
        Route::get('/group/{id}/edit', [ClassGroupController::class, 'edit'])->name('group.edit');
        Route::post('/group', [ClassGroupController::class, 'store'])->name('group.store');
        Route::put('/group/{id}', [ClassGroupController::class, 'update'])->name('group.update');
        Route::get('/group/{id}/show', [ClassGroupController::class, 'show'])->name('group.show');
        Route::get('/group/{classGroup}/add-student', [ClassGroupController::class, 'addStudentForm'])->name('group.add-student.form');
        Route::post('/group/{classGroup}/add-student', [ClassGroupController::class, 'storeStudent'])->name('group.add-student.store');
        Route::delete('/group/{id}', [ClassGroupController::class, 'destroy'])->name('group.destroy');

        // --- RUTE CETAK DAFTAR HADIR ---
        Route::controller(ClassGroupAttendanceController::class)->group(function () {
            Route::get('/group/attendance/modal', 'showModal')->name('attendance.modal');
            Route::get('/group/attendance/classes', 'getFilteredClasses')->name('attendance.classes');
            Route::get('/group/attendance/print', 'printPdf')->name('attendance.print');
        });

        // Route untuk Pindah Kelas
        Route::get('/group/{classGroup}/student/{student}/move', [ClassGroupController::class, 'moveClassForm'])
            ->name('group.student.move-form');
        Route::post('/group/{classGroup}/student/{student}/move', [ClassGroupController::class, 'moveClass'])
            ->name('group.student.move');

        // Route untuk Kenaikan Kelas
        Route::get('/group/{classGroup}/promotion', [ClassGroupPromotionController::class, 'promotionForm'])
            ->name('group.promotion.form');
        Route::post('/group/{classGroup}/promotion', [ClassGroupPromotionController::class, 'promote'])
            ->name('group.promote');

        Route::get('/group/{classGroup}/promotion/cancel', [ClassGroupPromotionController::class, 'promotionCancelForm'])
            ->name('group.promotion.cancel-form');
        Route::post('/group/{classGroup}/promotion/cancel', [ClassGroupPromotionController::class, 'cancelPromotion'])
            ->name('group.promotion.cancel');

        // Route untuk Kelulusan
        Route::get('/group/{classGroup}/graduation', [ClassGroupPromotionController::class, 'graduationForm'])
            ->name('group.graduation.form');
        Route::post('/group/{classGroup}/graduation', [ClassGroupPromotionController::class, 'graduate'])
            ->name('group.graduate');

        Route::get('/group/{classGroup}/graduation/cancel', [ClassGroupPromotionController::class, 'graduationCancelForm'])
            ->name('group.graduation.cancel-form');
        Route::post('/group/{classGroup}/graduation/cancel', [ClassGroupPromotionController::class, 'cancelGraduation'])
            ->name('group.graduation.cancel');

        // Route Mutasi
        Route::prefix('transfer')->group(function () {

            // Mutasi Masuk
            Route::name('transfer.in.')->prefix('in')->group(function () {
                Route::get('/', [StudentMutationInController::class, 'index'])->name('index');

                // Route Validasi Per Step (TAMBAHKAN BARIS INI)
                Route::post('/validate-step', [StudentMutationInController::class, 'validateStep'])->name('validate-step');

                // Step 1: form awal (belum ada siswa) + submit yang membuat siswa baru
                Route::get('/create', [StudentMutationInController::class, 'create'])->name('create');
                Route::post('/', [StudentMutationInController::class, 'store'])->name('store');
            });

            // Mutasi Keluar
            Route::name('transfer.out.')->prefix('out')->group(function () {
                Route::get('/', [StudentMutationOutController::class, 'index'])->name('index');
                Route::get('/create', [StudentMutationOutController::class, 'create'])->name('create');
                Route::post('/', [StudentMutationOutController::class, 'store'])->name('store');
            });
        });

        // Kelulusan
        Route::name('graduates.')->prefix('graduates')->group(function () {
            Route::get('/', [StudentGraduationController::class, 'index'])->name('index');
            Route::get('/{id}', [StudentGraduationController::class, 'show'])->name('show');
        });

        // Lainnya
        Route::get('/mutasi', fn() => 'Halaman Mutasi Peserta Didik')->name('mutasi.index');
        Route::get('/history', [StudentHistoryController::class, 'index'])->name('history.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Data Master
    |--------------------------------------------------------------------------
    */

    Route::prefix('master-data')->name('master-data.')->group(function () {

        Route::get('/', [MasterDataController::class, 'index'])
            ->name('academic');

        // Route khusus Modal & Action Tahun Ajaran
        Route::get('/academic-year/create', [MasterDataController::class, 'createAcademicYear'])->name('academic-year.create');
        Route::post('/academic-year', [MasterDataController::class, 'storeAcademicYear'])->name('academic-year.store');
        Route::get('/academic-year/{id}/edit', [MasterDataController::class, 'editAcademicYear'])->name('academic-year.edit');
        Route::put('/academic-year/{id}', [MasterDataController::class, 'updateAcademicYear'])->name('academic-year.update');
        Route::delete('/academic-year/{id}', [MasterDataController::class, 'destroyAcademicYear'])->name('academic-year.destroy');

        // Route khusus Modal & Action Semester
        Route::get('/semester/create', [MasterDataController::class, 'createSemester'])->name('semester.create');
        Route::post('/semester', [MasterDataController::class, 'storeSemester'])->name('semester.store');
        Route::get('/semester/{id}/edit', [MasterDataController::class, 'editSemester'])->name('semester.edit');
        Route::put('/semester/{id}', [MasterDataController::class, 'updateSemester'])->name('semester.update');
        Route::delete('/semester/{id}', [MasterDataController::class, 'destroySemester'])->name('semester.destroy');

        // Route khusus Modal & Action Jurusan
        // --- RUTE CONCENTRATION (JURUSAN) ---
        Route::get('/concentration/create', [MasterDataController::class, 'createConcentration'])->name('concentration.create');
        Route::post('/concentration', [MasterDataController::class, 'storeConcentration'])->name('concentration.store');
        Route::get('/concentration/{id}/edit', [MasterDataController::class, 'editConcentration'])->name('concentration.edit');
        Route::put('/concentration/{id}', [MasterDataController::class, 'updateConcentration'])->name('concentration.update');
        Route::delete('/concentration/{id}', [MasterDataController::class, 'destroyConcentration'])->name('concentration.destroy');

        // Data Sekolah
        Route::get('/school', [MasterDataController::class, 'school'])
            ->name('school.update');

        Route::post('/school', [MasterDataController::class, 'updateSchool'])
            ->name('school.update');

        // Akademik
        Route::post('/academics', [MasterDataController::class, 'storeAcademic'])
            ->name('academic.store');

        Route::put('/academics/{id}', [MasterDataController::class, 'updateAcademic'])
            ->name('academic.update');

        Route::delete('/academics/{id}', [MasterDataController::class, 'destroyAcademic'])
            ->name('academic.destroy');
    });
});
