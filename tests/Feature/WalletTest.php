<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Category;
use App\Models\Registration;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('attendee can top up their wallet within limits', function () {
    $user = User::factory()->create(['role' => 'attendee', 'credits' => 100.00]);

    // Test minimum limit (min 50)
    $response = $this->actingAs($user)->post('/attendee/wallet/topup', [
        'amount' => 49,
        'payment_method' => 'gcash',
        'account_number' => '09171234567'
    ]);
    $response->assertSessionHasErrors(['amount']);
    expect($user->fresh()->credits)->toEqual(100.00);

    // Test maximum limit (max 10000)
    $response = $this->actingAs($user)->post('/attendee/wallet/topup', [
        'amount' => 10001,
        'payment_method' => 'gcash',
        'account_number' => '09171234567'
    ]);
    $response->assertSessionHasErrors(['amount']);
    expect($user->fresh()->credits)->toEqual(100.00);

    // Test valid top-up
    $response = $this->actingAs($user)->post('/attendee/wallet/topup', [
        'amount' => 500,
        'payment_method' => 'gcash',
        'account_number' => '09171234567'
    ]);
    $response->assertRedirect();
    expect($user->fresh()->credits)->toEqual(600.00);

    // Verify transaction log
    $transaction = Transaction::where('user_id', $user->id)->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->type)->toEqual('topup');
    expect($transaction->amount)->toEqual(500.00);
    expect($transaction->running_balance)->toEqual(600.00);

    // Test wallet balance cap (max 50000)
    $this->actingAs($user)->post('/attendee/wallet/topup', ['amount' => 9999.00, 'payment_method' => 'gcash', 'account_number' => '09171234567']); // balance 10599
    $this->actingAs($user)->post('/attendee/wallet/topup', ['amount' => 10000.00, 'payment_method' => 'gcash', 'account_number' => '09171234567']); // balance 20599
    $this->actingAs($user)->post('/attendee/wallet/topup', ['amount' => 10000.00, 'payment_method' => 'gcash', 'account_number' => '09171234567']); // balance 30599
    $this->actingAs($user)->post('/attendee/wallet/topup', ['amount' => 10000.00, 'payment_method' => 'gcash', 'account_number' => '09171234567']); // balance 40599
    
    // Now trying to top up 10000 more would exceed 50000 limit
    $response = $this->actingAs($user)->post('/attendee/wallet/topup', ['amount' => 10000.00, 'payment_method' => 'gcash', 'account_number' => '09171234567']);
    $response->assertSessionHas('error');
    expect($user->fresh()->credits)->toBeLessThan(50000.00);
});

test('attendee cannot book a ticket with insufficient credits', function () {
    $attendee = User::factory()->create(['role' => 'attendee', 'credits' => 20.00]);
    $organizer = User::factory()->create(['role' => 'organizer', 'credits' => 0.00]);
    $category = Category::create(['name' => 'Concert', 'slug' => 'concert']);
    
    // Create event manually to avoid factory missing dependencies
    $event = Event::create([
        'organizer_id' => $organizer->id,
        'category_id' => $category->id,
        'title' => 'Test Event',
        'description' => 'Description test event',
        'location' => 'Manila',
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(2)->addHours(2),
        'capacity' => 10,
        'status' => 'upcoming'
    ]);

    $ticket = Ticket::create([
        'event_id' => $event->id,
        'name' => 'VIP',
        'price' => 50.00,
        'quantity' => 10
    ]);

    $response = $this->actingAs($attendee)->post(route('attendee.confirm', $event), [
        'ticket_id' => $ticket->id
    ]);

    $response->assertSessionHas('error');
    expect($attendee->fresh()->credits)->toEqual(20.00);
    expect(Registration::count())->toEqual(0);
});

test('booking a ticket processes correct credit transfers and logs', function () {
    $attendee = User::factory()->create(['role' => 'attendee', 'credits' => 100.00]);
    $organizer = User::factory()->create(['role' => 'organizer', 'credits' => 10.00]);
    $category = Category::create(['name' => 'Concert', 'slug' => 'concert']);
    
    $event = Event::create([
        'organizer_id' => $organizer->id,
        'category_id' => $category->id,
        'title' => 'Test Event 2',
        'description' => 'Description test event 2',
        'location' => 'Cebu',
        'start_date' => now()->addDays(3),
        'end_date' => now()->addDays(3)->addHours(2),
        'capacity' => 10,
        'status' => 'upcoming'
    ]);

    $ticket = Ticket::create([
        'event_id' => $event->id,
        'name' => 'Regular',
        'price' => 40.00,
        'quantity' => 10
    ]);

    $response = $this->actingAs($attendee)->post(route('attendee.confirm', $event), [
        'ticket_id' => $ticket->id
    ]);

    $registration = Registration::first();
    expect($registration)->not->toBeNull();
    $response->assertRedirect(route('attendee.bookings.receipt', $registration));

    // Credit transfers
    expect($attendee->fresh()->credits)->toEqual(60.00);
    expect($organizer->fresh()->credits)->toEqual(50.00);

    // Check transactions
    $attendeeTx = Transaction::where('user_id', $attendee->id)->where('type', 'purchase')->first();
    expect($attendeeTx)->not->toBeNull();
    expect($attendeeTx->amount)->toEqual(-40.00);
    expect($attendeeTx->running_balance)->toEqual(60.00);

    $organizerTx = Transaction::where('user_id', $organizer->id)->where('type', 'earning')->first();
    expect($organizerTx)->not->toBeNull();
    expect($organizerTx->amount)->toEqual(40.00);
    expect($organizerTx->running_balance)->toEqual(50.00);
});

