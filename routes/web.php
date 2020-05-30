<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// authentication
Route::group(['prefix' => 'auth'], function () {
    Route::auth(['register' => false, 'reset' => false, 'verify' => false]);
    Route::get('socialite', ['middleware' => 'guest', 'uses' => 'Auth\LoginController@redirect'])->name('auth.redirect');
    Route::get('callback', ['middleware' => 'guest', 'uses' => 'Auth\LoginController@callback'])->name('auth.callback');
});

Route::group(['middleware' => ['auth']], function () {
    Route::redirect('/', '/repository')->name('home');

    Route::resource('/repository', 'RepositoryController')->except(['show']);
    Route::resource('/backup', 'BackupController')->except([
        'create',
        'destroy',
        'edit',
        'store',
        'update',
    ]);
    Route::put('/backup/{backup}/poll', 'BackupController@poll')->name('backup.poll');
    Route::delete('/backup/{backup}/flush', 'BackupController@flush')->name('backup.flush');
    Route::post('/backup/{backup}/download', 'BackupController@download')->name('backup.download');
});

