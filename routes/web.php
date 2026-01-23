<?php

use Illuminate\Support\Facades\Route;

// === Import controller FQCN (Laravel 11)
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\RuanganController;
use App\Http\Controllers\Admin\BarangController;
use App\Http\Controllers\Admin\JadwalPerkuliahanController;
use App\Http\Controllers\Admin\JadwalPerkuliahanTemplateExportController;
use App\Http\Controllers\Admin\KegiatanController;
use App\Http\Controllers\Admin\SystemCalendarController;
use App\Http\Controllers\Admin\BookingsController;
use App\Http\Controllers\Admin\CalendarViewController;
use App\Http\Controllers\Admin\MobilController;
use App\Http\Controllers\Admin\KioskController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Admin\RiwayatPerjalananController;
use App\Http\Controllers\Admin\PermintaanKegiatanController;



// === Redirect root ke login (sesuai rute lama)
Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return redirect()->route('admin.home'); // atau redirect('/home')
})->middleware(['auth', 'verified'])->name('dashboard');

// === /home -> redirect ke admin.home + forward status (sesuai rute lama)
Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }
    return redirect()->route('admin.home');
});

Route::get('/phpinfo', function () {
    phpinfo();
});
// === Grup ADMIN (prefix + name + middleware=auth)
Route::middleware(['auth'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Kegiatan
    Route::get('kegiatan/template', [KegiatanController::class, 'downloadTemplate'])->name('kegiatan.template');
    Route::post('kegiatan/import', [KegiatanController::class, 'import'])->name('kegiatan.import');
    Route::delete('kegiatan/destroy', [KegiatanController::class, 'massDestroy'])->name('kegiatan.massDestroy');
    Route::resource('kegiatan', KegiatanController::class);
    Route::patch('kegiatan/{kegiatan}/status', [KegiatanController::class, 'updateStatus'])
        ->name('kegiatan.updateStatus') // jangan "admin.kegiatan..." karena sudah ada as('admin.')
        ->middleware('role.verification');
    Route::get('kegiatan/{kegiatan}/edit-surat-izin', [KegiatanController::class, 'editSuratIzin'])->name('kegiatan.editSuratIzin');
    Route::patch('kegiatan/{kegiatan}/update-surat-izin', [KegiatanController::class, 'updateSuratIzin'])->name('kegiatan.updateSuratIzin');
    Route::post('kegiatan/{kegiatan}/pinjam-barang', [KegiatanController::class, 'pinjamBarang'])->name('kegiatan.pinjamBarang');
    Route::post('kegiatan/{kegiatan}/kembalikan-barang/{barang}', [KegiatanController::class, 'kembalikanBarang'])->name('kegiatan.kembalikanBarang');

    // Permissions
    Route::delete('permissions/destroy', [PermissionsController::class, 'massDestroy'])->name('permissions.massDestroy');
    Route::resource('permissions', PermissionsController::class);

    // Roles
    Route::delete('roles/destroy', [RolesController::class, 'massDestroy'])->name('roles.massDestroy');
    Route::resource('roles', RolesController::class);

    // Users
    Route::delete('users/destroy', [UsersController::class, 'massDestroy'])->name('users.massDestroy');
    Route::resource('users', UsersController::class);

    // Ruangan
    Route::delete('ruangan/destroy', [RuanganController::class, 'massDestroy'])->name('ruangan.massDestroy');
    Route::resource('ruangan', RuanganController::class);
    Route::patch('ruangan/{id}/toggle', [RuanganController::class, 'toggle'])->name('ruangan.toggle');
    Route::post('ruangan/storeMedia', [RuanganController::class, 'storeMedia'])->name('ruangan.storeMedia');

    // Barangs
    Route::get('barangs/master', [BarangController::class, 'master'])->name('barangs.master');
    Route::delete('barangs/destroy', [BarangController::class, 'massDestroy'])->name('barangs.massDestroy');
    Route::resource('barangs', BarangController::class);


    // Jadwal Perkuliahan
    Route::delete('jadwal-perkuliahan/destroy', [JadwalPerkuliahanController::class, 'massDestroy'])->name('jadwal-perkuliahan.massDestroy');
    Route::resource('jadwal-perkuliahan', JadwalPerkuliahanController::class);
    Route::post('jadwal-perkuliahan/import', [JadwalPerkuliahanController::class, 'import'])->name('jadwal-perkuliahan.import');
    Route::get('jadwal-perkuliahan/template', [JadwalPerkuliahanTemplateExportController::class, 'export'])->name('jadwal-perkuliahan.template');

    // Kalender & Booking
    Route::get('kalender', [SystemCalendarController::class, 'index'])->name('systemCalendar');
    Route::get('cari-ruang', [BookingsController::class, 'cariRuang'])->name('cariRuang');
    Route::post('book-ruang', [BookingsController::class, 'bookRuang'])->name('bookRuang');
    
    Route::delete('mobils/destroy', [MobilController::class, 'massDestroy'])->name('mobils.massDestroy');
    Route::resource('mobils', MobilController::class);
    Route::delete('riwayat-perjalanan/mass-destroy', [RiwayatPerjalananController::class, 'massDestroy'])
        ->name('riwayat-perjalanan.massDestroy');
    Route::resource('riwayat-perjalanan', RiwayatPerjalananController::class)->parameters([
        'riwayat-perjalanan' => 'riwayat_perjalanan' // Memastikan parameter binding benar
        ]);
    Route::patch('riwayat-perjalanan/{riwayat_perjalanan}/selesai', [RiwayatPerjalananController::class, 'selesaikan'])->name('riwayat-perjalanan.selesaikan');
    Route::patch('riwayat-perjalanan/{riwayat_perjalanan}/mulai', [RiwayatPerjalananController::class, 'mulaiJalan'])->name('riwayat-perjalanan.mulai');

    // Kiosk Mode (fullscreen TV dashboard)
    Route::get('kiosk', [KioskController::class, 'index'])->name('kiosk');
    Route::get('api/kiosk/events', [KioskController::class, 'events'])->name('api.kiosk.events');

    // Statistik (hanya untuk admin/home_access)
    Route::get('statistics', [\App\Http\Controllers\Admin\StatisticsController::class, 'index'])
        ->name('statistics.index');
    Route::get('statistics/export-excel', [App\Http\Controllers\Admin\StatisticsController::class, 'exportExcel'])
        ->name('statistics.exportExcel');
    // Route Permintaan Kegiatan
    Route::post(
        'permintaan-kegiatan/{permintaan_kegiatan}/proses-konsumsi',
        [PermintaanKegiatanController::class, 'prosesKonsumsi']
    )->name('permintaan-kegiatan.prosesKonsumsi');

    Route::resource('permintaan-kegiatan', PermintaanKegiatanController::class);


    // API Holidays
    Route::get('api/holidays', [CalendarViewController::class, 'getHolidays'])->name('api.holidays');
});

Route::get('/dashboard-signage', function () {
    return view('signage');
});

// === Grup PROFILE (Change Password) dengan pengecekan file controller (sesuai rute lama)
Route::middleware(['auth'])->prefix('profile')->as('profile.')->group(function () {
    if (class_exists(ChangePasswordController::class)) {
        Route::get('password', [ChangePasswordController::class, 'edit'])->name('password.edit');
        Route::post('password', [ChangePasswordController::class, 'update'])->name('password.update');
        Route::post('profile', [ChangePasswordController::class, 'updateProfile'])->name('password.updateProfile');
        Route::post('profile/destroy', [ChangePasswordController::class, 'destroy'])->name('password.destroyProfile');
    }
});

// === Rute auth dari Breeze (gantikan Auth::routes())
require __DIR__.'/auth.php';
