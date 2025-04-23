<?php

namespace Sanil\FileUpload\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sanil\FileUpload\Models\Upload;

trait FileUpload
{
	public function uploads()
	{
		return $this->morphMany( Upload::class, 'uploadable' );
	}

	public function upload( $file, ?string $collection = 'default', string $disk_name = 'public' ): false|Upload
	{
		try {
			$uuid = Str::uuid()->toString();
			$uuid = str_replace( '-', '', $uuid );

			// Append model name to the upload path dynamically
			$upload_dir_path = 'uploads/' . $uuid;

			$upload = $this->handleFileUpload(
				file           : $file,
				upload_dir_path: $upload_dir_path,
				disk_name      : $disk_name,
			);

			if ( $upload === false ) {
				throw new \Exception( 'File upload failed' );
			}

			return $this->uploads()->create( [
				'upload_path' => $upload[ 'upload_path' ],
				'mime_type'   => $upload[ 'mime_type' ],
				'ext'         => $upload[ 'extension' ],
				'disk'        => $upload[ 'disk' ],
				'size'        => round( $upload[ 'size' ], 2 ), // rounded size in KB
				'collection'  => $collection,
			] );

		}
		catch ( \Exception $e ) {
			Log::error( 'File upload error: ' . $e->getMessage() );
			return false;
		}
	}

	protected function handleFileUpload( $file, string $upload_dir_path, string $disk_name = 'public' ): array|false
	{
		if ( !$file ) {
			return false;
		}

		$folder_name = $upload_dir_path;
		$extension = $file->getClientOriginalExtension();
		$filename = "full.$extension";

		// Make sure the directory exists using the specified disk
		Storage::disk( $disk_name )->makeDirectory( $folder_name );

		$full_relative_path = "$folder_name/$filename";
		$full_storage_path = Storage::disk( $disk_name )->path( $full_relative_path );

		Log::info( 'Uploading to: ' . $full_storage_path );

		// Store the original file
		Storage::disk( $disk_name )->putFileAs( $folder_name, $file, $filename );

		// Ensure visibility matches disk config
		$disk_visibility = config( "filesystems.disks.$disk_name.visibility", 'private' );
		Storage::disk( $disk_name )->setVisibility( $full_relative_path, $disk_visibility );

		$mime = $file->getClientMimeType();
		$filesize = $file->getSize();

		try {
			// Only resize if the file is an image
			if ( Str::startsWith( $mime, 'image/' ) ) {
				$image_sizes = config( 'image.size' );

				foreach ( $image_sizes as $size ) {
					$new_filename = $size[ 0 ] . 'x' . $size[ 1 ] . '.' . $extension;
					$new_relative_path = "$folder_name/$new_filename";
					$new_image_full_path = Storage::disk( $disk_name )->path( $new_relative_path );

					// Resize and save using Spatie Image
					\Spatie\Image\Image::load( $full_storage_path )
						->width( $size[ 0 ] )
						->height( $size[ 1 ] )
						->save( $new_image_full_path );

					// Manually set visibility for the thumbnail
					Storage::disk( $disk_name )->setVisibility( $new_relative_path, $disk_visibility );
				}
			}
		}
		catch ( \Exception $e ) {
			Log::error( 'Image resize error: ' . $e->getMessage() );
		}

		return [
			'upload_path' => $folder_name,
			'extension'   => $extension,
			'size'        => $filesize / 1024, // in KB
			'mime_type'   => $mime,
			'disk'        => $disk_name,
		];
	}

