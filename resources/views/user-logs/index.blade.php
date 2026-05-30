@extends('layouts.app')

@section('content')
<div class="py-2">
    <div class="max-w-[100rem] mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-bold text-gray-900">User activity log</h1>
                <p class="text-gray-600 mt-1 text-sm">Sign-ins, profile changes, and selected administrative actions. Visible to administrators only.</p>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-4">
            <form method="get" action="{{ route('user-logs.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[12rem]">
                    <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Action, description, IP…"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="min-w-[12rem]">
                    <label for="user_id" class="block text-xs font-medium text-gray-600 mb-1">User</label>
                    <select name="user_id" id="user_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All users</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected((string) request('user_id') === (string) $u->id)>
                                {{ $u->name }} ({{ $u->email }}) — {{ $u->role }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium px-4 py-2 rounded-lg">Apply</button>
                @if(request()->hasAny(['search', 'user_id']))
                    <a href="{{ route('user-logs.index') }}" class="text-sm text-blue-600 hover:underline py-2">Clear</a>
                @endif
            </form>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600 border-b">
                        <tr>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">When</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">User</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Action</th>
                            <th class="px-4 py-3 font-medium min-w-[12rem]">Description</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">IP</th>
                            <th class="px-4 py-3 font-medium min-w-[8rem]">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50/80">
                                <td class="px-4 py-2 text-gray-700 whitespace-nowrap tabular-nums">{{ $log->created_at->timezone(config('app.timezone'))->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-2">
                                    @if($log->user)
                                        <span class="font-medium text-gray-900">{{ $log->user->name }}</span>
                                        <span class="text-gray-500 text-xs block">{{ $log->user->email }} · {{ $log->user->role }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2"><code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded text-gray-800">{{ $log->action }}</code></td>
                                <td class="px-4 py-2 text-gray-700">{{ $log->description ?: '—' }}</td>
                                <td class="px-4 py-2 text-gray-600 whitespace-nowrap font-mono text-xs">{{ $log->ip_address ?: '—' }}</td>
                                <td class="px-4 py-2 text-xs text-gray-600 break-all max-w-xs">
                                    @if($log->properties && count($log->properties))
                                        <pre class="whitespace-pre-wrap font-mono text-[11px] leading-snug bg-gray-50 border rounded p-2 max-h-32 overflow-y-auto">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-500">No log entries match your filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
