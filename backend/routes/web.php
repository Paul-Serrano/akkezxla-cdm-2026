<?php

use App\Livewire\Admin\CreateUser;
use App\Livewire\Admin\EditConfig;
use App\Livewire\Admin\ImportData;
use App\Livewire\Admin\ManageRoles;
use App\Livewire\Auth\Login;
use App\Livewire\EditProfile;
use App\Http\Controllers\FootballDataExplorerController;
use App\Livewire\BettingGuide as BettingGuidePage;
use App\Livewire\MatchDay;
use App\Livewire\Ranking;
use App\Livewire\Standings;
use App\Livewire\WinamaxBetSummary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Lightweight health endpoints for uptime checks.
Route::get('/health', static fn () => response()->json([
    'status' => 'ok',
], 200));

Route::get('/ready', static fn () => response()->json([
    'status' => 'ok',
    'timestamp' => now()->toIso8601String(),
], 200));

Route::get('/', Standings::class);
Route::get('/matchday', MatchDay::class);
Route::get('/matchday/{matchday}', MatchDay::class);
Route::get('/betting-guide', BettingGuidePage::class)->name('betting-guide');

// Auth
Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');

// Admin
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/users', CreateUser::class)->name('admin.users');
    Route::get('/admin/config', EditConfig::class)->name('admin.config');
    Route::get('/admin/import-data', ImportData::class)->name('admin.import-data');
    Route::get('/admin/roles', ManageRoles::class)->name('admin.roles');
});

Route::get('/profile', EditProfile::class)
    ->middleware('auth')
    ->name('profile');

// Restricted
Route::get('/ranking', Ranking::class)
    ->name('ranking');

Route::get('/football-data-explorer', FootballDataExplorerController::class)
    ->middleware(['auth', 'admin'])
    ->name('football-data.explorer');

Route::get('/winamax-bet', WinamaxBetSummary::class)
    ->middleware(['auth', 'akkezxla'])
    ->name('winamax-bet.summary');

