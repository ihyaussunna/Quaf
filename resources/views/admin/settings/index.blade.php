@extends('layouts.admin', ['title' => 'Festival Settings & Live Mode'])

@section('content')
<div class="space-y-6 max-w-4xl">
    <div>
        <h1 class="text-3xl font-serif font-black text-slate-900">Festival Settings</h1>
        <p class="text-xs font-mono text-slate-500 mt-1">Configure global festival behavior, Live Fest Mode, registration deadlines, and branding metadata.</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf

        <!-- Live Fest Mode Card -->
        <div class="rounded-3xl bg-white border-2 border-[#f3bd2e]/40 p-6 sm:p-8 relative overflow-hidden shadow-sm">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-amber-50 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h2 class="text-lg font-serif font-bold text-slate-900">Live Fest Mode</h2>
                    </div>
                    <p class="text-xs font-sans text-slate-600 max-w-xl leading-relaxed">
                        When enabled, the public homepage transforms into an active Festival Live Center featuring real-time stage progress monitors, breaking result ticker, and live group rankings.
                    </p>
                </div>
                <div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="live_fest_mode" value="0">
                        <input type="checkbox" name="live_fest_mode" value="1" {{ ($settings['live_fest_mode'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#f3bd2e]"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Registration Status Card & Window -->
        @php
            $isOpen = ($settings['registration_open'] ?? '1') == '1';
        @endphp
        <div class="rounded-3xl bg-white border-2 {{ $isOpen ? 'border-emerald-500/40' : 'border-rose-500/40' }} p-6 sm:p-8 space-y-6 shadow-sm relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full {{ $isOpen ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                        <h2 class="text-lg font-serif font-bold text-slate-900">Group Entry Registration Portal</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold uppercase {{ $isOpen ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $isOpen ? 'OPEN' : 'CLOSED' }}
                        </span>
                    </div>
                    <p class="text-xs font-sans text-slate-500 max-w-xl">
                        Allow Group Leaders to submit participant entries and register students for upcoming programs.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('admin.settings.toggle-registration') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold font-mono uppercase tracking-wider transition-all shadow-sm {{ $isOpen ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                            {{ $isOpen ? 'Close Registration Now' : 'Open Registration Now' }}
                        </button>
                    </form>
                    <input type="hidden" name="registration_open" value="{{ $isOpen ? '1' : '0' }}">
                </div>
            </div>

            <!-- Registration Window (Start & End Times) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-mono text-slate-600 mb-1 uppercase tracking-wider font-semibold">
                        Registration Window Opens (Start Time)
                    </label>
                    <input type="datetime-local" name="registration_start" value="{{ $settings['registration_start'] ?? '' }}"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <span class="text-[10px] font-mono text-slate-400 block mt-1">Leave blank to allow registration immediately</span>
                </div>
                <div>
                    <label class="block text-xs font-mono text-slate-600 mb-1 uppercase tracking-wider font-semibold">
                        Registration Window Closes (Deadline)
                    </label>
                    <input type="datetime-local" name="registration_end" value="{{ $settings['registration_end'] ?? '' }}"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <span class="text-[10px] font-mono text-slate-400 block mt-1">Registration automatically closes after this time</span>
                </div>
            </div>
        </div>

        <!-- General Metadata -->
        <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm">
            <h2 class="text-lg font-serif font-bold text-slate-900 border-b border-slate-100 pb-4">Conclave Branding & Organization</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-mono text-slate-600 mb-2 uppercase tracking-wider font-semibold">Festival Name</label>
                    <input type="text" name="festival_name" value="{{ $settings['festival_name'] ?? 'QUAF' }}" required
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-mono text-slate-600 mb-2 uppercase tracking-wider font-semibold">Scheduled Dates</label>
                    <input type="text" name="festival_dates" value="{{ $settings['festival_dates'] ?? 'October 24 - 28, 2026' }}" required
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-mono text-slate-600 mb-2 uppercase tracking-wider font-semibold">Festival Tagline</label>
                    <input type="text" name="tagline" value="{{ $settings['tagline'] ?? 'The Grand Cultural Conclave of Talents' }}" required
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-mono text-slate-600 mb-2 uppercase tracking-wider font-semibold">Organizing Body</label>
                    <input type="text" name="organizer" value="{{ $settings['organizer'] ?? 'Ihyaussunna Students Union, Markazu Saquafathi Sunniyya' }}" required
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                </div>
            </div>

            <div class="pt-4 flex justify-end border-t border-slate-100">
                <button type="submit" class="px-8 py-3 bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase rounded-xl hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                    Save Global Settings
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
