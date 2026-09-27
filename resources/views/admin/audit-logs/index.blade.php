@extends('layouts.admin', ['title' => 'Security Audit Logs'])

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-sora font-black text-slate-900">Security & Operation Audit Logs</h1>
        <p class="text-xs font-mono text-slate-500 mt-1">Immutable audit trail of all administrative, scoring, publishing, and green room activities.</p>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="rounded-2xl bg-white border border-slate-200 p-4 grid grid-cols-1 sm:grid-cols-3 gap-4 shadow-sm">
        <div>
            <select name="action" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">All Actions</option>
                @foreach($actions as $act)
                    <option value="{{ $act }}" {{ $action == $act ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $act)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="user_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">All Users</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->role }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 py-2.5 bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase rounded-xl hover:brightness-110 shadow-md shadow-[#f3bd2e]/20">Filter</button>
            <a href="{{ route('admin.audit-logs.index') }}" class="px-3 py-2.5 bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 rounded-xl text-xs font-mono font-semibold">Reset</a>
        </div>
    </form>

    <!-- Logs Table -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm" x-data="{ expanded: null }">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Timestamp</th>
                        <th class="px-6 py-4 font-semibold">Actor</th>
                        <th class="px-6 py-4 font-semibold">Action</th>
                        <th class="px-6 py-4 font-semibold">Entity</th>
                        <th class="px-6 py-4 font-semibold">IP Address</th>
                        <th class="px-6 py-4 text-right font-semibold">Payload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('M d, H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $log->user?->name ?? 'System' }}</div>
                                <div class="text-[10px] text-[#f3bd2e] uppercase font-semibold">{{ $log->user?->role ?? 'CRON/AUTO' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded bg-amber-50 text-[#f3bd2e] border border-amber-200 text-[11px] font-bold">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                @if($log->auditable_type)
                                    {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-mono text-[11px]">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($log->new_values || $log->old_values)
                                    <button @click="expanded = (expanded === {{ $log->id }} ? null : {{ $log->id }})" class="text-[#f3bd2e] hover:underline font-semibold">
                                        <span x-text="expanded === {{ $log->id }} ? 'Hide' : 'Inspect'">Inspect</span>
                                    </button>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                        <!-- Expandable Payload Details -->
                        @if($log->new_values || $log->old_values)
                            <tr x-show="expanded === {{ $log->id }}" style="display: none;" class="bg-slate-50 border-t border-b border-slate-200">
                                <td colspan="6" class="p-4 sm:p-6 space-y-3">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-[11px]">
                                        @if($log->old_values)
                                            <div>
                                                <h4 class="text-slate-700 font-bold mb-1 uppercase">Previous State:</h4>
                                                <pre class="p-3 bg-white rounded-xl border border-slate-200 overflow-x-auto text-red-600 font-mono">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        @endif
                                        @if($log->new_values)
                                            <div>
                                                <h4 class="text-slate-700 font-bold mb-1 uppercase">Updated State / Payload:</h4>
                                                <pre class="p-3 bg-white rounded-xl border border-slate-200 overflow-x-auto text-emerald-700 font-mono">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">No audit logs recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $logs->links() }}
    </div>
</div>
@endsection
