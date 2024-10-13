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
