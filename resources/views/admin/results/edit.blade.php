@extends('layouts.admin', ['title' => 'Edit Result: ' . $result->program->name])

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.results.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">&larr; Back to Results</a>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-amber-50 text-[#f3bd2e] border border-[#f3bd2e]/30">{{ $result->program->code }}</span>
                <span class="text-xs font-mono text-slate-500">{{ $result->program->eligibility ?? 'A Zone' }} &bull; {{ ucfirst($result->program->type) }}</span>
            </div>
            <h1 class="text-3xl font-sora font-black text-slate-900">Edit Verdict: {{ $result->program->name }}</h1>
        </div>

        @if(!empty($podium['first']))
            <form method="POST" action="{{ route('admin.results.auto-determine', $result->program) }}">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-mono font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-xs">
                    Auto-Fill from Judge Scores
                </button>
            </form>
        @endif
    </div>

    @if(!empty($podium['first']))
        <!-- Judge Scores Auto-Podium Reference -->
        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5 space-y-2">
            <span class="text-[10px] font-mono uppercase tracking-wider font-bold text-slate-500">Judge Scores Ranking:</span>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs font-mono">
                <div class="p-3 rounded-xl bg-white border border-amber-200">
                    <span class="text-[10px] font-bold text-amber-700 uppercase">1st: </span>
                    <span class="font-bold text-slate-900">{{ $podium['first']->student?->name ?? $podium['first']->group?->name }}</span>
                    <div class="text-[11px] text-slate-500">{{ $podium['first']->computed_avg_score }} pts &bull; Grade {{ $podium['first']->computed_grade ?? 'None' }}</div>
                </div>
                @if(!empty($podium['second']))
                    <div class="p-3 rounded-xl bg-white border border-slate-200">
                        <span class="text-[10px] font-bold text-slate-700 uppercase">2nd: </span>
                        <span class="font-bold text-slate-900">{{ $podium['second']->student?->name ?? $podium['second']->group?->name }}</span>
                        <div class="text-[11px] text-slate-500">{{ $podium['second']->computed_avg_score }} pts &bull; Grade {{ $podium['second']->computed_grade ?? 'None' }}</div>
                    </div>
                @endif
                @if(!empty($podium['third']))
                    <div class="p-3 rounded-xl bg-white border border-slate-200">
                        <span class="text-[10px] font-bold text-amber-900 uppercase">3rd: </span>
                        <span class="font-bold text-slate-900">{{ $podium['third']->student?->name ?? $podium['third']->group?->name }}</span>
                        <div class="text-[11px] text-slate-500">{{ $podium['third']->computed_avg_score }} pts &bull; Grade {{ $podium['third']->computed_grade ?? 'None' }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.results.update', $result) }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-mono uppercase text-[#f3bd2e] mb-1.5 font-bold">1st Place (Champion)</label>
            <select name="first_entry_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                @foreach($result->program->entries as $entry)
                    <option value="{{ $entry->id }}" {{ old('first_entry_id', $result->first_entry_id) == $entry->id ? 'selected' : '' }}>
                        Chest #{{ $entry->chest_number }} &mdash; {{ $entry->student?->name ?? 'Group Team' }} ({{ $entry->group?->name }})
                        @if(!empty($podium['first']) && $podium['first']->id === $entry->id)
                            [AUTO 1ST - {{ $podium['first']->computed_avg_score }} pts]
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-700 mb-1.5 font-bold">2nd Place</label>
            <select name="second_entry_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">-- None --</option>
                @foreach($result->program->entries as $entry)
                    <option value="{{ $entry->id }}" {{ old('second_entry_id', $result->second_entry_id) == $entry->id ? 'selected' : '' }}>
                        Chest #{{ $entry->chest_number }} &mdash; {{ $entry->student?->name ?? 'Group Team' }} ({{ $entry->group?->name }})
                        @if(!empty($podium['second']) && $podium['second']->id === $entry->id)
                            [AUTO 2ND - {{ $podium['second']->computed_avg_score }} pts]
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-amber-700 mb-1.5 font-bold">3rd Place</label>
            <select name="third_entry_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">-- None --</option>
                @foreach($result->program->entries as $entry)
                    <option value="{{ $entry->id }}" {{ old('third_entry_id', $result->third_entry_id) == $entry->id ? 'selected' : '' }}>
                        Chest #{{ $entry->chest_number }} &mdash; {{ $entry->student?->name ?? 'Group Team' }} ({{ $entry->group?->name }})
                        @if(!empty($podium['third']) && $podium['third']->id === $entry->id)
                            [AUTO 3RD - {{ $podium['third']->computed_avg_score }} pts]
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Workflow Status</label>
            <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="published" {{ old('status', $result->status) == 'published' ? 'selected' : '' }}>Published (Live on Public Portal + Auto Certificates + Calculate Points)</option>
                <option value="verified" {{ old('status', $result->status) == 'verified' ? 'selected' : '' }}>Verified</option>
                <option value="under_review" {{ old('status', $result->status) == 'under_review' ? 'selected' : '' }}>Under Review</option>
                <option value="draft" {{ old('status', $result->status) == 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Jury Remarks / Official Notes</label>
            <textarea name="remarks" rows="2" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">{{ old('remarks', $result->remarks) }}</textarea>
        </div>

        <div class="pt-4 flex items-center justify-between border-t border-slate-100">
            <button type="submit" form="delete-form" class="text-xs font-mono text-red-600 hover:underline">
                Delete Verdict
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.results.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
                <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                    Update Verdict
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" method="POST" action="{{ route('admin.results.destroy', $result) }}" onsubmit="return confirm('Delete this result? Points will be recalculated.');" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
