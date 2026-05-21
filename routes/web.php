<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrganizerController;
use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('auditLogs');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::get('/transactions', [AdminController::class, 'transactions'])->name('transactions.index');
        Route::post('/users/{user}/adjust-balance', [AdminController::class, 'adjustBalance'])->name('users.adjustBalance');

        Route::get('/users/export', [AdminController::class, 'exportUsers'])->name('users.export');
        Route::post('/users/bulk', [AdminController::class, 'bulkActions'])->name('users.bulk');
        Route::put('/users/{user}/suspend', [AdminController::class, 'suspendUser'])->name('users.suspend');

        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy');

        Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
        Route::post('/categories/reorder', [AdminController::class, 'reorderCategories'])->name('categories.reorder');
        Route::get('/categories/create', [AdminController::class, 'createCategory'])->name('categories.create');
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::get('/categories/{category}/edit', [AdminController::class, 'editCategory'])->name('categories.edit');
        Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminController::class, 'deleteCategory'])->name('categories.destroy');
    });

    // Organizer routes
    Route::middleware('role:organizer')->prefix('organizer')->name('organizer.')->group(function () {
        Route::get('/dashboard', [OrganizerController::class, 'index'])->name('dashboard');
        Route::get('/events', [OrganizerController::class, 'events'])->name('events');
        Route::get('/events/create', [OrganizerController::class, 'createEvent'])->name('events.create');
        Route::post('/events', [OrganizerController::class, 'storeEvent'])->name('events.store');
        Route::get('/events/{event}/edit', [OrganizerController::class, 'editEvent'])->name('events.edit');
        Route::put('/events/{event}', [OrganizerController::class, 'updateEvent'])->name('events.update');
        Route::put('/events/{event}/status', [OrganizerController::class, 'updateStatus'])->name('events.status');
        Route::delete('/events/{event}', [OrganizerController::class, 'deleteEvent'])->name('events.destroy');
        Route::post('/events/{event}/clone', [OrganizerController::class, 'cloneEvent'])->name('events.clone');
        
        Route::get('/events/{event}/tickets', [OrganizerController::class, 'tickets'])->name('events.tickets');
        Route::get('/events/{event}/tickets/create', [OrganizerController::class, 'createTicket'])->name('events.tickets.create');
        Route::post('/events/{event}/tickets', [OrganizerController::class, 'storeTicket'])->name('events.tickets.store');
        Route::delete('/tickets/{ticket}', [OrganizerController::class, 'deleteTicket'])->name('tickets.destroy');
        
        Route::get('/events/{event}/attendees', [OrganizerController::class, 'attendees'])->name('events.attendees');
        Route::put('/registrations/{registration}/status', [OrganizerController::class, 'updateRegistrationStatus'])->name('registrations.status');

        Route::get('/wallet', [OrganizerController::class, 'wallet'])->name('wallet.index');
    });

    // Attendee routes
    Route::middleware('role:attendee')->prefix('attendee')->name('attendee.')->group(function () {
        Route::get('/dashboard', [AttendeeController::class, 'index'])->name('dashboard');
        Route::get('/calendar', [AttendeeController::class, 'calendar'])->name('calendar');
        Route::get('/events', [AttendeeController::class, 'events'])->name('events');
        Route::get('/events/{event}', [AttendeeController::class, 'show'])->name('events.show');
        Route::get('/events/{event}/ics', [AttendeeController::class, 'downloadICS'])->name('events.calendar.download');
        Route::post('/events/{event}/register', [AttendeeController::class, 'register'])->name('events.register');
        Route::get('/tickets', [AttendeeController::class, 'myTickets'])->name('tickets');
        Route::post('/events/{event}/feedback', [AttendeeController::class, 'submitFeedback'])->name('events.feedback');
        Route::delete('/registrations/{registration}', [AttendeeController::class, 'cancelRegistration'])->name('registrations.destroy');

        // Favorites
        Route::post('/events/{event}/favorite', [AttendeeController::class, 'toggleFavorite'])->name('events.favorite');
        Route::get('/favorites', [AttendeeController::class, 'favorites'])->name('favorites');

        // Booking flow
        Route::get('/events/{event}/checkout', [AttendeeController::class, 'checkout'])->name('checkout');
        Route::post('/events/{event}/confirm', [AttendeeController::class, 'confirm'])->name('confirm');

        // Wallet & Receipt
        Route::get('/wallet', [\App\Http\Controllers\WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/topup', [\App\Http\Controllers\WalletController::class, 'topup'])->name('wallet.topup');
        Route::get('/bookings/{registration}/receipt', [AttendeeController::class, 'receipt'])->name('bookings.receipt');
        Route::get('/bookings/{registration}/download', [AttendeeController::class, 'downloadTicket'])->name('bookings.download');
    });
});

require __DIR__.'/auth.php';
