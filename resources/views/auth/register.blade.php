<x-guest-layout>
    <div style="text-align: center; margin-bottom: 2rem;">
        <h2 class="h2 mb-2">Create Account</h2>
        <p class="text-muted">Join the premium event experience</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="form-group">
            <label class="form-label" for="name">Name</label>
            <input id="name" class="form-control" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm" style="color: var(--danger);" />
        </div>

        <!-- Email Address -->
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm" style="color: var(--danger);" />
        </div>

        <!-- Password -->
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input id="password" class="form-control" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm" style="color: var(--danger);" />
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm" style="color: var(--danger);" />
        </div>

        <div class="flex flex-col gap-4 mt-6">
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Sign Up
            </button>
        </div>
        
        <div class="mt-6 text-center text-sm text-muted">
            Already have an account? <a href="{{ route('login') }}" style="color: var(--primary); text-decoration: none; font-weight: 500;">Log in</a>
        </div>
    </form>
</x-guest-layout>
