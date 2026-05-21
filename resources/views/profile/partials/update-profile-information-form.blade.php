<section>
    <header>
        <h3 class="h3 text-gradient mb-2">Profile Information</h3>
        <p class="text-muted text-sm mb-6">
            Update your account's profile information and email address.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="form-group">
            <label for="name" class="form-label">Name</label>
            <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name')<span class="text-sm mt-2 block" style="color: var(--danger);">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @error('email')<span class="text-sm mt-2 block" style="color: var(--danger);">{{ $message }}</span>@enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-4">
                    <p class="text-sm text-muted">
                        Your email address is unverified.
                        <button form="send-verification" class="btn btn-glass" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                            Click here to re-send the verification email.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm" style="color: var(--success);">
                            A new verification link has been sent to your email address.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 mt-6 pt-4" style="border-top: 1px solid var(--border);">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            @if (session('status') === 'profile-updated')
                <p class="text-sm text-muted animate-fade-in" style="margin: 0; color: var(--success);">Saved successfully.</p>
            @endif
        </div>
    </form>
</section>
