<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('uploads', function (Blueprint $table) {
            $table->string('tags')->index()->nullable()->after('uploadable_type');
        });
    }

    public function down(): void {
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }
};
