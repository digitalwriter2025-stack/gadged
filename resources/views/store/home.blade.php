{{-- Every block comes from Content → Home sections in the admin panel, in its sort order. --}}
<x-layouts.store>
    @foreach ($sections as $section)
        @include('store.home.'.str_replace('_', '-', $section['type']), ['section' => $section])
    @endforeach
</x-layouts.store>
