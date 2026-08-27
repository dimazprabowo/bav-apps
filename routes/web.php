<?php

use App\Livewire\Actions\Logout;
use App\Models\Alat;
use App\Models\LogBookPeminjaman;
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

        // Alat (full-page form for create/edit, route-model-binding with encrypted ID)
        Route::prefix('alat')->name('alat.')->group(function () {
            Route::view('/', 'master-data.alats')->middleware('can:alat_view')->name('index');
            Route::view('/create', 'master-data.alats-create')->middleware('can:alat_create')->name('create');
            Route::get('/{alat}/edit', function (Alat $alat) {
                return view('master-data.alats-edit', ['alat' => $alat]);
            })->middleware('can:alat_update')->name('edit');
            Route::get('/{alat}', function (Alat $alat) {
                return view('master-data.alats-show', ['alat' => $alat]);
            })->middleware('can:alat_view')->name('show');
        });
    });

    // Operasional Routes
    Route::prefix('operasional')->name('operasional.')->group(function () {
        Route::prefix('logbook')->name('logbook.')->group(function () {
            Route::view('/', 'operasional.logbook')->middleware('can:logbook_view')->name('index');
            Route::view('/create', 'operasional.logbook-create')->middleware('can:logbook_create')->name('create');
            Route::get('/{logBook}/edit', function (LogBookPeminjaman $logBook) {
                return view('operasional.logbook-edit', ['logBook' => $logBook]);
            })->middleware('can:logbook_update')->name('edit');
        });
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
