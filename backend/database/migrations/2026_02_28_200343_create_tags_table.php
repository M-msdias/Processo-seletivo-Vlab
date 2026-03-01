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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('educational_resource_tag', function (Blueprint $table) {
            $table->foreignId('educational_resource_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('tag_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->primary(['educational_resource_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educational_resource_tag');
        Schema::dropIfExists('tags');
    }
};
