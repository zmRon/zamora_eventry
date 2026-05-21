<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">System Audit Logs</h2>
    </x-slot>

    <div class="glass-panel">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Target Type</th>
                        <th>Target ID</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $log->user ? $log->user->name : 'System' }}</td>
                        <td><span class="badge badge-primary">{{ $log->action }}</span></td>
                        <td>{{ $log->target_type ?? '-' }}</td>
                        <td>{{ $log->target_id ?? '-' }}</td>
                        <td>{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No audit logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>
