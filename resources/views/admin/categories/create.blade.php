<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">Create Category</h2>
    </x-slot>

    <div class="glass-panel" style="max-width: 600px; margin: 0 auto;">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Category Name <span class="text-red-600">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                @error('description') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="mt-6 flex justify-between">
                <a href="{{ route('admin.categories') }}" class="btn btn-glass">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</x-app-layout>
