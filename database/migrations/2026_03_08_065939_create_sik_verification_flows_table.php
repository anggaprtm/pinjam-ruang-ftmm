<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sik_verification_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_ormawa_id')->constrained('jenis_ormawas');
            $table->string('nama_flow');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sik_verification_flows');
    }
};
