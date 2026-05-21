<x-guest-layout>
    <div style="text-align: center; margin-bottom: 2rem;">
        <h2 class="h2 mb-2">Welcome Back</h2>
        <p class="text-muted">Sign in to your account</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm" style="color: var(--danger);" />
        </div>

        <!-- Password -->
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm" style="color: var(--danger);" />
        </div>

        <!-- Remember Me -->
        <div class="form-group flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember" style="accent-color: var(--primary);">
            <label for="remember_me" class="text-sm text-muted">Remember me</label>
        </div>

        <div class="flex flex-col gap-4 mt-6">
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Log in
            </button>
            
            @if (Route::has('password.request'))
                <a class="text-sm text-muted text-center" style="text-decoration: none;" href="{{ route('password.request') }}">
                    Forgot your password?
                </a>
            @endif
        </div>
        
        <div class="mt-6 text-center text-sm text-muted">
            Don't have an account? <a href="{{ route('register') }}" style="color: var(--primary); text-decoration: none; font-weight: 500;">Sign up</a>
        </div>
    </form>
</x-guest-layout>
