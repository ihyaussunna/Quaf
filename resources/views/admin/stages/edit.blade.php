@extends('layouts.admin', ['title' => 'Edit Stage: ' . $stage->name])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.stages.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Stages</a>
        <h1 class="text-3xl font-serif font-black text-slate-900">Edit Stage: {{ $stage->name }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.stages.update', $stage) }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Stage Name</label>
            <input type="text" name="name" value="{{ old('name', $stage->name) }}" required
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Stage Code</label>
                <input type="text" name="code" value="{{ old('code', $stage->code) }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Seating Capacity</label>
                <input type="number" name="capacity" value="{{ old('capacity', $stage->capacity) }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Location / Description</label>
            <input type="text" name="location" value="{{ old('location', $stage->location) }}"
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Status</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="active" {{ old('status', $stage->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="break" {{ old('status', $stage->status) == 'break' ? 'selected' : '' }}>Break</option>
                    <option value="closed" {{ old('status', $stage->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Current Program</label>
                <select name="current_program_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="">None</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ old('current_program_id', $stage->current_program_id) == $prog->id ? 'selected' : '' }}>{{ $prog->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Next Program</label>
                <select name="next_program_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="">None</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ old('next_program_id', $stage->next_program_id) == $prog->id ? 'selected' : '' }}>{{ $prog->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-between border-t border-slate-100">
            <button type="submit" form="delete-form" class="text-xs font-mono text-red-600 hover:underline">
                Delete Stage
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.stages.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
                <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                    Update Stage
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" method="POST" action="{{ route('admin.stages.destroy', $stage) }}" onsubmit="return confirm('Are you sure you want to delete this stage?');" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