test('cancelling registration refunds attendee and deducts organizer capped at balance', function () {
    $attendee = User::factory()->create(['role' => 'attendee', 'credits' => 60.00]);
    $organizer = User::factory()->create(['role' => 'organizer', 'credits' => 15.00]);
    $category = Category::create(['name' => 'Concert', 'slug' => 'concert']);
    
    $event = Event::create([
        'organizer_id' => $organizer->id,
        'category_id' => $category->id,
        'title' => 'Test Event 3',
        'description' => 'Description test event 3',
        'location' => 'Davao',
        'start_date' => now()->addDays(4),
        'end_date' => now()->addDays(4)->addHours(2),
        'capacity' => 10,
        'status' => 'upcoming'
    ]);

    $ticket = Ticket::create([
        'event_id' => $event->id,
        'name' => 'Regular 2',
        'price' => 40.00,
        'quantity' => 10
    ]);

    $registration = Registration::create([
        'attendee_id' => $attendee->id,
        'ticket_id' => $ticket->id,
        'status' => 'confirmed'
    ]);

    $response = $this->actingAs($attendee)->delete(route('attendee.registrations.destroy', $registration));
    $response->assertSessionHas('success');

    // Attendee gets full refund
    expect($attendee->fresh()->credits)->toEqual(100.00);

    // Organizer balance capped deduction to current balance (15.00)
    expect($organizer->fresh()->credits)->toEqual(0.00);

    // Transaction records
    $attendeeRefundTx = Transaction::where('user_id', $attendee->id)->where('type', 'refund')->first();
    expect($attendeeRefundTx)->not->toBeNull();
    expect($attendeeRefundTx->amount)->toEqual(40.00);

    $organizerDeductTx = Transaction::where('user_id', $organizer->id)->where('type', 'refund_deduction')->first();
    expect($organizerDeductTx)->not->toBeNull();
    expect($organizerDeductTx->amount)->toEqual(-15.00);
});

test('admin can adjust user balances within cap', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'attendee', 'credits' => 100.00]);

    // Add credits
    $response = $this->actingAs($admin)->post(route('admin.users.adjustBalance', $user), [
        'amount' => 150.00,
        'type' => 'add',
        'description' => 'Bonus credit'
    ]);
    $response->assertSessionHas('success');
    expect($user->fresh()->credits)->toEqual(250.00);

    // Subtract credits
    $response = $this->actingAs($admin)->post(route('admin.users.adjustBalance', $user), [
        'amount' => 50.00,
        'type' => 'subtract',
        'description' => 'Correction'
    ]);
    $response->assertSessionHas('success');
    expect($user->fresh()->credits)->toEqual(200.00);

    // Exceed cap
    $response = $this->actingAs($admin)->post(route('admin.users.adjustBalance', $user), [
        'amount' => 50000.00,
        'type' => 'add',
        'description' => 'Exceed cap'
    ]);
    $response->assertSessionHas('error');
    expect($user->fresh()->credits)->toEqual(200.00);
});

test('attendee can download their ticket pdf', function () {
    $attendee = User::factory()->create(['role' => 'attendee', 'credits' => 100.00]);
    $organizer = User::factory()->create(['role' => 'organizer']);
    $category = Category::create(['name' => 'Concert', 'slug' => 'concert']);
    
    $event = Event::create([
        'organizer_id' => $organizer->id,
        'category_id' => $category->id,
        'title' => 'Download Event',
        'description' => 'Description download event',
        'location' => 'Manila',
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(2)->addHours(2),
        'capacity' => 10,
        'status' => 'upcoming'
    ]);

    $ticket = Ticket::create([
        'event_id' => $event->id,
        'name' => 'Regular',
        'price' => 50.00,
        'quantity' => 10
    ]);

    $registration = Registration::create([
        'attendee_id' => $attendee->id,
        'ticket_id' => $ticket->id,
        'status' => 'confirmed'
    ]);

    $response = $this->actingAs($attendee)->get(route('attendee.bookings.download', $registration));
    $response->assertStatus(200);
    expect($response->headers->get('content-type'))->toEqual('application/pdf');
});

test('attendee can download event ics with Manila timezone', function () {
    $attendee = User::factory()->create(['role' => 'attendee']);
    $organizer = User::factory()->create(['role' => 'organizer']);
    $category = Category::create(['name' => 'Concert', 'slug' => 'concert']);
    
    $event = Event::create([
        'organizer_id' => $organizer->id,
        'category_id' => $category->id,
        'title' => 'Download Event ICS',
        'description' => 'Description download event ics',
        'location' => 'Manila',
        'start_date' => now()->parse('2026-05-20 08:00:00'),
        'end_date' => now()->parse('2026-05-20 10:00:00'),
        'capacity' => 10,
        'status' => 'upcoming'
    ]);

    $response = $this->actingAs($attendee)->get(route('attendee.events.calendar.download', $event));
    $response->assertStatus(200);
    expect($response->headers->get('content-type'))->toContain('text/calendar');
    expect($response->getContent())->toContain('DTSTART;TZID=Asia/Manila:');
    expect($response->getContent())->toContain('DTEND;TZID=Asia/Manila:');
});



