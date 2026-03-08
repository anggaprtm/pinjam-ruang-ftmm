<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sik_applications', function (Blueprint $table) {
            $table->string('issued_document_path')->nullable()->after('nomor_sik_eoffice');
        });
    }

    public function down(): void
    {
        Schema::table('sik_applications', function (Blueprint $table) {
            $table->dropColumn('issued_document_path');
        });
    }
};
