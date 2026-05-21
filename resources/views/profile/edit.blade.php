<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">Profile</h2>
    </x-slot>

    <div class="grid grid-cols-1 gap-6" style="max-width: 800px; margin: 0 auto; padding-bottom: 4rem;">
        <div class="glass-panel">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="glass-panel">
            @include('profile.partials.update-password-form')
        </div>

        <div class="glass-panel">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
