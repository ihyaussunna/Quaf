@extends('layouts.admin')

@section('title', 'Programs to Verify - QUAF Fest')

@section('content')
<div class="space-y-6">
    <div class="text-center max-w-xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900">Programs to Verify</h1>
        <p class="text-xs text-gray-500 mt-1">Review, verify and dispatch judged programs to announcer</p>
    </div>

    <!-- Search & Filter Bar -->
    <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center gap-3">
        <form method="GET" action="{{ route('admin.mark-entry.handler') }}" class="flex-1 flex gap-2 w-full">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search program..." class="w-full bg-white border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange shadow-xs">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <select name="status" onchange="this.form.submit()" class="bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange shadow-xs">
                <option value="">All</option>
                <option value="passed" {{ $status === 'passed' ? 'selected' : '' }}>Passed</option>
                <option value="verified" {{ $status === 'verified' ? 'selected' : '' }}>Verified</option>
                <option value="send" {{ $status === 'send' ? 'selected' : '' }}>Delivered / Send</option>
                <option value="announced" {{ $status === 'announced' ? 'selected' : '' }}>Announced</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-brand-orange text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition shadow-xs">
                Filter
            </button>
        </form>
    </div>

    <!-- Program List -->
    <div class="max-w-4xl mx-auto space-y-3">
        @forelse($programs as $program)
            @php
                $status = $program->result?->status ?? 'passed';
            @endphp
            <div class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-gray-200 transition">
                <div class="flex items-center gap-3 flex-wrap">
                    <h3 class="font-bold text-gray-900 text-base">{{ $program->name }}</h3>
                    <span class="text-xs text-gray-500 font-medium">Zone: {{ $program->eligibility ?? 'A Zone' }}</span>

                    @if($status === 'passed')
                        <span class="px-3 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                            passed
                        </span>
                    @elseif($status === 'verified')
                        <span class="px-3 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                            verified
                        </span>
                    @elseif($status === 'send' || $status === 'delivered')
                        <span class="px-3 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                            send
                        </span>
                    @elseif($status === 'announced')
                        <span class="px-3 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-[#005c94]">
                            announced
                        </span>
                    @else
                        <span class="px-3 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                            {{ $status }}
                        </span>
                    @endif
                </div>

                <!-- Action Buttons matching exact Quaf Fest workflow -->
                <div class="flex items-center gap-2 self-end sm:self-auto">
                    <form method="POST" action="{{ route('admin.mark-entry.status-transition', $program->id) }}" class="inline">
                        @csrf
                        @if($status === 'passed')
                            <input type="hidden" name="action" value="verify">
                            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition shadow-xs">
                                Verify
                            </button>
                        @elseif($status === 'verified')
                            <input type="hidden" name="action" value="send">
                            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition shadow-xs">
                                Send
                            </button>
                        @elseif($status === 'send' || $status === 'delivered')
                            <input type="hidden" name="action" value="announced">
                            <button type="submit" class="px-5 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 transition shadow-xs">
                                Delivered
                            </button>
                        @elseif($status === 'announced')
                            <button type="button" disabled class="px-5 py-2 bg-[#005c94] text-white rounded-xl text-xs font-bold opacity-90 cursor-default">
                                Announced
                            </button>
                        @else
                            <input type="hidden" name="action" value="verify">
                            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition shadow-xs">
                                Verify
                            </button>
                        @endif
                    </form>

                    <!-- Reset Button -->
                    <form method="POST" action="{{ route('admin.mark-entry.status-transition', $program->id) }}" class="inline" onsubmit="return confirm('Reset status to passed?')">
                        @csrf
                        <input type="hidden" name="action" value="reset">
                        <button type="submit" class="px-4 py-2 bg-rose-300/80 text-rose-800 rounded-xl text-xs font-bold hover:bg-rose-400 hover:text-white transition">
                            Reset
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400">
                No programs found.
            </div>
        @endforelse
    </div>

    @if($programs->hasPages())
        <div class="max-w-4xl mx-auto pt-4">
            {{ $programs->links() }}
        </div>
    @endif
</div>
@endsection
