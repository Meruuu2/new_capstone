@extends('layouts.admin')

@section('admin-content')
<div class="container mx-auto max-w-7xl space-y-6 px-4 py-8">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Administration</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">Audit trail</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-600">Read-only history of application changes and sign-in activity. Sensitive values are redacted.</p>
        </div>
    </header>

    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
        <label class="text-sm font-medium text-slate-700">
            Search
            <input name="search" value="{{ $filters['search'] ?? '' }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm" placeholder="Actor, record ID, route">
        </label>
        <label class="text-sm font-medium text-slate-700">
            Action
            <select name="action" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                <option value="">All actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ ucfirst($action) }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm font-medium text-slate-700">
            Record type
            <select name="subject_type" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                <option value="">All record types</option>
                @foreach ($subjectTypes as $type)
                    <option value="{{ $type }}" @selected(($filters['subject_type'] ?? '') === $type)>{{ class_basename($type) }}</option>
                @endforeach
            </select>
        </label>
        <div class="flex items-end gap-2">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit">Filter</button>
            <a class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" href="{{ route('admin.audit-logs.index') }}">Clear</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">When</th>
                        <th class="px-4 py-3">Actor</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Record</th>
                        <th class="px-4 py-3">Changes</th>
                        <th class="px-4 py-3">Request</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-slate-900">{{ $log->actor?->name ?? 'System / public' }}</span>
                                @if ($log->actor?->email)<span class="block text-xs text-slate-500">{{ $log->actor->email }}</span>@endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-700">{{ ucfirst($log->action) }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</td>
                            <td class="max-w-xl px-4 py-3">
                                <details>
                                    <summary class="cursor-pointer font-medium text-blue-700">View field changes</summary>
                                    <div class="mt-2 grid gap-3 md:grid-cols-2">
                                        <div>
                                            <p class="mb-1 text-xs font-semibold uppercase text-slate-500">Before</p>
                                            <pre class="overflow-x-auto rounded bg-slate-50 p-2 text-xs">{{ json_encode($log->old_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </div>
                                        <div>
                                            <p class="mb-1 text-xs font-semibold uppercase text-slate-500">After</p>
                                            <pre class="overflow-x-auto rounded bg-slate-50 p-2 text-xs">{{ json_encode($log->new_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </div>
                                    </div>
                                </details>
                            </td>
                            <td class="max-w-xs px-4 py-3 text-xs text-slate-500">
                                <span class="block">{{ $log->route ?? '—' }}</span>
                                <span class="block">{{ $log->ip_address ?? '—' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">No audit records match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
