@extends('layouts.app')

@section('title', 'Perfil')

@section('content')
<div class="bg-gray-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200 min-h-screen">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-5">

        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm p-5 sm:p-8">
            <div class="w-full">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm p-5 sm:p-8">
            <div class="w-full">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm p-5 sm:p-8">
            <div class="w-full">
                @include('profile.partials.delete-user-form')
            </div>
        </div>

    </div>
    
</div>
@endsection