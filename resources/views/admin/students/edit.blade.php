@extends('layouts.admin', ['title' => 'Edit Participant: ' . $student->name])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.students.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-1.5 block font-semibold">← Back to Participants</a>
            <h1 class="text-2xl sm:text-3xl font-sora font-black text-slate-900">Edit Participant: {{ $student->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ $student->student_id }}</span>
            <span class="px-2.5 py-1 rounded-md text-xs font-bold text-white shadow-2xs" style="background-color: {{ $student->group?->color_hex ?? '#be1e2d' }}">
                {{ $student->group?->name ?? 'No Group' }}
            </span>
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

    <form method="POST" action="{{ route('admin.students.update', $student) }}" 
          x-data="{
              category: '{{ old('category', $student->category) }}',
              classLevel: '{{ old('class_level', $student->class_level ?? '') }}',
              autoDetectedZone: '',
              onClassChange() {
                  const c = this.classLevel.toUpperCase().trim();
                  if (!c) {
                      this.autoDetectedZone = '';
                      return;
                  }
                  if (c.includes('4') || c.includes('TQS') || c.includes('RABIA') || c.includes('THAQASSUS')) {
                      this.category = 'A Zone';
                      this.autoDetectedZone = 'A Zone (Class 4)';
                  } else if (c.includes('3') || c.includes('SALIS')) {
                      this.category = 'B Zone';
                      this.autoDetectedZone = 'B Zone (Class 3)';
                  } else if (c.includes('2') || c.includes('1') || c.includes('ULA') || c.includes('SANI')) {
                      this.category = 'C Zone';
                      this.autoDetectedZone = 'C Zone (Class 1 & 2)';
                  } else {
                      this.autoDetectedZone = '';
                  }
              },
              setClass(cls) {
                  this.classLevel = cls;
                  this.onClassChange();
              },
              init() {
                  if (this.classLevel) this.onClassChange();
              }
          }"
          class="rounded-2xl bg-white border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <!-- Name & Student ID / Chest Number -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="sm:col-span-2">
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Participant Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $student->name) }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Student ID / Chest #</label>
                <input type="text" name="student_id" value="{{ old('student_id', $student->student_id) }}"
                       placeholder="e.g. QUAF-ST-1001"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 font-mono focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>
        </div>

        <!-- Group & Zone -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Group <span class="text-red-500">*</span></label>
                <select name="group_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
                    @foreach($groups as $grp)
                        <option value="{{ $grp->id }}" {{ old('group_id', $student->group_id) == $grp->id ? 'selected' : '' }}>
                            {{ $grp->name }} ({{ $grp->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-mono uppercase text-slate-600 font-bold">Zone <span class="text-red-500">*</span></label>
                    <span x-show="autoDetectedZone" class="text-[10px] font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200" x-text="'Auto: ' + autoDetectedZone"></span>
                </div>
                <select name="category" x-model="category" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
                    @foreach($categories as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Class -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-mono uppercase text-slate-600 font-bold">Class / Year</label>
                <span class="text-[11px] font-mono text-slate-400">4 in class → A Zone | 3 → B Zone | 1 & 2 → C Zone</span>
            </div>
            <input type="text" name="class_level" x-model="classLevel" @input="onClassChange()"
                   placeholder="e.g. NF4, UH3, U1..."
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            
            <!-- Quick Class Select Chips -->
            <div class="mt-2.5 flex flex-wrap items-center gap-1.5 text-[11px] font-mono">
                <span class="text-slate-400 font-semibold mr-1">Quick Select:</span>
                <span class="text-slate-300">|</span>
                <span class="text-red-700 font-bold">A Zone (4):</span>
                @foreach(['NF4', 'UH4', 'S4', 'ID4', 'UT4', 'L4', 'TQS'] as $c4)
                    <button type="button" @click="setClass('{{ $c4 }}')" class="px-2 py-0.5 rounded bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 transition-colors font-bold">{{ $c4 }}</button>
                @endforeach
                <span class="text-slate-300">|</span>
                <span class="text-amber-700 font-bold">B Zone (3):</span>
                @foreach(['NF3', 'ID3', 'UH3', 'UT3', 'S3', 'L3'] as $c3)
                    <button type="button" @click="setClass('{{ $c3 }}')" class="px-2 py-0.5 rounded bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors font-bold">{{ $c3 }}</button>
                @endforeach
                <span class="text-slate-300">|</span>
                <span class="text-sky-700 font-bold">C Zone (1&2):</span>
                @foreach(['U1', 'U2', 'L2'] as $c12)
                    <button type="button" @click="setClass('{{ $c12 }}')" class="px-2 py-0.5 rounded bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 transition-colors font-bold">{{ $c12 }}</button>
                @endforeach
            </div>
        </div>

        <!-- Contact & Photo URL -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Contact Phone Number</label>
                <input type="text" name="contact" value="{{ old('contact', $student->contact) }}"
                       placeholder="+91 ..."
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Photo URL</label>
                <input type="url" name="photo_url" value="{{ old('photo_url', $student->photo_url) }}"
                       placeholder="https://..."
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>
        </div>

        <!-- Current Registered Competitions Preview -->
        @if($student->entries && $student->entries->isNotEmpty())
            <div class="pt-4 border-t border-slate-100">
                <span class="text-xs font-mono uppercase font-bold text-slate-500 block mb-2">Enrolled Competitions ({{ $student->entries->count() }}):</span>
                <div class="flex flex-wrap gap-2">
                    @foreach($student->entries as $entry)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-100 border border-slate-200 text-xs font-medium text-slate-700">
                            <span class="font-mono text-[10px] text-slate-400">#{{ $entry->chest_number }}</span>
                            <span>{{ $entry->program?->name }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="pt-4 flex items-center justify-between border-t border-slate-100">
            <button type="submit" form="delete-form" class="text-xs font-mono text-red-600 hover:text-red-700 hover:underline">
                Delete Participant
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.students.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800 transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#be1e2d] text-white hover:bg-[#a01624] shadow-md shadow-[#be1e2d]/20 transition-all">
                    Update Participant
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Are you sure you want to delete this participant delegate? All assigned event entries will also be removed.');" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
