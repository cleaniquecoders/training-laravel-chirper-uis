<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Chirps') }}
        </h2>
    </x-slot>


    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">



        {{-- 1. Method = POST --}}
        {{-- 2. Route - use route() helper --}}
        <form method="POST" action="{{ route('chirps.store') }}">
            {{-- 3. CSRF --}}
            @csrf
            <textarea
                name="message"
                placeholder="{{ __('What\'s on your mind?') }}"
                class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
            >{{ old('message') }}</textarea>
            {{-- 4. Validation Message --}}
            <x-input-error :messages="$errors->get('message')" class="mt-2" />
            <x-primary-button class="mt-4">{{ __('Chirp') }}</x-primary-button>
        </form>




    </div>







    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{ $chirps->links() }}
                    @foreach ($chirps as $chirp)
                        <li>{{ $chirp->message }}</li>
                    @endforeach
                    {{ $chirps->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
