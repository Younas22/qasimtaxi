<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $names = [
            'Bilzen', 'Diepenbeek', 'Hoeselt', 'Riemst', 'Lanaken', 'Zutendaal',
            'Genk', 'Hasselt', 'Tongeren', 'Kortessem', 'Beverst', 'Munsterbilzen',
            'Eigenbilzen', 'Mopertingen', 'Waltwilder', 'Martenslinde', 'Rosmeer',
            'Spouwen', 'Grote-Spouwen', 'Kleine-Spouwen', 'Hees', 'Vlijtingen',
            'Kanne', 'Membruggen', 'Herderen', 'Millen', 'Maastricht',
        ];

        $now = now();
        DB::table('areas')->insert(
            collect($names)->values()->map(fn ($name, $i) => [
                'name' => $name,
                'slug' => Str::slug($name),
                'sort_order' => $i + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
