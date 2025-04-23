Laravel File Upload Trait
=========================

A Laravel trait to handle file uploads, including image resizing, storage management, and URL generation for uploaded files. This package provides a reusable solution for managing file uploads in Laravel applications with support for multiple storage disks, collections, and image resizing using the Spatie Image library.

Features
--------

*   **File Uploads**: Upload files to specified storage disks with unique directory paths using UUIDs.
    
*   **Image Resizing**: Automatically resize images to predefined sizes (configured in image.size) using Spatie Image.
    
*   **Polymorphic Relationships**: Associate uploads with any model using a morphMany relationship.
    
*   **Storage Management**: Handle file storage, visibility, and directory creation on various disks (e.g., public, S3).
    
*   **URL Generation**: Generate public or temporary URLs for uploaded files, with support for regenerating missing thumbnails.
    
*   **File Deletion**: Delete individual or all uploads associated with a model, including their storage directories and database records.
    
*   **Error Logging**: Comprehensive error logging for upload, resize, and deletion operations.
    

Requirements
------------

*   PHP >= 8.0
    
*   Laravel >= 8.x
    
*   Spatie Image for image resizing
    
*   Laravel Storage (configured disks, e.g., public, S3)
    
*   A database table for the Upload model (provided by Sanil\\FileUpload\\Models\\Upload)
    

Installation
------------

1.  **Install the Package**
    
    Since this is a custom trait, you can include it in your Laravel project by copying the FileUpload trait to your desired namespace (e.g., app/Traits/FileUpload.php) or by creating a package.
    
    If using as a package, publish it to a repository and install via Composer:
    
    bash
    
    CollapseCopy
    
        composer require your-vendor/laravel-file-upload
    
2.  **Set Up the Upload Model**
    
    Ensure the Upload model (Sanil\\FileUpload\\Models\\Upload) exists and has the following schema:
    
    php
    
    CollapseCopy
    
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->string('upload_path');
            $table->string('mime_type');
            $table->string('ext');
            $table->string('disk');
            $table->float('size'); // Size in KB
            $table->string('collection')->default('default');
            $table->morphs('uploadable'); // Polymorphic relationship
            $table->timestamps();
        });
    
    Run migrations:
    
    bash
    
    CollapseCopy
    
        php artisan migrate
    
3.  **Configure Image Sizes**
    
    Add image size configurations to your config/image.php file:
    
    php
    
    CollapseCopy
    
        return [
            'size' => [
                [300, 300], // Thumbnail size (width, height)
                [800, 600], // Medium size
                [1200, 900], // Large size
            ],
        ];
    
4.  **Install Spatie Image**
    
    Install the Spatie Image library for image resizing:
    
    bash
    
    CollapseCopy
    
        composer require spatie/image
    
5.  **Configure Storage Disks**
    
    Ensure your storage disks are configured in config/filesystems.php. For example:
    
    php
    
    CollapseCopy
    
        'disks' => [
            'public' => [
                'driver' => 'local',
                'root' => storage_path('app/public'),
                'url' => env('APP_URL').'/storage',
                'visibility' => 'public',
            ],
            // Add other disks like S3 if needed
        ],
    

Usage
-----

1.  **Add the Trait to a Model**
    
    Use the FileUpload trait in any model that needs file upload functionality:
    
    php
    
    CollapseCopy
    
        namespace App\Models;
        
        use Illuminate\Database\Eloquent\Model;
        use Sanil\FileUpload\Traits\FileUpload;
        
        class Post extends Model
        {
            use FileUpload;
        }
    
2.  **Upload a File**
    
    Upload a file (e.g., from a controller):
    
    php
    
    CollapseCopy
    
        use App\Models\Post;
        use Illuminate\Http\Request;
        
        public function uploadFile(Request $request)
        {
            $post = Post::find(1);
            $file = $request->file('file');
        
            $upload = $post->upload($file, 'images', 'public');
        
            if ($upload) {
                return response()->json(['message' => 'File uploaded successfully', 'upload' => $upload]);
            }
        
            return response()->json(['message' => 'File upload failed'], 500);
        }
    
3.  **Get File URL**
    
    Generate a public or temporary URL for an uploaded file:
    
    php
    
    CollapseCopy
    
        $post = Post::find(1);
        $upload = $post->uploads()->first();
        
        // Get URL for the full-size image
        $fullUrl = $post->get_file_public_url($upload, 'full');
        
        // Get URL for a resized image (e.g., 300x300)
        $thumbnailUrl = $post->get_file_public_url($upload, '300x300');
    
4.  **Delete Uploads**
    
    Delete a specific upload or all uploads for a model:
    
    php
    
    CollapseCopy
    
        $post = Post::find(1);
        
        // Delete a specific upload
        $upload = $post->uploads()->first();
        $post->deleteUploads($upload);
        
        // Delete all uploads
        $post->deleteUploads();
    

Methods
-------

*   uploads(): Defines a morphMany relationship to the Upload model.
    
*   upload($file, ?string $collection = 'default', string $disk\_name = 'public'): Uploads a file to the specified disk and collection, returning an Upload model or false on failure.
    
*   handleFileUpload($file, string $upload\_dir\_path, string $disk\_name = 'public'): Handles the file upload process, including storage and image resizing.
    
*   get\_file\_public\_url(Upload $model, ?string $image\_size = 'full', bool $re\_generate\_thumbnail\_if\_missing = true, int $expires\_after\_in\_minutes = 5): Generates a public or temporary URL for an uploaded file, with optional thumbnail regeneration.
    
*   deleteUploads(Upload $upload = null): Deletes a specific upload or all uploads associated with the model, including their storage directories and database records.
    

Configuration
-------------

*   **Image Sizes**: Define in config/image.php as an array of \[width, height\] pairs.
    
*   **Storage Disks**: Configure in config/filesystems.php to match your storage needs (e.g., local, S3).
    
*   **Visibility**: The trait respects the disk's visibility setting (public or private) and applies it to uploaded files and thumbnails.
    

Error Handling
--------------

*   Errors during upload, resizing, or deletion are logged using Laravel's Log facade.
    
*   Methods return false on failure, with detailed error messages in the logs.
    

Contributing
------------

1.  Fork the repository.
    
2.  Create a feature branch (git checkout -b feature/new-feature).
    
3.  Commit your changes (git commit -m 'Add new feature').
    
4.  Push to the branch (git push origin feature/new-feature).
    
5.  Open a pull request.
    

License
-------

This project is licensed under the MIT License. See the LICENSE file for details.

Support
-------

For issues, questions, or suggestions, please open an issue on the GitHub repository or contact the maintainer at \[your-email@example.com\].
