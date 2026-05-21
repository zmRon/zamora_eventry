<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">System Settings</h2>
    </x-slot>

    <div class="glass-panel max-w-2xl mx-auto p-6">
        <h3 class="h3 mb-4">Maintenance Mode</h3>
        <p class="text-muted mb-6">Putting the application in maintenance mode will prevent regular users from accessing the site. Only administrators will be able to log in.</p>

        <!-- Dummy form for UI demonstration as requested -->
        <form action="#" method="POST">
            @csrf
            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="maintenance_mode" class="form-checkbox text-primary rounded" disabled>
                    <span>Enable Maintenance Mode</span>
                </label>
                <p class="text-xs text-muted mt-1">(This is currently disabled in this environment)</p>
            </div>

            <button type="button" class="btn btn-primary opacity-50 cursor-not-allowed">Save Settings</button>
        </form>
    </div>
</x-app-layout>
