@section('title', __('Edit Genre (:name)', ['name' => $genre->title]))
@extends('dashboard.layout')
@section('content')

  <div class="flex flex-col gap-5">
    <div class="flex flex-col gap-1">
      <div class="flex justify-between">
        <h3 class="text-3xl font-bold tracking-tight">{{ __('Edit Genre (:name)', ['name' => $genre->title]) }}</h3>
        <x-table-button href="{{ route('dashboard.genres.index') }}">
          <x-icons.back />
          <span>{{ __('Back') }}</span>
        </x-table-button>
      </div>
    </div>
    <hr class="border-black/10 dark:border-white/10" />
    <x-error :errors="$errors" />

    <form action="{{ route('dashboard.genres.update', $genre->id) }}" method="POST" class="flex flex-col gap-4">
      @csrf
      @method('PUT')

      <x-input id="title" name="title" :value="old('name', $genre->title)" label="{{ __('Name') }}" placeholder="{{ __('Genre Name') }}" />
      <x-input id="slug" name="slug" :value="old('slug', $genre->slug)" label="{{ __('Slug') }}" placeholder="{{ __('Genre Slug') }}" />
      <x-primary-button>{{ __('Update') }}</x-primary-button>
    </form>
  </div>
@endsection
