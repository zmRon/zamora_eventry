<section>
    <header>
        <h3 class="h3 text-gradient mb-2">Update Password</h3>
        <p class="text-muted text-sm mb-6">
            Ensure your account is using a long, random password to stay secure.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="form-group">
            <label for="update_password_current_password" class="form-label">Current Password</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" />
            @error('current_password', 'updatePassword')<span class="text-sm mt-2 block" style="color: var(--danger);">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="update_password_password" class="form-label">New Password</label>
            <input id="update_password_password" name="password" type="password" class="form-control" autocomplete="new-password" />
            @error('password', 'updatePassword')<span class="text-sm mt-2 block" style="color: var(--danger);">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" />
            @error('password_confirmation', 'updatePassword')<span class="text-sm mt-2 block" style="color: var(--danger);">{{ $message }}</span>@enderror
        </div>

        <div class="flex items-center gap-4 mt-6 pt-4" style="border-top: 1px solid var(--border);">
            <button type="submit" class="btn btn-primary">Update Password</button>
            @if (session('status') === 'password-updated')
                <p class="text-sm text-muted animate-fade-in" style="margin: 0; color: var(--success);">Saved successfully.</p>
            @endif
        </div>
    </form>
</section>
