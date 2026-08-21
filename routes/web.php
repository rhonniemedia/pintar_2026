<?php

use App\Http\Controllers\Admin\Home\HomeController;
use App\Http\Controllers\Admin\Master\MasterDataController;
use App\Http\Controllers\Admin\Settings\SchoolController;
use App\Http\Controllers\Admin\Students\ClassGroupAttendanceController;
use App\Http\Controllers\Admin\Students\ClassGroupController;
use App\Http\Controllers\Admin\Students\ClassGroupPromotionController;
use App\Http\Controllers\Admin\Students\StudentController;
use App\Http\Controllers\Admin\Students\StudentGraduationController;
use App\Http\Controllers\Admin\Students\StudentHistoryController;
use App\Http\Controllers\Admin\Students\StudentLetterController;
use App\Http\Controllers\Admin\Students\StudentMutationInController;
use App\Http\Controllers\Admin\Students\StudentMutationOutController;
use App\Http\Controllers\Admin\Students\StudentReportController;
use App\Http\Controllers\Admin\User\ProfileController;
use App\Http\Controllers\Admin\User\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Integration\SpmbSyncController;
use App\Http\Middleware\AuthorizeAppAccess;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.home');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Menerapkan middleware auth dan AuthorizeAppAccess ke seluruh rute admin
Route::prefix('admin')->name('admin.')->middleware(['auth', AuthorizeAppAccess::class])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Beranda
    |--------------------------------------------------------------------------
    */

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    /*
    |--------------------------------------------------------------------------
    | Integrasi Sistem
    |--------------------------------------------------------------------------
    */
    Route::prefix('integration')->name('integration.')->group(function () {
        Route::post('/spmb/sync/store', [SpmbSyncController::class, 'store'])->name('spmb.sync.store');
        Route::get('/spmb/sync/info', [SpmbSyncController::class, 'checkInfo'])->name('spmb.sync.info');
    });

    /*
    |--------------------------------------------------------------------------
    | Peserta Didik
    |--------------------------------------------------------------------------
    */

    Route::prefix('students')->name('students.')->group(function () {

        // Data Peserta Didik
        Route::get('/data', [StudentController::class, 'index'])->name('data.index');

        // TAMBAHKAN 2 BARIS INI:
        Route::get('/data/create', [StudentController::class, 'create'])->name('data.create');
        Route::post('/data', [StudentController::class, 'store'])->name('data.store');

        Route::get('/floating', [StudentController::class, 'floating'])->name('floating.index');
        Route::get('/floating/export', [StudentController::class, 'exportFloating'])->name('floating.export');
        Route::get('/data/export', [StudentController::class, 'export'])->name('data.export');

        Route::get('/data/generate-nis-modal', [StudentController::class, 'generateNisModal'])->name('data.generate-nis-modal');
        Route::post('/data/generate-nis', [StudentController::class, 'generateNis'])->name('data.generate-nis');

        Route::delete('/data/{id}', [StudentController::class, 'destroy'])->name('data.destroy');

        // Detail
        Route::get('/{id}/detail/personal', [StudentController::class, 'show'])->name('detail.personal');
        Route::get('/{id}/detail/guardian', [StudentController::class, 'showGuardian'])->name('detail.guardian');

        // Edit
        Route::get('/{id}/edit/personal', [StudentController::class, 'edit'])->name('edit.personal');
        Route::put('/{id}/edit/personal', [StudentController::class, 'update'])->name('edit.personal.update');

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

        // Rute Cetak Daftar Hadir
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
                Route::post('/validate-step', [StudentMutationInController::class, 'validateStep'])->name('validate-step');
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

        // Persuratan
        Route::name('letters.')->prefix('letters')->group(function () {
            Route::get('/', [StudentLetterController::class, 'index'])->name('index');

            Route::get('/create', [StudentLetterController::class, 'create'])->name('create');
            Route::get('/create/active', [StudentLetterController::class, 'createActive'])->name('create-active');
            Route::post('/active', [StudentLetterController::class, 'storeActive'])->name('store-active');

            Route::get('/create/good-conduct', [StudentLetterController::class, 'createGoodConduct'])->name('create-good-conduct');
            Route::post('/good-conduct', [StudentLetterController::class, 'storeGoodConduct'])->name('store-good-conduct');

            Route::get('/create/poor-family', [StudentLetterController::class, 'createPoorFamily'])->name('create-poor-family');
            Route::post('/poor-family', [StudentLetterController::class, 'storePoorFamily'])->name('store-poor-family');

            Route::get('/{letter}/download', [StudentLetterController::class, 'download'])->name('download');
            Route::delete('/{letter}', [StudentLetterController::class, 'destroy'])->name('destroy');
        });

        // Laporan / Rekapitulasi PDF
        Route::prefix('reports')->name('reports.')->group(function () {
            // Laporan Rekapitulasi Jurusan
            Route::get('/concentration/modal', [StudentReportController::class, 'concentrationModal'])->name('concentration.modal');
            Route::get('/concentration', [StudentReportController::class, 'concentrationReport'])->name('concentration');

            // Laporan Keadaan Siswa
            Route::get('/student-count/modal', [StudentReportController::class, 'studentCountModal'])->name('student-count.modal');
            Route::get('/student-count', [StudentReportController::class, 'studentCountReport'])->name('student-count');

            // Laporan Mutasi Siswa
            Route::get('/mutation/modal', [StudentReportController::class, 'mutationModal'])->name('mutation.modal');
            Route::get('/mutation', [StudentReportController::class, 'mutationReport'])->name('mutation');
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

        Route::get('/', [MasterDataController::class, 'index'])->name('academic');

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
        Route::get('/concentration/create', [MasterDataController::class, 'createConcentration'])->name('concentration.create');
        Route::post('/concentration', [MasterDataController::class, 'storeConcentration'])->name('concentration.store');
        Route::get('/concentration/{id}/edit', [MasterDataController::class, 'editConcentration'])->name('concentration.edit');
        Route::put('/concentration/{id}', [MasterDataController::class, 'updateConcentration'])->name('concentration.update');
        Route::delete('/concentration/{id}', [MasterDataController::class, 'destroyConcentration'])->name('concentration.destroy');

        // Data Sekolah
        Route::prefix('school')->name('school.')->group(function () {
            Route::get('/', [SchoolController::class, 'index'])->name('index');
            Route::get('/edit', [SchoolController::class, 'edit'])->name('edit');
            Route::put('/', [SchoolController::class, 'update'])->name('update');
        });
        // Akademik
        Route::post('/academics', [MasterDataController::class, 'storeAcademic'])->name('academic.store');
        Route::put('/academics/{id}', [MasterDataController::class, 'updateAcademic'])->name('academic.update');
        Route::delete('/academics/{id}', [MasterDataController::class, 'destroyAcademic'])->name('academic.destroy');
    });

    Route::prefix('users')->name('users.')->middleware([EnsureUserIsSuperAdmin::class])->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');

        Route::get('/{user}/edit-role', [UserController::class, 'editRole'])->name('edit-role');
        Route::put('/{user}/edit-role', [UserController::class, 'updateRole'])->name('edit-role.update');

        Route::get('/{user}/edit-password', [UserController::class, 'editPassword'])->name('edit-password');
        Route::put('/{user}/edit-password', [UserController::class, 'updatePassword'])->name('edit-password.update');
    });

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::post('/photo', [ProfileController::class, 'uploadPhoto'])->name('upload-photo');

        Route::get('/edit-data', [ProfileController::class, 'editData'])->name('edit-data');
        Route::put('/update-data', [ProfileController::class, 'updateData'])->name('update-data');

        Route::get('/edit-password', [ProfileController::class, 'editPassword'])->name('edit-password');
        Route::put('/update-password', [ProfileController::class, 'updatePassword'])->name('update-password');
    });
});
