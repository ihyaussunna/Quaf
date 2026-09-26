@extends('layouts.admin', ['title' => 'Create Festival House'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.groups.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Houses</a>
        <h1 class="text-3xl font-serif font-black text-slate-900">Create House</h1>
    </div>

    <form method="POST" action="{{ route('admin.groups.store') }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">House Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Al-Quds"
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">House Code</label>
                <input type="text" name="code" value="{{ old('code') }}" required placeholder="e.g. ALQ"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Accent Color (Hex)</label>
                <input type="text" name="color_hex" value="{{ old('color_hex', '#10b981') }}" required placeholder="#10b981"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">House Leader / Captain</label>
            <select name="leader_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">-- Select Leader Account --</option>
                @foreach($leaders as $ldr)
                    <option value="{{ $ldr->id }}" {{ old('leader_id') == $ldr->id ? 'selected' : '' }}>{{ $ldr->name }} ({{ $ldr->email }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Emblem / Logo URL</label>
            <input type="url" name="logo_url" value="{{ old('logo_url') }}" placeholder="https://..."
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
            <a href="{{ route('admin.groups.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
            <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                Save House
            </button>
        </div>
    </form>
</div>
@endsection
