<?php

Route::redirect('/', '/login');
Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }

    return redirect()->route('admin.home');
});

Auth::routes(['register' => false]);

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
    Route::get('/', 'HomeController@index')->name('home');
    Route::patch('kegiatan/{kegiatan}/update-status', 'HomeController@updateStatus')->name('admin.kegiatan.updateStatus');

    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

    // Ruangan
    Route::delete('ruangan/destroy', 'RuanganController@massDestroy')->name('ruangan.massDestroy');
    Route::resource('ruangan', 'RuanganController');
    Route::patch('ruangan/{id}/toggle', 'RuanganController@toggle')->name('ruangan.toggle');

    // Kegiatan
    Route::delete('kegiatan/destroy', 'KegiatanController@massDestroy')->name('kegiatan.massDestroy');
    Route::resource('kegiatan', 'KegiatanController');
    Route::patch('kegiatan/{kegiatan}/status', 'KegiatanController@updateStatus')
        ->name('kegiatan.updateStatus')
        ->middleware('role.verification');
    Route::get('kegiatan/{kegiatan}/edit-surat-izin', 'KegiatanController@editSuratIzin')->name('kegiatan.editSuratIzin');
    Route::patch('kegiatan/{kegiatan}/update-surat-izin', 'KegiatanController@updateSuratIzin')->name('kegiatan.updateSuratIzin');

    Route::get('kalender', 'SystemCalendarController@index')->name('systemCalendar');
    Route::get('cari-ruang', 'BookingsController@cariRuang')->name('cariRuang');
    Route::post('book-ruang', 'BookingsController@bookRuang')->name('bookRuang');
});
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'namespace' => 'Auth', 'middleware' => ['auth']], function () {
    // Change password
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', 'ChangePasswordController@edit')->name('password.edit');
        Route::post('password', 'ChangePasswordController@update')->name('password.update');
        Route::post('profile', 'ChangePasswordController@updateProfile')->name('password.updateProfile');
        Route::post('profile/destroy', 'ChangePasswordController@destroy')->name('password.destroyProfile');
    }
});
Auth::routes();

Route::get('/home', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('home');
