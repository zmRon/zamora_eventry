<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Transaction;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Transaction::where('user_id', $user->id)
            ->with(['registration.ticket.event', 'event']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->paginate(10)->withQueryString();

        return view('attendee.wallet.index', compact('transactions', 'user'));
    }

    public function topup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50|max:10000',
            'payment_method' => 'required|in:gcash,maya,card',
            'card_number' => [
                'nullable',
                'string',
                'required_if:payment_method,card',
                'regex:/^\d[0-9\s-]*$/',
            ],
            'account_number' => [
                'nullable',
                'string',
                'required_if:payment_method,gcash,payment_method,maya',
                'regex:/^\d[0-9\s-]*$/',
            ],
        ], [
            'amount.min' => 'Minimum top-up amount is ₱50.00.',
            'amount.max' => 'Maximum top-up amount is ₱10,000.00.',
            'card_number.regex' => 'The card number must be a valid positive number sequence.',
            'account_number.regex' => 'The account/phone number must be a valid positive number sequence.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::where('id', Auth::id())->lockForUpdate()->firstOrFail();

                if ($user->credits + $request->amount > 50000) {
                    throw new \Exception('Top-up would exceed the maximum wallet balance of ₱50,000.00.');
                }

                $user->addCredits($request->amount);

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'topup',
                    'amount' => $request->amount,
                    'running_balance' => $user->credits,
                    'payment_method' => $request->payment_method,
                    'status' => 'success',
                    'description' => 'Simulated top-up via ' . strtoupper($request->payment_method),
                ]);
            });

            return redirect()->route('attendee.wallet.index')->with('success', 'Wallet topped up successfully!');
        } catch (\Exception $e) {
            return redirect()->route('attendee.wallet.index')->with('error', $e->getMessage());
        }
    }
}
