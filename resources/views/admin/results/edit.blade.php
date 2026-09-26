@extends('layouts.admin', ['title' => 'Edit Result: ' . $result->program->name])

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="mb-6">
        <a href="{{ route('admin.results.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Results</a>
        <div class="flex items-center gap-2 mb-1">
            <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-amber-50 text-[#f3bd2e] border border-[#f3bd2e]/30">{{ $result->program->code }}</span>
            <span class="text-xs font-mono text-slate-500">{{ $result->program->eligibility ?? 'A Zone' }} • {{ ucfirst($result->program->type) }}</span>
        </div>
        <h1 class="text-3xl font-serif font-black text-slate-900">Edit Verdict: {{ $result->program->name }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.results.update', $result) }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-mono uppercase text-[#f3bd2e] mb-1.5 font-bold">1st Place (Champion)</label>
            <select name="first_entry_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                @foreach($result->program->entries as $entry)
                    <option value="{{ $entry->id }}" {{ old('first_entry_id', $result->first_entry_id) == $entry->id ? 'selected' : '' }}>
                        Chest #{{ $entry->chest_number }} — {{ $entry->student?->name ?? 'House Team' }} ({{ $entry->group->name }})
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
                        Chest #{{ $entry->chest_number }} — {{ $entry->student?->name ?? 'House Team' }} ({{ $entry->group->name }})
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
                        Chest #{{ $entry->chest_number }} — {{ $entry->student?->name ?? 'House Team' }} ({{ $entry->group->name }})
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
