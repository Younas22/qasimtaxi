<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('popular_routes', function (Blueprint $table) {
            $table->foreignId('route_type_id')->nullable()->after('type')->constrained()->nullOnDelete();
        });

        $typeIds = DB::table('route_types')->pluck('id', 'slug');

        foreach ($typeIds as $slug => $id) {
            DB::table('popular_routes')->where('type', $slug)->update(['route_type_id' => $id]);
        }

        // Anything that didn't match a known slug falls back to "City Route"
        // rather than being left orphaned.
        DB::table('popular_routes')->whereNull('route_type_id')->update(['route_type_id' => $typeIds['city'] ?? $typeIds->first()]);

        Schema::table('popular_routes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('popular_routes', function (Blueprint $table) {
            $table->enum('type', ['airport', 'city', 'intercity'])->default('city')->after('id');
        });

        $slugsById = DB::table('route_types')->pluck('slug', 'id');

        foreach ($slugsById as $id => $slug) {
            DB::table('popular_routes')->where('route_type_id', $id)->update(['type' => $slug]);
        }

        Schema::table('popular_routes', function (Blueprint $table) {
            $table->dropForeign(['route_type_id']);
            $table->dropColumn('route_type_id');
        });
    }
};
