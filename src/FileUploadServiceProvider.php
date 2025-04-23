<?php

namespace Sanil\FileUpload;

use Illuminate\Support\ServiceProvider;

class FileUploadServiceProvider extends ServiceProvider
{
	public function boot(): void
	{
		// Load migrations if any
		$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

		// Publish migrations
		$this->publishes([
			__DIR__ . '/../database/migrations' => database_path('migrations'),
		], 'fileupload-migrations');

		// Publish config file
		$this->publishes([
			__DIR__ . '/../config/fileupload.php' => config_path('fileupload.php'),
		], 'fileupload-config');
	}

	public function register(): void
	{
		// Merge configuration file
		$this->mergeConfigFrom(
			__DIR__ . '/../config/fileupload.php',
			'fileupload'
		);
	}
}