	public function get_file_public_url(
		Upload  $model,
		?string $image_size = 'full',
		bool    $re_generate_thumbnail_if_missing = true,
		int     $expires_after_in_minutes = 5
	): string|false {
		try {
			static $url_cache = [];

			$cache_key = $model->id . '_' . $image_size;
			if ( isset( $url_cache[ $cache_key ] ) ) {
				return $url_cache[ $cache_key ];
			}

			$image_sizes = config( 'image.size' );
			$upload_path = $model->upload_path;
			$disk = $model->disk;
			$ext = $model->ext;

			if ( !array_key_exists( $image_size, $image_sizes ) && $image_size !== 'full' ) {
				throw new \Exception( __( 'Invalid image size' ) );
			}

			if ( $image_size !== 'full' ) {
				$width = $image_sizes[ $image_size ][ 0 ];
				$height = $image_sizes[ $image_size ][ 1 ];
			}

			$file_name_full = "full.{$ext}";
			$file_name = ( $image_size === 'full' ) ? $file_name_full : "{$width}x{$height}.{$ext}";
			$image_path = "{$upload_path}/{$file_name}";

			if ( Storage::disk( $disk )->exists( $image_path ) ) {
				$url = Storage::disk( $disk )->visibility( $image_path ) === 'public'
					? Storage::disk( $disk )->url( $image_path )
					: Storage::disk( $disk )->temporaryUrl( $image_path, now()->addMinutes( $expires_after_in_minutes ) );

				$url_cache[ $cache_key ] = $url;
				return $url;
			}

			$full_image_path = "{$upload_path}/{$file_name_full}";
			if ( !Storage::disk( $disk )->exists( $full_image_path ) ) {
				throw new \Exception( __( 'Source image not found' ) );
			}

			if ( $re_generate_thumbnail_if_missing ) {
				$src_path = storage_path( "app/{$disk}/{$full_image_path}" );
				$new_filename = "{$width}x{$height}.{$ext}";
				$new_image_path = "{$upload_path}/{$new_filename}";
				$new_image_full_path = storage_path( "app/{$disk}/{$new_image_path}" );

				if ( !file_exists( dirname( $new_image_full_path ) ) ) {
					mkdir( dirname( $new_image_full_path ), 0755, true );
				}

				\Spatie\Image\Image::load( $src_path )
					->width( $width )
					->height( $height )
					->save( $new_image_full_path );

				$url = Storage::disk( $disk )->temporaryUrl( $new_image_path, now()->addMinutes( $expires_after_in_minutes ) );
				$url_cache[ $cache_key ] = $url;
				return $url;
			}
		}
		catch ( \Exception $e ) {
			Log::error( 'HelperService$get_file_public_url : ' . $e->getMessage() );
			return false;
		}

		return false;
	}


	public function deleteUploads( Upload $upload = null ): bool
	{
		try {
			$uploadsToDelete = collect();

			// If specific upload is passed, validate the relationship
			if ( $upload ) {
				if (
					$upload->uploadable_type !== get_class( $this ) ||
					$upload->uploadable_id !== $this->id
				) {
					Log::warning( "Attempted to delete upload not belonging to the model", [
						'model_type' => get_class( $this ),
						'model_id'   => $this->id,
						'upload_id'  => $upload->id,
					] );
					return false;
				}

				// Add specific upload to collection if validated
				$uploadsToDelete->push( $upload );
			} else {
				// If no specific upload, get all uploads for the model
				$uploadsToDelete = $this->uploads;
			}

			foreach ( $uploadsToDelete as $item ) {
				// Check if the upload path exists and is not empty before attempting deletion
				if ( !empty( $item->upload_path ) && Storage::disk( $item->disk )->exists( $item->upload_path ) ) {
					// Log the deletion process for tracking
					Log::info( "Deleting upload directory", [
						'model_type'  => get_class( $this ),
						'model_id'    => $this->id,
						'upload_path' => $item->upload_path,
					] );

					// Delete directory from the disk
					Storage::disk( $item->disk )->deleteDirectory( $item->upload_path );
				}

				// Log the deletion of the upload record
				Log::info( "Deleting upload record", [
					'upload_id'  => $item->id,
					'model_type' => get_class( $this ),
					'model_id'   => $this->id,
				] );

				// Delete the upload record from the database
				$item->delete();
			}

			return true;
		}
		catch ( \Exception $e ) {
			Log::error( 'deleteUploads error: ' . $e->getMessage(), [
				'model_type' => get_class( $this ),
				'model_id'   => $this->id,
				'upload_id'  => $upload?->id,
			] );
			return false;
		}
	}


}
