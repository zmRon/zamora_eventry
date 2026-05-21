<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Event;
use App\Models\Category;

class AdminController extends Controller
{
    public function index()
    {
        $usersCount = User::count();
        $eventsCount = Event::count();
        $categoriesCount = Category::count();
        
        $lastWeekUsers = User::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();
        $recentUsersCreated = User::where('created_at', '>=', now()->subDays(7))->count();
        $usersTrend = $lastWeekUsers > 0 ? (($recentUsersCreated - $lastWeekUsers) / $lastWeekUsers) * 100 : 100;
        
        $lastWeekEvents = Event::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();
        $recentEventsCreated = Event::where('created_at', '>=', now()->subDays(7))->count();
        $eventsTrend = $lastWeekEvents > 0 ? (($recentEventsCreated - $lastWeekEvents) / $lastWeekEvents) * 100 : 100;
        
        $categoryUsage = Category::withCount('events')->orderBy('events_count', 'desc')->get();
        
        // Activity Feed
        $recentUsers = User::latest()->take(3)->get()->map(function($user) { return ['type' => 'user', 'title' => 'New User', 'desc' => $user->name . ' joined', 'time' => $user->created_at]; });
        $recentEvents = Event::latest()->take(3)->get()->map(function($event) { return ['type' => 'event', 'title' => 'New Event', 'desc' => $event->title . ' created', 'time' => $event->created_at]; });
        $recentBookings = \App\Models\Registration::with('attendee', 'ticket.event')->latest()->take(3)->get()->map(function($reg) { return ['type' => 'booking', 'title' => 'New Booking', 'desc' => ($reg->attendee->name ?? 'User') . ' booked ' . ($reg->ticket->event->title ?? 'an event'), 'time' => $reg->created_at]; });
        
        $activityFeed = collect()->concat($recentUsers)->concat($recentEvents)->concat($recentBookings)->sortByDesc('time')->take(6);
        $totalPlatformRevenue = \App\Models\Transaction::where('type', 'topup')->sum('amount');

        return view('admin.dashboard', compact('usersCount', 'eventsCount', 'categoriesCount', 'categoryUsage', 'usersTrend', 'eventsTrend', 'activityFeed', 'totalPlatformRevenue'));
    }

    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sort')) {
            $direction = $request->direction === 'desc' ? 'desc' : 'asc';
            $validSorts = ['role', 'status', 'created_at'];
            if (in_array($request->sort, $validSorts)) {
                $query->orderBy($request->sort, $direction);
            }
        } else {
            $query->latest();
        }

        $users = $query->paginate(15)->withQueryString();
        $categoryUsage = Category::withCount('events')->orderBy('events_count', 'desc')->get();
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $suspendedUsers = User::where('status', 'suspended')->count();

        return view('admin.users.index', compact('users', 'categoryUsage', 'totalUsers', 'activeUsers', 'suspendedUsers'));
    }

    public function suspendUser(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot suspend yourself.');
        }

        if ($user->status === 'suspended') {
            $user->update([
                'status' => 'active',
                'suspended_until' => null,
            ]);
            return back()->with('success', 'User unsuspended successfully.');
        }

        $validated = $request->validate([
            'duration' => 'required|integer|min:1',
        ]);

        $user->update([
            'status' => 'suspended',
            'suspended_until' => now()->addDays((int) $validated['duration']),
        ]);

        return back()->with('success', 'User suspended successfully.');
    }

    public function bulkActions(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,suspend,activate',
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
        ]);

        $users = User::whereIn('id', $validated['users'])->where('id', '!=', auth()->id());

        switch ($validated['action']) {
            case 'delete':
                $users->delete();
                $message = 'Users deleted successfully.';
                break;
            case 'suspend':
                $users->update(['status' => 'suspended', 'suspended_until' => now()->addDays(7)]);
                $message = 'Users suspended for 7 days.';
                break;
            case 'activate':
                $users->update(['status' => 'active', 'suspended_until' => null]);
                $message = 'Users activated successfully.';
                break;
        }

        return back()->with('success', $message ?? 'Action completed.');
    }

    public function exportUsers()
    {
        $users = User::all();
        $filename = "users_export_" . date('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['ID', 'Name', 'Email', 'Role', 'Status', 'Last Login', 'Created At']);

        foreach ($users as $user) {
            fputcsv($handle, [
                $user->id,
                $user->name,
                $user->email,
                $user->role,
                $user->status,
                $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
                $user->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($handle);
        exit;
    }

    public function auditLogs()
    {
        $logs = \App\Models\AuditLog::with('user')->latest()->paginate(20);
        return view('admin.audit-logs', compact('logs'));
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,organizer,attendee',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        
        $updateData = [
            'role' => $validated['role'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    public function categories()
    {
        $categories = Category::withCount('events')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function reorderCategories(Request $request)
    {
        $order = $request->input('order', []);
        foreach ($order as $index => $id) {
            Category::where('id', $id)->update(['sort_order' => $index]);
        }
        return response()->json(['success' => true]);
    }

    public function createCategory()
    {
        return view('admin.categories.create');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        Category::create($validated);
        return redirect()->route('admin.categories')->with('success', 'Category created successfully.');
    }

    public function deleteCategory(Category $category)
    {
        if ($category->events()->count() > 0) {
            return back()->with('error', 'Cannot delete a category that has associated events.');
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('success', 'Category deleted successfully.');
    }

    public function editCategory(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $category->update($validated);
        return redirect()->route('admin.categories')->with('success', 'Category updated successfully.');
    }

    public function transactions(Request $request)
    {
        $totalPlatformRevenue = \App\Models\Transaction::where('type', 'topup')->sum('amount');

        $query = \App\Models\Transaction::with(['user', 'event', 'registration.ticket']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        return view('admin.transactions.index', compact('transactions', 'totalPlatformRevenue'));
    }

    public function adjustBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:add,subtract',
            'description' => 'required|string|min:3|max:255',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $user) {
                $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
                $amount = abs($request->amount);

                if ($request->type === 'add') {
                    if ($lockedUser->credits + $amount > 50000) {
                        throw new \Exception('Adjustment would exceed user\'s maximum wallet balance cap of ₱50,000.00.');
                    }
                    $lockedUser->addCredits($amount);
                    $logAmount = $amount;
                } else {
                    $lockedUser->deductCredits($amount);
                    $logAmount = -$amount;
                }

                \App\Models\Transaction::create([
                    'user_id' => $lockedUser->id,
                    'type' => 'adjustment',
                    'amount' => $logAmount,
                    'running_balance' => $lockedUser->credits,
                    'status' => 'success',
                    'description' => 'Admin adjustment: ' . $request->description,
                ]);
            });

            return back()->with('success', 'User balance adjusted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to adjust balance: ' . $e->getMessage());
        }
    }
}
