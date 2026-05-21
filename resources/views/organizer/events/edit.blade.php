<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="h2 text-gradient">Edit Event: {{ $event->title }}</h2>
            <span class="text-sm text-muted">Last updated: {{ $event->updated_at->diffForHumans() }}</span>
        </div>
    </x-slot>

    <div class="glass-panel" style="max-width: 800px; margin: 0 auto;">
        <form action="{{ route('organizer.events.update', $event) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="form-label">Event Title <span class="text-red-600">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $event->title) }}" required>
                    @error('title') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Category <span class="text-red-600">*</span></label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $event->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Visibility <span class="text-red-600">*</span></label>
                    <select name="is_public" class="form-control" required>
                        <option value="1" {{ old('is_public', $event->is_public ? '1' : '0') == '1' ? 'selected' : '' }}>Public (Visible to everyone)</option>
                        <option value="0" {{ old('is_public', $event->is_public ? '1' : '0') == '0' ? 'selected' : '' }}>Private (Hidden from attendees)</option>
                    </select>
                    @error('is_public') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Status <span class="text-red-600">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="upcoming" {{ old('status', $event->status) == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="ongoing" {{ old('status', $event->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="completed" {{ old('status', $event->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $event->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Location <span class="text-red-600">*</span></label>
                    <input type="text" name="location" class="form-control" value="{{ old('location', $event->location) }}" required>
                    @error('location') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Capacity <span class="text-red-600">*</span></label>
                    <input type="number" name="capacity" min="1" class="form-control" value="{{ old('capacity', $event->capacity) }}" required>
                    @error('capacity') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="form-group col-span-2">
                    <label class="form-label">Event Image</label>
                    <div id="drop-zone" style="border: 2px dashed var(--border); border-radius: var(--radius-md); padding: 2rem; text-align: center; cursor: pointer; background: rgba(0,0,0,0.02); transition: all 0.2s;">
                        <div id="drop-zone-text" class="text-muted" style="{{ $event->image_url ? 'display: none;' : '' }}">
                            <span style="font-size: 2rem; display: block; margin-bottom: 0.5rem;">📸</span>
                            Drag & drop your new image here to replace current
                        </div>
                        <img id="image-preview" src="{{ $event->image_url ?? '' }}" style="display: {{ $event->image_url ? 'block' : 'none' }}; max-height: 200px; margin: 0 auto; border-radius: 8px; box-shadow: var(--shadow-sm);" />
                    </div>
                    <input type="file" name="image" id="file-input" class="form-control" accept="image/*" style="display: none;">
                    <p class="text-muted text-xs mt-1">Leave empty to keep current. Max 2MB. JPEG, PNG, or GIF.</p>
                    @error('image') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Start Date &amp; Time <span class="text-red-600">*</span></label>
                    <input type="datetime-local" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $event->start_date->format('Y-m-d\TH:i')) }}" required>
                    @error('start_date') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">End Date &amp; Time <span class="text-red-600">*</span></label>
                    <input type="datetime-local" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', $event->end_date->format('Y-m-d\TH:i')) }}" required>
                    @error('end_date') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="form-group mt-4">
                <label class="form-label">Description <span class="text-red-600">*</span></label>
                <textarea name="description" id="description" class="form-control" rows="5" required oninput="document.getElementById('char-count').innerText = this.value.length">{{ old('description', $event->description) }}</textarea>
                <div class="text-xs text-muted mt-1 text-right"><span id="char-count">{{ strlen(old('description', $event->description) ?? '') }}</span> characters</div>
                @error('description') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Tickets Section -->
            <div class="mt-8 mb-6 p-4" style="border: 1px solid var(--border); border-radius: var(--radius-md); background: rgba(0,0,0,0.02);">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="h3 text-sm">Ticket Types <span class="text-red-600">*</span></h3>
                    <button type="button" class="btn btn-primary" style="padding: 4px 12px; font-size: 0.8rem;" onclick="addTicketRow()">+ Add Ticket</button>
                </div>
                
                @error('tickets') <span class="text-xs text-red-600 mb-2 block">{{ $message }}</span> @enderror

                <div id="tickets-container">
                    @forelse($event->tickets as $index => $ticket)
                        <div class="ticket-row grid grid-cols-4 gap-4 mb-3 items-end">
                            <input type="hidden" name="tickets[{{ $index }}][id]" value="{{ $ticket->id }}">
                            <div class="col-span-1">
                                <label class="text-xs text-muted mb-1 block">Ticket Name <span class="text-red-600">*</span></label>
                                <input type="text" name="tickets[{{ $index }}][name]" class="form-control" value="{{ $ticket->name }}" required>
                            </div>
                            <div class="col-span-1">
                                <label class="text-xs text-muted mb-1 block">Price (₱) <span class="text-red-600">*</span></label>
                                <input type="number" step="0.01" name="tickets[{{ $index }}][price]" class="form-control" value="{{ $ticket->price }}" required>
                            </div>
                            <div class="col-span-1">
                                <label class="text-xs text-muted mb-1 block">Quantity <span class="text-red-600">*</span></label>
                                <input type="number" name="tickets[{{ $index }}][quantity]" class="form-control" value="{{ $ticket->quantity }}" required>
                            </div>
                            <div class="col-span-1">
                                <label class="text-xs text-transparent mb-1 block select-none">&nbsp;</label>
                                <button type="button" class="btn btn-danger" style="padding: 0.875rem 1rem; width: 100%; border-radius: var(--radius-sm);" onclick="this.closest('.ticket-row').remove()">Remove</button>
                            </div>
                        </div>
                    @empty
                        <div class="ticket-row grid grid-cols-4 gap-4 mb-3 items-end">
                            <div class="col-span-1">
                                <label class="text-xs text-muted mb-1 block">Ticket Name <span class="text-red-600">*</span></label>
                                <input type="text" name="tickets[0][name]" class="form-control" placeholder="e.g. Free, VIP" required>
                            </div>
                            <div class="col-span-1">
                                <label class="text-xs text-muted mb-1 block">Price (₱) <span class="text-red-600">*</span></label>
                                <input type="number" step="0.01" name="tickets[0][price]" class="form-control" value="0" required>
                            </div>
                            <div class="col-span-1">
                                <label class="text-xs text-muted mb-1 block">Quantity <span class="text-red-600">*</span></label>
                                <input type="number" name="tickets[0][quantity]" class="form-control" required>
                            </div>
                            <div class="col-span-1">
                                <label class="text-xs text-transparent mb-1 block select-none">&nbsp;</label>
                                <button type="button" class="btn btn-danger" style="padding: 0.875rem 1rem; width: 100%; border-radius: var(--radius-sm);" onclick="this.closest('.ticket-row').remove()">Remove</button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <a href="{{ route('organizer.events') }}" class="btn btn-glass">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Event</button>
            </div>
        </form>
    </div>

    <script>
        // Set today as minimum for start_date and end_date
        const today = new Date();
        const pad = n => String(n).padStart(2, '0');
        const todayStr = `${today.getFullYear()}-${pad(today.getMonth()+1)}-${pad(today.getDate())}T${pad(today.getHours())}:${pad(today.getMinutes())}`;

        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        startDateInput.min = todayStr;
        endDateInput.min = startDateInput.value || todayStr;

        startDateInput.addEventListener('change', function() {
            endDateInput.min = this.value || todayStr;
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = this.value;
            }
        });

        let ticketIndex = {{ count($event->tickets) > 0 ? count($event->tickets) : 1 }};
        function addTicketRow() {
            const container = document.getElementById('tickets-container');
            const row = document.createElement('div');
            row.className = 'ticket-row grid grid-cols-4 gap-4 mb-3 items-end';
            row.innerHTML = `
                <div class="col-span-1">
                    <label class="text-xs text-muted mb-1 block">Ticket Name <span class="text-red-600">*</span></label>
                    <input type="text" name="tickets[${ticketIndex}][name]" class="form-control" placeholder="e.g. VIP" required>
                </div>
                <div class="col-span-1">
                    <label class="text-xs text-muted mb-1 block">Price (₱) <span class="text-red-600">*</span></label>
                    <input type="number" step="0.01" name="tickets[${ticketIndex}][price]" class="form-control" value="0" required>
                </div>
                <div class="col-span-1">
                    <label class="text-xs text-muted mb-1 block">Quantity <span class="text-red-600">*</span></label>
                    <input type="number" name="tickets[${ticketIndex}][quantity]" class="form-control" required>
                </div>
                <div class="col-span-1">
                    <label class="text-xs text-transparent mb-1 block select-none">&nbsp;</label>
                    <button type="button" class="btn btn-danger" style="padding: 0.875rem 1rem; width: 100%; border-radius: var(--radius-sm);" onclick="this.closest('.ticket-row').remove()">Remove</button>
                </div>
            `;
            container.appendChild(row);
            ticketIndex++;
        }

        // Drag and drop image upload
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const preview = document.getElementById('image-preview');
        const dropText = document.getElementById('drop-zone-text');

        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--primary)';
            dropZone.style.background = 'rgba(var(--primary-rgb), 0.05)';
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--border)';
            dropZone.style.background = 'rgba(0,0,0,0.02)';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--border)';
            dropZone.style.background = 'rgba(0,0,0,0.02)';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateImagePreview();
            }
        });

        fileInput.addEventListener('change', updateImagePreview);

        function updateImagePreview() {
            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    dropText.style.display = 'none';
                }
                reader.readAsDataURL(fileInput.files[0]);
            }
        }

        // Unsaved changes warning
        let isDirty = false;
        
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('change', () => { isDirty = true; });
            element.addEventListener('input', () => { isDirty = true; });
        });

        window.addEventListener('beforeunload', (e) => {
            if (isDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        document.querySelector('form').addEventListener('submit', () => {
            isDirty = false;
        });
    </script>
</x-app-layout>