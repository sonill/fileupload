<?php

namespace Sanil\FileUpload;

use Illuminate\Support\ServiceProvider;

class FileUploadServiceProvider extends ServiceProvider
{
	public function boot(): void
	{
		// Just load migrations automatically, no publishing
		$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
	}

	public function register(): void
	{
		// No config to merge, so leave empty or remove
	}
}
