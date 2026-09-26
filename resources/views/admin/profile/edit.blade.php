@extends('layouts.admin')

@section('title', 'Edit Admin Profile - QUAF Fest')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-3xl border border-gray-100 shadow-lg p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Admin Profile</h1>
            <p class="text-xs text-gray-500 mt-1">Update your administrative credentials</p>
        </div>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Name / Username</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 font-medium focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                @error('name')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">New Password (leave blank to keep current)</label>
                <input type="password" name="password" placeholder="••••••••" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 font-medium focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                @error('password')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3.5 bg-brand-orange text-white rounded-2xl font-bold text-sm hover:bg-orange-600 transition shadow-sm">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
