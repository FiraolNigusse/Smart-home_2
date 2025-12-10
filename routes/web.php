<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\RoleChangeRequestController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\BiometricCredentialController;
use App\Http\Controllers\Auth\MfaChallengeController;

Route::middleware('forcehttps')->group(function () {
    // Temporary debug route - remove after fixing reCAPTCHA
    Route::get('/debug-recaptcha', function () {
        return view('debug-recaptcha');
    })->name('debug.recaptcha');
    
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/mfa-challenge', [MfaChallengeController::class, 'show'])->name('mfa.challenge');
    Route::post('/mfa-challenge', [MfaChallengeController::class, 'verify'])->name('mfa.verify');

    Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Devices
    Route::resource('devices', DeviceController::class);
    Route::post('/devices/{device}/control', [DeviceController::class, 'control'])->name('devices.control');
    Route::middleware('role:owner')->group(function () {
        Route::post('/devices/{device}/permissions', [DeviceController::class, 'grantPermission'])->name('devices.permissions.store');
        Route::delete('/devices/{device}/permissions/{permission}', [DeviceController::class, 'revokePermission'])->name('devices.permissions.destroy');
    });

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    Route::get('/audit-logs/export/json', [AuditLogController::class, 'export'])->name('audit-logs.export');

    // Rules (Owner only)
    Route::middleware('role:owner')->group(function () {
        Route::resource('rules', RuleController::class);
    });

    // Role change requests
    Route::get('/role-requests', [RoleChangeRequestController::class, 'index'])->name('role-requests.index');
    Route::post('/role-requests', [RoleChangeRequestController::class, 'store'])->name('role-requests.store');
    Route::middleware('role:owner')->group(function () {
        Route::get('/admin/role-requests', [RoleChangeRequestController::class, 'reviewIndex'])->name('role-requests.review');
        Route::patch('/admin/role-requests/{roleChangeRequest}/approve', [RoleChangeRequestController::class, 'approve'])->name('role-requests.approve');
        Route::patch('/admin/role-requests/{roleChangeRequest}/deny', [RoleChangeRequestController::class, 'deny'])->name('role-requests.deny');
    });

    // Security center
    Route::middleware('role:owner')->group(function () {
        Route::get('/security/tokens', [ApiTokenController::class, 'index'])->name('security.tokens.index');
        Route::post('/security/tokens', [ApiTokenController::class, 'store'])->name('security.tokens.store');
        Route::delete('/security/tokens/{tokenId}', [ApiTokenController::class, 'destroy'])->name('security.tokens.destroy');
    });

    Route::get('/security/biometrics', [BiometricCredentialController::class, 'index'])->name('security.biometrics.index');
    Route::post('/security/biometrics', [BiometricCredentialController::class, 'store'])->name('security.biometrics.store');
    Route::delete('/security/biometrics/{credential}', [BiometricCredentialController::class, 'destroy'])->name('security.biometrics.destroy');

    // WebAuthn endpoints
    Route::prefix('api/webauthn')->name('webauthn.')->group(function () {
        Route::get('/registration/challenge', [\App\Http\Controllers\Auth\WebAuthnController::class, 'registrationChallenge'])->name('registration.challenge');
        Route::post('/registration', [\App\Http\Controllers\Auth\WebAuthnController::class, 'register'])->name('registration');
        Route::get('/authentication/challenge', [\App\Http\Controllers\Auth\WebAuthnController::class, 'authenticationChallenge'])->name('authentication.challenge');
        Route::post('/authentication', [\App\Http\Controllers\Auth\WebAuthnController::class, 'authenticate'])->name('authentication');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
    require __DIR__.'/auth.php';
});
