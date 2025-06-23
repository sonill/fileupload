<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUploadsTable extends Migration
{
	public function up(): void
	{
		Schema::table( 'uploads', function ( Blueprint $table ) {
			$table->unsignedBigInteger( 'uploadable_id' )->nullable()->change();
			$table->string( 'uploadable_type' )->nullable()->change();
		} );
	}

	public function down(): void
	{
		Schema::table('uploads', function (Blueprint $table) {
			$table->unsignedBigInteger('uploadable_id')->nullable(false)->change();
			$table->string('uploadable_type')->nullable(false)->change();
		});
	}
}
