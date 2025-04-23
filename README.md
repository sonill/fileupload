Laravel File Upload Trait
=========================

A Laravel package to handle file uploads, including image resizing, storage management, and URL generation for uploaded files. This package provides a reusable solution for managing file uploads in Laravel applications with support for multiple storage disks, collections, and image resizing using the Spatie Image library.

Features
--------

*   **File Uploads**: Upload files to specified storage disks with unique directory paths using UUIDs.
*   **Image Resizing**: Automatically resize images to predefined sizes (configured in config file).
*   **Storage Management**: Handle file storage, visibility, and directory creation on various disks (e.g., public, S3).
*   **URL Generation**: Generate public or temporary URLs for uploaded files, with support for regenerating missing thumbnails.
*   **File Deletion**: Delete individual or all uploads associated with a model, including their storage directories and database records.    
*   **Error Logging**: Comprehensive error logging for upload, resize, and deletion operations.
    

Requirements
------------

*   PHP >= 8.0
*   Laravel >= 10.x
    

Installation
------------

1.  **Install the Package**
    
    You can install this via Composer:
    
        composer require sanil/file-upload
    
    
    Run migrations:

        php artisan migrate
    
3.  **Configure Image Sizes**
    
    Add image size configurations to your config/image.php file:
    
        return [
            'size' => [
                [300, 300], // Thumbnail size (width, height)
                [800, 600], // Medium size
                [1200, 900], // Large size
            ],
        ];
        

Usage
-----

1.  **Add the Trait to a Model**
    
    Use the FileUpload trait in any model that needs file upload functionality:
    
        namespace App\Models;
        
        use Illuminate\Database\Eloquent\Model;
        use Sanil\FileUpload\Traits\FileUpload;
        
        class Post extends Model
        {
            use FileUpload;
        }
    
2.  **Upload a File**
    
    Upload a file (e.g., from a controller):
    
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
    

    
        $post = Post::find(1);
        $upload = $post->uploads()->first();
        
        // Get URL for the full-size image
        $fullUrl = $post->get_file_public_url($upload, 'full');
        
        // Get URL for a resized image (e.g., 300x300)
        $thumbnailUrl = $post->get_file_public_url($upload, '300x300');
    
4.  **Delete Uploads**
    
    Delete a specific upload or all uploads for a model:
    
    php
    

    
        $post = Post::find(1);
        
        // Delete a specific upload
        $upload = $post->uploads()->first();
        $post->deleteUploads($upload);
        
        // Delete all uploads
        $post->deleteUploads();
    

    

Configuration
-------------

*   **Image Sizes**: Define in config/image.php as an array of width, height pairs.
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

For issues, questions, or suggestions, please open an issue on the GitHub repository or contact the maintainer at: sanilshakya@gmail.com
