<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">Edit User</h2>
    </x-slot>

    <div class="glass-panel" style="max-width: 600px; margin: 0 auto; background: #fff; padding: 2.5rem; border-radius: 12px; box-shadow: var(--shadow-md);">
        <div class="mb-6" style="border-bottom: 1px solid var(--border); padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
            <h3 class="h3" style="color: var(--text-high); margin-bottom: 0.25rem;">{{ $user->name }}</h3>
            <p class="text-muted" style="font-size: 0.9rem;">Joined {{ $user->created_at->format('M d, Y') }} | Current Role: <strong class="text-gradient">{{ ucfirst($user->role) }}</strong></p>
        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Email Field -->
            <div class="form-group mb-4">
                <label class="form-label" style="font-weight: 600; color: var(--text-high);">Email Address <span class="text-red-600">*</span></label>
                <input type="email" name="email" class="input w-full" value="{{ old('email', $user->email) }}" required placeholder="user@example.com">
                @error('email') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Role Dropdown -->
            <div class="form-group mb-4">
                <label class="form-label" style="font-weight: 600; color: var(--text-high);">User Role <span class="text-red-600">*</span></label>
                <select name="role" class="input w-full" style="height: 38px; padding: 0 0.5rem;" required>
                    <option value="attendee" {{ old('role', $user->role) === 'attendee' ? 'selected' : '' }}>Attendee</option>
                    <option value="organizer" {{ old('role', $user->role) === 'organizer' ? 'selected' : '' }}>Organizer</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Password Fields -->
            <div class="glass-panel p-4 mb-4" style="background: #f8fafc; border-color: #e2e8f0; border-radius: 8px;">
                <h4 class="text-sm font-bold mb-3" style="color: var(--text-high); display: flex; align-items: center; gap: 0.25rem;">
                    <span>🔒</span> Change Password (Optional)
                </h4>
                <p class="text-xs text-muted mb-4">Leave fields blank if you do not wish to change the user's password.</p>

                <div class="form-group mb-3">
                    <label class="form-label" style="font-weight: 600;">New Password</label>
                    <input type="password" name="password" class="input w-full" placeholder="Minimum 8 characters">
                    @error('password') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="form-group mb-1">
                    <label class="form-label" style="font-weight: 600;">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="input w-full" placeholder="Re-type new password">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-6 flex justify-between" style="border-top: 1px solid var(--border); padding-top: 1.5rem;">
                <a href="{{ route('admin.users') }}" class="btn btn-glass" style="height: 38px; display: inline-flex; align-items: center;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="height: 38px; display: inline-flex; align-items: center;">Update User</button>
            </div>
        </form>
    </div>
</x-app-layout>
