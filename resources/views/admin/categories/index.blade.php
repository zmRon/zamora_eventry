<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="h2 text-gradient">Manage Categories</h2>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">+ New Category</a>
        </div>
    </x-slot>

    <div class="glass-panel">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;"></th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Events Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="sortable-categories">
                    @foreach($categories as $category)
                    <tr data-id="{{ $category->id }}" class="sortable-row">
                        <td style="cursor: grab; color: var(--text-muted); text-align: center;">
                            <span class="drag-handle" title="Drag to reorder">☰</span>
                        </td>
                        <td>{{ $category->name }}</td>
                        <td title="{{ $category->description }}" style="cursor: help;">
                            {{ Str::limit($category->description, 50) }}
                        </td>
                        <td>
                            <span class="badge {{ $category->events_count > 0 ? 'badge-primary' : 'badge-glass' }}">
                                {{ $category->events_count }}
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-glass" style="padding: 4px 10px; font-size: 0.8rem;">Edit</a>
                                
                                @if($category->events_count == 0)
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 4px 10px; font-size: 0.8rem;" onclick="return confirm('Are you sure you want to delete the category \'{{ $category->name }}\'?')">Delete</button>
                                    </form>
                                @else
                                    <button class="btn btn-danger opacity-50 cursor-not-allowed" style="padding: 4px 10px; font-size: 0.8rem;" title="Cannot delete category with associated events" disabled>Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('sortable-categories');
            if (el) {
                var sortable = Sortable.create(el, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'bg-gray-100',
                    onEnd: function (evt) {
                        var order = [];
                        document.querySelectorAll('.sortable-row').forEach(function (row) {
                            order.push(row.getAttribute('data-id'));
                        });

                        fetch('{{ route('admin.categories.reorder') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: order })
                        }).then(response => response.json())
                          .then(data => {
                              if (!data.success) {
                                  alert('Failed to reorder categories. Please try again.');
                              }
                          });
                    }
                });
            }
        });
    </script>
</x-app-layout>
