<?php

use App\Livewire\Actions\Logout;
use App\Models\Pengadaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::redirect('/', '/login');

// Logout Route (must be authenticated)
Route::post('/logout', function (Request $request, Logout $logout) {
    $logout();

    return redirect('/');
})->middleware('auth')->name('logout');

// Authenticated Routes
Route::middleware(['auth', 'verified', 'active'])->group(function () {

    // Dashboard
    Route::view('/dashboard', 'pages.dashboard')->name('dashboard');

    // Profile
    Route::view('profile', 'profile')->name('profile');

    // Master Data Routes
    Route::prefix('master-data')->name('master-data.')->group(function () {
        Route::view('/cabangs', 'master-data.cabangs')->middleware('can:cabang_view')->name('cabangs');
        Route::view('/klasters', 'master-data.klasters')->middleware('can:klaster_view')->name('klasters');
        Route::view('/satuans', 'master-data.satuans')->middleware('can:satuan_view')->name('satuans');
        Route::view('/kategori-items', 'master-data.kategori-items')->middleware('can:kategori_item_view')->name('kategori-items');
        Route::view('/vendors', 'master-data.vendors')->middleware('can:vendor_view')->name('vendors');
    });

    // Pengadaan Aset Routes (full-page form for create/edit, route-model-binding with encrypted ID)
    Route::prefix('pengadaan')->name('pengadaan.')->group(function () {
        Route::view('/', 'pengadaan.index')->middleware('can:pengadaan_view')->name('index');
        Route::view('/create', 'pengadaan.create')->middleware('can:pengadaan_create')->name('create');
        Route::get('/{pengadaan}/edit', function (Pengadaan $pengadaan) {
            return view('pengadaan.edit', ['pengadaan' => $pengadaan]);
        })->middleware('can:pengadaan_update')->name('edit');
        Route::get('/{pengadaan}', function (Pengadaan $pengadaan) {
            return view('pengadaan.show', ['pengadaan' => $pengadaan]);
        })->middleware('can:pengadaan_view')->name('show');
    });

    // Notifications
    Route::view('/notifications', 'notifications.index')->middleware('can:notifications_view')->name('notifications.index');
    Route::view('/notifications/send', 'notifications.send')->middleware('can:notifications_send')->name('notifications.send');

    // Chat
    Route::view('/chat', 'chat.index')->middleware('can:chat_view')->name('chat.index');

    // Settings Routes - each route checks its own permission
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::view('/system', 'settings.system')->middleware('can:configuration_view')->name('system');
        Route::view('/users', 'settings.users')->middleware('can:users_view')->name('users');
        Route::view('/roles', 'settings.roles')->middleware('can:roles_view')->name('roles');
    });
});

require __DIR__.'/auth.php';
