@extends('layouts.student', ['title' => 'Student Dashboard'])

@section('content')
<div class="space-y-8">
    <!-- Student Profile Hero (Light Theme) -->
    <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden shadow-sm">
        <div class="flex items-center gap-6 relative z-10">
            <div class="w-20 h-20 rounded-2xl bg-slate-100 border-2 border-[#f3bd2e]/60 flex items-center justify-center font-sora text-3xl font-bold text-[#f3bd2e] overflow-hidden flex-shrink-0 shadow-md">
                @if($student->photo_path)
                    <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                @else
                    {{ substr($student->name, 0, 1) }}
                @endif
            </div>

            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold" style="background-color: {{ $student->group->color_hex }}20; color: {{ $student->group->color_hex }}">
                        {{ $student->group->name }}
                    </span>
                    <span class="text-[10px] font-mono text-slate-500">• {{ $student->zone_name }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-sora font-black text-slate-900">{{ $student->name }}</h1>
                <p class="text-xs font-mono text-slate-500">
                    Chest #: <strong class="text-[#f3bd2e] font-bold">{{ $student->chest_number ?? '---' }}</strong> • Student ID: {{ $student->student_id }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 relative z-10">
            <a href="{{ route('student.idcard') }}" class="px-5 py-2.5 bg-[#f3bd2e] hover:bg-amber-500 text-white font-mono font-bold text-xs uppercase rounded-xl flex items-center gap-2 shadow-md shadow-[#f3bd2e]/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                <span>View QR Badge</span>
            </a>
        </div>
    </div>

    <!-- Official Individual Programme Quota Meter (5 Max) -->
    <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ ($individualCount ?? 0) >= 5 ? 'bg-rose-500' : 'bg-emerald-500 animate-pulse' }}"></span>
                <h3 class="text-sm font-mono font-bold uppercase tracking-wider text-slate-900">Individual Programme Quota</h3>
            </div>
            <p class="text-xs text-slate-500 font-sora">
                Each student can participate in a maximum of 5 individual programmes (Own Zone + Mix Zone).
            </p>
        </div>
        <div class="flex items-center gap-5 flex-shrink-0 bg-slate-50 border border-slate-200/80 px-6 py-3 rounded-2xl">
            <div class="text-right">
                <div class="text-2xl font-sora font-black">
                    <span class="{{ ($individualCount ?? 0) >= 5 ? 'text-[#be1e2d]' : 'text-emerald-700' }}">{{ $individualCount ?? 0 }}</span>
                    <span class="text-slate-400 text-lg">/ {{ $maxSlots ?? 5 }}</span>
                </div>
                <span class="text-[10px] font-mono font-bold uppercase text-slate-500">Used Slots</span>
            </div>
            <div class="h-8 w-[1px] bg-slate-300"></div>
            <div class="text-left">
                <div class="text-2xl font-sora font-black {{ ($remainingSlots ?? 0) > 0 ? 'text-[#f3bd2e]' : 'text-slate-400' }}">
                    {{ $remainingSlots ?? 5 }}
                </div>
                <span class="text-[10px] font-mono font-bold uppercase text-slate-500">Remaining</span>
            </div>
        </div>
    </div>

    <!-- PROMINENT NEXT PROGRAM COUNTDOWN WIDGET (Light Theme) -->
    <div class="rounded-3xl bg-gradient-to-br from-amber-50/80 via-white to-amber-50/40 border-2 border-[#f3bd2e]/40 p-6 sm:p-8 relative overflow-hidden shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 border border-amber-300 text-[#be1e2d] text-xs font-mono font-bold">
                    <span class="w-2 h-2 rounded-full bg-[#f3bd2e] animate-ping"></span>
                    <span>NEXT PROGRAM STAGE SLOT</span>
                </div>

                @if($nextEntry)
                    <h2 class="text-2xl sm:text-3xl font-sora font-black text-slate-900 tracking-wide">
                        {{ $nextEntry->program->name }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-4 text-xs font-mono text-slate-600">
                        <span>Venue: <strong class="text-slate-900">{{ $nextEntry->program->stage->name ?? 'TBA' }}</strong></span>
                        <span>•</span>
                        <span>Scheduled: <strong class="text-[#f3bd2e]">{{ $nextEntry->program->scheduled_time?->format('M d, h:i A') }}</strong></span>
                        <span>•</span>
                        <span>Chest: <strong class="text-slate-900">#{{ $nextEntry->chest_number }}</strong></span>
                    </div>
                @else
                    <h2 class="text-xl font-sora font-bold text-slate-700">
                        No upcoming programs scheduled at this time.
                    </h2>
                    <p class="text-xs font-mono text-slate-500">
                        Check back once the next stage schedules are announced by the festival desk.
                    </p>
                @endif
            </div>

            @if($nextEntry && $nextEntry->program->scheduled_time)
                <!-- Live Countdown Timer (Light Theme) -->
                <div x-data="{
                    target: new Date('{{ $nextEntry->program->scheduled_time->toIso8601String() }}').getTime(),
                    now: new Date().getTime(),
                    days: 0, hours: 0, minutes: 0, seconds: 0,
                    init() {
                        this.update();
                        setInterval(() => { this.now = new Date().getTime(); this.update(); }, 1000);
                    },
                    update() {
                        let diff = this.target - this.now;
                        if (diff <= 0) {
                            this.days = 0; this.hours = 0; this.minutes = 0; this.seconds = 0;
                            return;
                        }
                        this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                        this.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                        this.seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    }
                }" class="flex items-center gap-2 sm:gap-3 font-mono">
                    <div class="bg-white border border-amber-200 rounded-2xl p-3 text-center min-w-[64px] shadow-xs">
                        <span class="text-2xl font-bold text-[#f3bd2e]" x-text="days">0</span>
                        <span class="text-[9px] text-slate-500 block uppercase mt-0.5 font-semibold">Days</span>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl p-3 text-center min-w-[64px] shadow-xs">
                        <span class="text-2xl font-bold text-slate-900" x-text="hours">0</span>
                        <span class="text-[9px] text-slate-500 block uppercase mt-0.5 font-semibold">Hours</span>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl p-3 text-center min-w-[64px] shadow-xs">
                        <span class="text-2xl font-bold text-slate-900" x-text="minutes">0</span>
                        <span class="text-[9px] text-slate-500 block uppercase mt-0.5 font-semibold">Mins</span>
                    </div>
                    <div class="bg-white border border-amber-200 rounded-2xl p-3 text-center min-w-[64px] shadow-xs">
                        <span class="text-2xl font-bold text-amber-600" x-text="seconds">0</span>
                        <span class="text-[9px] text-slate-500 block uppercase mt-0.5 font-semibold">Secs</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Enrolled Programs Grid (Light Theme) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-sora font-bold text-slate-900">My Enrolled Programs</h2>
            <span class="text-xs font-mono text-slate-500 font-semibold">Total: {{ $myPrograms->count() }} Events</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($myPrograms as $entry)
                <div class="bg-white border border-slate-200 rounded-3xl p-6 space-y-4 hover:border-[#f3bd2e]/40 transition-all shadow-sm hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase">
                            {{ $entry->program->zone?->name ?? $entry->program->eligibility ?? 'All Zones' }} • {{ ucfirst($entry->program->type) }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono uppercase font-bold
                            {{ $entry->status === 'verified' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                            {{ $entry->status }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-xl font-sora font-bold text-slate-900">{{ $entry->program->name }}</h3>
                        <p class="text-xs font-mono text-slate-500 mt-1">Program Code: {{ $entry->program->code }} • Chest #{{ $entry->chest_number }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 grid grid-cols-2 gap-3 text-xs font-mono">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Stage Venue</span>
                            <span class="text-slate-800 font-semibold">{{ $entry->program->stage->name ?? 'TBA' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Schedule Slot</span>
                            <span class="text-slate-800 font-semibold">{{ $entry->program->scheduled_time?->format('M d, h:i A') ?? 'TBA' }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-500 font-mono text-xs bg-white rounded-3xl border border-slate-200 shadow-sm">
                    You have not been enrolled in any programs yet. Contact your Group Leader.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Announcements -->
    @if($announcements->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-3xl p-6 space-y-4 shadow-sm">
            <h3 class="text-lg font-sora font-bold text-slate-900">Festival Announcements</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($announcements as $ann)
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-sora font-bold text-slate-900">{{ $ann->title }}</h4>
                            <span class="text-[10px] font-mono text-slate-400">{{ $ann->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs font-sora text-slate-700">{{ $ann->content }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
