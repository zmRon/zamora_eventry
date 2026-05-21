<section>
    <header>
        <h3 class="h3 text-gradient mb-2" style="background: linear-gradient(135deg, #ef4444, #b91c1c); -webkit-background-clip: text;">Delete Account</h3>
        <p class="text-muted text-sm mb-6">
            Once your account is deleted, all of its resources and data will be permanently deleted.
        </p>
    </header>

    <button class="btn btn-danger" onclick="document.getElementById('confirm-user-deletion').style.display='block'">Delete Account</button>

    <div id="confirm-user-deletion" class="animate-fade-in" style="display: {{ $errors->userDeletion->isNotEmpty() ? 'block' : 'none' }}; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border);">
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <h3 class="h3 mb-2" style="font-size: 1.25rem;">Are you sure you want to delete your account?</h3>
            <p class="text-muted text-sm mb-6">
                Please enter your password to confirm you would like to permanently delete your account.
            </p>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input id="password" name="password" type="password" class="form-control" placeholder="Password" />
                @error('password', 'userDeletion')<span class="text-sm mt-2 block" style="color: var(--danger);">{{ $message }}</span>@enderror
            </div>

            <div class="flex justify-between items-center mt-6 pt-4" style="border-top: 1px solid var(--border);">
                <button type="button" class="btn btn-glass" onclick="document.getElementById('confirm-user-deletion').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Delete</button>
            </div>
        </form>
    </div>
</section>
