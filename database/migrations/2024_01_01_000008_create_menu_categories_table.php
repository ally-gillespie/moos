<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('menu_categories')->insert([
            ['key' => 'burger', 'label' => 'Burgers', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'side',   'label' => 'Sides',   'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Change category from a fixed enum to a plain string so new categories can be used.
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('category')->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_categories');

        Schema::table('menu_items', function (Blueprint $table) {
            $table->enum('category', ['burger', 'side'])->change();
        });
    }
};
