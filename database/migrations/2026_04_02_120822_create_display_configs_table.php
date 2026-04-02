<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('display_configs', function (Blueprint $table) {
            $table->id();
            $table->string('location'); // lantai6, lantai7, dll
            $table->string('mode')->default('dashboard'); // dashboard | announcement
            $table->string('content_type')->nullable(); // image | text | url
            $table->text('content_value')->nullable(); // isi konten
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('display_configs');
    }
};
