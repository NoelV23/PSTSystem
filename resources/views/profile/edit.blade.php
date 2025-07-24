@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10 px-4 ml-0">
    <x-page-header :title="'Profile'" />
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-6 bg-white shadow rounded-lg">
            @include('profile.partials.update-profile-information-form')
        </div>
        <div class="p-6 bg-white shadow rounded-lg">
            @include('profile.partials.update-password-form')
        </div>
    </div>
</div>
@endsection
