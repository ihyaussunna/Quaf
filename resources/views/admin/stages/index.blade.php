@extends('layouts.admin', ['title' => 'Stage Management'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-sora font-black text-slate-900">Festival Stages</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Configure venues, switch live stage statuses (Active/Break/Closed), and set current events.</p>
        </div>
        <a href="{{ route('admin.stages.create') }}" class="px-4 py-2.5 rounded-xl bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase tracking-wider hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
            + New Stage
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($stages as $stage)
            <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-amber-50 text-[#f3bd2e] border border-[#f3bd2e]/30">
                                {{ $stage->code }}
                            </span>
                            <span class="text-xs font-mono text-slate-500">Capacity: {{ $stage->capacity }} seats</span>
                        </div>

                        <!-- Status Badge -->
                        @if($stage->status === 'active')
                            <span class="px-2.5 py-1 rounded text-xs font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">LIVE ACTIVE</span>
                        @elseif($stage->status === 'break')
                            <span class="px-2.5 py-1 rounded text-xs font-mono font-bold bg-amber-50 text-amber-700 border border-amber-200">INTERMISSION</span>
                        @else
                            <span class="px-2.5 py-1 rounded text-xs font-mono font-bold bg-slate-100 text-slate-600 border border-slate-200">CLOSED</span>
                        @endif
                    </div>

                    <h3 class="text-2xl font-sora font-bold text-slate-900 mb-1">{{ $stage->name }}</h3>
                    <p class="text-xs text-slate-500 mb-6 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                        <span>Location: {{ $stage->location ?? 'Campus Arena' }}</span>
                    </p>

                    <!-- Quick Live Status & Program Selector Form -->
                    <form method="POST" action="{{ route('admin.stages.live-status', $stage) }}" class="space-y-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs font-mono">
                            <div>
                                <label class="block text-[10px] text-slate-600 uppercase mb-1 font-semibold">Live Status</label>
                                <select name="status" class="w-full bg-white border border-slate-300 rounded-lg px-2.5 py-2 text-slate-900 text-xs focus:outline-none focus:border-[#f3bd2e]">
                                    <option value="active" {{ $stage->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="break" {{ $stage->status === 'break' ? 'selected' : '' }}>Break</option>
                                    <option value="closed" {{ $stage->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-600 uppercase mb-1 font-semibold">Current Program</label>
                                <select name="current_program_id" class="w-full bg-white border border-slate-300 rounded-lg px-2.5 py-2 text-slate-900 text-xs focus:outline-none focus:border-[#f3bd2e]">
                                    <option value="">None (Break)</option>
                                    @foreach($programs as $prog)
                                        <option value="{{ $prog->id }}" {{ $stage->current_program_id == $prog->id ? 'selected' : '' }}>{{ $prog->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-600 uppercase mb-1 font-semibold">Next Program</label>
                                <select name="next_program_id" class="w-full bg-white border border-slate-300 rounded-lg px-2.5 py-2 text-slate-900 text-xs focus:outline-none focus:border-[#f3bd2e]">
                                    <option value="">None</option>
                                    @foreach($programs as $prog)
                                        <option value="{{ $prog->id }}" {{ $stage->next_program_id == $prog->id ? 'selected' : '' }}>{{ $prog->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('stages.projector', $stage) }}" target="_blank" class="text-xs font-mono text-[#005c94] hover:underline font-semibold flex items-center gap-1">
                                    <span>Auditorium Projector</span> ↗
                                </a>
                            </div>
                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase hover:brightness-110 shadow-sm">
                                Update Live Status
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-mono">
                    <span class="text-slate-500">{{ $stage->programs_count }} programs allocated</span>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.stages.edit', $stage) }}" class="text-slate-600 hover:text-slate-900 font-semibold">Edit Stage</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
