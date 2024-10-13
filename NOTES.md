# Notes

## Preparing Chirps

Create new project:

```bash
laravel new chirps
```

> Choose: Laravel Breeze, Blade with Alpine, Dark Mode, Pest, Init Git - Yes
> Choose: MySQL, Yes, Yes

## Preparing Data

```bash
cd chirps/
php artisan make:model -mcrf Chirp
```

Update Chirt migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chirps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('message');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chirps');
    }
};
```

Then run:

```bash
php artisan migrate
```

Update ChirpFactory:

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chirp>
 */
class ChirpFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(),
            'message' => fake()->sentence(),
        ];
    }
}
```

Create DevSeeder:

```bash
php artisan make:seeder DevSeeder
```

Update DevSeeder:

```php
<?php

namespace Database\Seeders;

use App\Models\Chirp;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(app()->isProduction()) {
            return;
        }

        Chirp::factory(100)->create();
    }
}
```

Now seed some dummy data:

```bash
php artisan db:seed --class=DevSeeder
```

## Setup Route Resource

Open up `routes/web.`, update as following:

```php
<?php

// import Chirp Controller
use App\Http\Controllers\ChirpController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Chirps Route
    Route::resource('chirps', ChirpController::class);
});

require __DIR__.'/auth.php';
```

Confirm the route exists by run:

```bash
php artisan route:list
```

OR

```bash
php artisan route:list --path=chirps
```

## Setup: Chirp Listing Page

In `app/Http/Controllers/ChirpController.php` at `index()` method, update as following:

```php
public function index()
{
    // 1. query from database all the chirps record
    $chirps = Chirp::get(); // select * from chirps

    // 2. pass data to view for rendering
    // 3. then return respnose from rendered view
    return view('chirps.index', compact('chirps'));

    // return view('chirps.index', ['tweets' => $chirps]);
    // return view('chirps.index')->with('tweets', $chirps);
}
```

Add the model namespace as well at the top of the class:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;
```

Then create new view for chirp's index page:

```bash
php artisan make:view chirps.index
```

Then update the `resources/views/chirps/index.blade.php`:

```php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Chirps') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @foreach ($chirps as $chirp)
                        <li>{{ $chirp->message }}</li>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

Now you can register & login the app, and go to URL: `<your-domain>/chirps`.

> Add Navigation menu as in <https://bootcamp.laravel.com/blade/creating-chirps#navigation-menu>.

## Setup: Using Pagination

Update the query in ChirpController:

```php
$chirps = Chirp::get();
```

To use paginate:

```php
$chirps = Chirp::paginate();
```

Then in `resources/views/chirps/index.blade.php`, add the following at the top & bottom of list.

```php
{{ $chirps->links() }}
<div class="p-6">
    {{ $chirps->links() }}
    @foreach ($chirps as $chirp)
        <li>{{ $chirp->message }}</li>
    @endforeach
    {{ $chirps->links() }}
</div>
```

## Finish up CRUD

Update `app/Models/Chirp.php` by adding relationship to `app/Models/User.php`:

```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

Then update the `app/Http/Controllers/ChirpController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. query from database all the chirps record
        $chirps = Chirp::latest()->paginate(); // select * from chirps limi 0, 10

        // 2. pass data to view for rendering
        // 3. then return respnose from rendered view
        return view('chirps.index', compact('chirps'));

        // return view('chirps.index', ['tweets' => $chirps]);
        // return view('chirps.index')->with('tweets', $chirps);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // validate our form input
        $validated = $request->validate([
            'message' => 'required|string|max:250',
            'message' => [
                'required',
                'string',
                'max:250'
            ],
        ]);

        // store in database through relationship
        $request->user()->chirps()->create($validated);

        // store in database without relationship
        // Chirp::create([
        //     'user_id' => auth()->user()->id,
        //     'message' => $request->message,
        // ]);

        // flash message

        // redirect
        return redirect(route('chirps.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chirp $chirp)
    {
        Gate::authorize('update', $chirp);

        return view('chirps.edit', [
            'chirp' => $chirp,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chirp $chirp)
    {
        Gate::authorize('update', $chirp);

        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $chirp->update($validated);

        return redirect(route('chirps.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chirp $chirp)
    {
        Gate::authorize('delete', $chirp);

        $chirp->delete();

        return redirect(route('chirps.index'));
    }
}
```

Then update the `resources/views/chirps/index.blade.php`:

```php
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
            <textarea name="message" placeholder="{{ __('What\'s on your mind?') }}"
                class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">{{ old('message') }}</textarea>
            {{-- 4. Validation Message --}}
            <x-input-error :messages="$errors->get('message')" class="mt-2" />
            <x-primary-button class="mt-4">{{ __('Chirp') }}</x-primary-button>
        </form>


        <div class="mt-6 bg-white shadow-sm rounded-lg divide-y p-4 ">
            <div class="my-2">{{ $chirps->links() }}</div>
            @foreach ($chirps as $chirp)
                <div class="p-6 flex space-x-2 text-wrap my-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 -scale-x-100" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <div class="flex-1">
                        <div class="flex justify-between items-center text-wrap">
                            <div>
                                <span class="text-gray-800">{{ $chirp->user->name }}</span>
                                <small
                                    class="ml-2 text-sm text-gray-600">{{ $chirp->created_at->format('j M Y, g:i a') }}</small>
                                @unless ($chirp->created_at->eq($chirp->updated_at))
                                    <small class="text-sm text-gray-600"> &middot; {{ __('edited') }}</small>
                                @endunless
                            </div>
                            @if ($chirp->user->is(auth()->user()))
                                <x-dropdown>
                                    <x-slot name="trigger">
                                        <button>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                            </svg>
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-dropdown-link :href="route('chirps.edit', $chirp)">
                                            {{ __('Edit') }}
                                        </x-dropdown-link>
                                        <form method="POST" action="{{ route('chirps.destroy', $chirp) }}">
                                            @csrf
                                            @method('delete')
                                            <x-dropdown-link :href="route('chirps.destroy', $chirp)" onclick=" event.preventDefault(); if(confirm('Are you sure?')) { this.closest('form').submit(); }">
                                                {{ __('Delete') }}
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            @endif
                        </div>
                        <p class="mt-4 text-lg text-gray-900">{{ $chirp->message }}</p>
                    </div>
                </div>
            @endforeach
            <div class="my-2">{{ $chirps->links() }}</div>
        </div>

    </div>

</x-app-layout>
```

Then add new `resources/views/chirps/edit.blade.php` and add the following content:

```php
<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('chirps.update', $chirp) }}">
            @csrf
            @method('patch')
            <textarea
                name="message"
                class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
            >{{ old('message', $chirp->message) }}</textarea>
            <x-input-error :messages="$errors->get('message')" class="mt-2" />
            <div class="mt-4 space-x-2">
                <x-primary-button>{{ __('Save') }}</x-primary-button>
                <a href="{{ route('chirps.index') }}">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</x-app-layout>
```

Then lastly, create policy for the `app/Models/Chirp.php`:

```bash
php artisan make:policy ChirpPolicy --model=Chirp
```

Then update then `app/Policies/ChirpPolicy.php` as following:

```php
<?php

namespace App\Policies;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChirpPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Chirp $chirp): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Chirp $chirp): bool
    {
        return $chirp->user()->is($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Chirp $chirp): bool
    {
        return $this->update($user, $chirp);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Chirp $chirp): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Chirp $chirp): bool
    {
        return false;
    }
}
```

Now you should be able to have complete CRUD pages for Chirp.
