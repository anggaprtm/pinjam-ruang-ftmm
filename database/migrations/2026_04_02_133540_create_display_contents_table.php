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
        Schema::create('display_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('display_config_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // image / text
            $table->text('value')->nullable();
            $table->string('image_path')->nullable();
            $table->integer('duration')->default(5); // detik
            $table->integer('order')->default(0);
            $table->timestamps();
        });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('display_contents');
    }
};
