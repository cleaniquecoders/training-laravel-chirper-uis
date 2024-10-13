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

