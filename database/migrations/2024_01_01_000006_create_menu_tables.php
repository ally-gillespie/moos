<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['burger', 'side']);
            $table->string('key')->unique();
            $table->string('name');
            $table->unsignedInteger('price_pence');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('menu_item_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->unsignedInteger('price_pence')->default(0);
            $table->timestamps();
        });

        Schema::create('menu_ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['standard', 'paid']);
            $table->unsignedInteger('price_pence')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('meal_deals', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->foreignId('side_menu_item_id')->nullable()->constrained('menu_items')->nullOnDelete();
            $table->unsignedInteger('discount_pence');
            $table->timestamps();
        });

        // Seed from config/menu.php data

        // Burgers
        $burgers = [
            ['key' => 'classic',  'name' => 'Classic Burger',  'price_pence' => 650, 'sort_order' => 1],
            ['key' => 'cheese',   'name' => 'Cheeseburger',    'price_pence' => 700, 'sort_order' => 2],
            ['key' => 'double',   'name' => 'Double Stack',    'price_pence' => 950, 'sort_order' => 3],
            ['key' => 'veggie',   'name' => 'Veggie Burger',   'price_pence' => 650, 'sort_order' => 4],
        ];
        foreach ($burgers as $burger) {
            DB::table('menu_items')->insert([
                'category'    => 'burger',
                'key'         => $burger['key'],
                'name'        => $burger['name'],
                'price_pence' => $burger['price_pence'],
                'sort_order'  => $burger['sort_order'],
                'active'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // Sides
        DB::table('menu_items')->insert([
            'category'    => 'side',
            'key'         => 'loaded_fries',
            'name'        => 'Loaded Fries',
            'price_pence' => 350,
            'sort_order'  => 1,
            'active'      => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $loadedFriesId = DB::table('menu_items')->where('key', 'loaded_fries')->value('id');

        DB::table('menu_item_extras')->insert([
            [
                'menu_item_id' => $loadedFriesId,
                'key'          => 'fries_cheese',
                'name'         => 'Cheese',
                'price_pence'  => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'menu_item_id' => $loadedFriesId,
                'key'          => 'fries_ham',
                'name'         => 'Ham',
                'price_pence'  => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);

        // Standard ingredients
        $standardIngredients = ['lettuce', 'tomato', 'onion', 'pickles', 'ketchup', 'mustard', 'mayo'];
        foreach ($standardIngredients as $i => $name) {
            DB::table('menu_ingredients')->insert([
                'name'       => $name,
                'type'       => 'standard',
                'price_pence'=> 0,
                'sort_order' => $i + 1,
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Paid extras
        $paidExtras = [
            ['name' => 'Bacon',        'price_pence' => 100],
            ['name' => 'Extra Cheese', 'price_pence' => 80],
            ['name' => 'Fried Egg',    'price_pence' => 90],
            ['name' => 'Jalapeños',    'price_pence' => 50],
        ];
        foreach ($paidExtras as $i => $extra) {
            DB::table('menu_ingredients')->insert([
                'name'        => $extra['name'],
                'type'        => 'paid',
                'price_pence' => $extra['price_pence'],
                'sort_order'  => $i + 1,
                'active'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // Meal deals
        DB::table('meal_deals')->insert([
            'label'              => 'Meal Deal',
            'side_menu_item_id'  => $loadedFriesId,
            'discount_pence'     => 100,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_deals');
        Schema::dropIfExists('menu_item_extras');
        Schema::dropIfExists('menu_ingredients');
        Schema::dropIfExists('menu_items');
    }
};
