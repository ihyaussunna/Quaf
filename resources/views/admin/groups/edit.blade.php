@extends('layouts.admin', ['title' => 'Edit House: ' . $group->name])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.groups.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-1.5 block font-semibold">← Back to Houses</a>
            <h1 class="text-2xl sm:text-3xl font-serif font-black text-slate-900">Edit House: {{ $group->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full border border-slate-300 shadow-xs" style="background-color: {{ $group->color_hex ?? '#be1e2d' }}"></span>
            <span class="text-xs font-mono font-bold text-slate-700">{{ $group->code }}</span>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs space-y-1">
            <p class="font-bold">Please correct the following errors:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.groups.update', $group) }}" class="rounded-2xl bg-white border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm" x-data="{ color: '{{ old('color_hex', $group->color_hex ?? '#be1e2d') }}' }">
        @csrf
        @method('PUT')

        <!-- Basic House Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">House / Team Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $group->name) }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>

            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">House Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $group->code) }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 uppercase font-mono focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>
        </div>

        <!-- Color Palette -->
        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-2 font-bold">Official House Theme Color <span class="text-red-500">*</span></label>
            <div class="flex flex-wrap items-center gap-3">
                <input type="color" name="color_hex" x-model="color" class="w-12 h-10 rounded-xl cursor-pointer border border-slate-300 p-0.5 bg-slate-50">
                <input type="text" x-model="color" class="w-28 bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-mono uppercase text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white">
                
                <!-- Quick Preset Palette Buttons -->
                <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                    <button type="button" @click="color = '#be1e2d'" class="px-2.5 py-1 text-[11px] font-mono rounded-lg border text-white font-bold" style="background-color: #be1e2d;">PACTO (Red)</button>
                    <button type="button" @click="color = '#f3bd2e'" class="px-2.5 py-1 text-[11px] font-mono rounded-lg border text-slate-900 font-bold" style="background-color: #f3bd2e;">YUGO (Gold)</button>
                    <button type="button" @click="color = '#005c94'" class="px-2.5 py-1 text-[11px] font-mono rounded-lg border text-white font-bold" style="background-color: #005c94;">CONCO (Blue)</button>
                    <button type="button" @click="color = '#009444'" class="px-2.5 py-1 text-[11px] font-mono rounded-lg border text-white font-bold" style="background-color: #009444;">LUMO (Green)</button>
                    <button type="button" @click="color = '#0f172a'" class="px-2.5 py-1 text-[11px] font-mono rounded-lg border text-white font-bold" style="background-color: #0f172a;">UNIO (Black)</button>
                </div>
            </div>
        </div>

        <!-- Leader / Captain -->
        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">House Captain / Leader Account</label>
            <select name="leader_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
                <option value="">-- No Specific Leader Assigned --</option>
                @foreach($leaders as $ldr)
                    <option value="{{ $ldr->id }}" {{ old('leader_id', $group->leader_id) == $ldr->id ? 'selected' : '' }}>
                        {{ $ldr->name }} ({{ $ldr->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Manager Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-slate-100">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Manager Name</label>
                <input type="text" name="manager_name" value="{{ old('manager_name', $group->manager_name) }}"
                       placeholder="e.g. Master / Coordinator"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>

            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Manager Contact Phone</label>
                <input type="text" name="manager_contact" value="{{ old('manager_contact', $group->manager_contact) }}"
                       placeholder="e.g. +91 98470 00000"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>
        </div>

        <!-- Official Display Names in Reports / Certificates -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Name in Results / Reports</label>
                <input type="text" name="name_in_results" value="{{ old('name_in_results', $group->name_in_results) }}"
                       placeholder="Leave empty to use House Name"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>

            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Name in Certificates</label>
                <input type="text" name="name_in_certificates" value="{{ old('name_in_certificates', $group->name_in_certificates) }}"
                       placeholder="Leave empty to use House Name"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>
        </div>

        <!-- Emblem / Logo URL -->
        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Emblem / Logo URL</label>
            <input type="url" name="logo_url" value="{{ old('logo_url', $group->logo_url) }}"
                   placeholder="https://..."
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 flex items-center justify-between border-t border-slate-100">
            <button type="submit" form="delete-form" class="text-xs font-mono text-red-600 hover:text-red-700 hover:underline">
                Delete House
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.groups.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800 transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#be1e2d] text-white hover:bg-[#a01624] shadow-md shadow-[#be1e2d]/20 transition-all">
                    Update House
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" method="POST" action="{{ route('admin.groups.destroy', $group) }}" onsubmit="return confirm('Are you sure you want to delete this house? This will impact all assigned students and entries.');" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
