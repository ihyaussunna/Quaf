@extends('layouts.admin', ['title' => 'Edit Judge: ' . $judge->name])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.judges.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Judges</a>
        <h1 class="text-3xl font-serif font-black text-slate-900">Edit Judge: {{ $judge->name }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.judges.update', $judge) }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Judge Full Name</label>
            <input type="text" name="name" value="{{ old('name', $judge->name) }}" required
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Designation / Title</label>
                <input type="text" name="designation" value="{{ old('designation', $judge->designation) }}"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Specialization</label>
                <input type="text" name="specialization" value="{{ old('specialization', $judge->specialization) }}"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Contact Number</label>
                <input type="text" name="contact" value="{{ old('contact', $judge->contact) }}"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-mono uppercase text-slate-600 font-bold">Access PIN</label>
                    <button type="button" onclick="document.getElementById('accessCodeInput').value = Math.floor(1000 + Math.random() * 9000);" class="text-[11px] text-[#be1e2d] hover:underline font-bold">
                        Generate Tough PIN
                    </button>
                </div>
                <input type="text" id="accessCodeInput" name="access_code" maxlength="6" value="{{ old('access_code', $judge->access_code) }}" placeholder="e.g. 8429"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 font-mono font-bold tracking-widest focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Assigned Programs</label>
            <div class="max-h-56 overflow-y-auto space-y-2 p-3 rounded-xl bg-slate-50 border border-slate-300 text-xs font-mono">
                @php $assignedIds = $judge->programs->pluck('id')->toArray(); @endphp
                @foreach($programs as $prog)
                    <label class="flex items-center gap-2 text-slate-700 hover:text-slate-900 cursor-pointer">
                        <input type="checkbox" name="program_ids[]" value="{{ $prog->id }}"
                               {{ in_array($prog->id, $assignedIds) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-[#f3bd2e] focus:ring-0">
                        <span>[{{ $prog->code }}] {{ $prog->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="pt-4 flex items-center justify-between border-t border-slate-100">
            <button type="submit" form="delete-form" class="text-xs font-mono text-red-600 hover:underline">
                Delete Judge
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.judges.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
                <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                    Update Judge
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" method="POST" action="{{ route('admin.judges.destroy', $judge) }}" onsubmit="return confirm('Delete judge and associated user login?');" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
