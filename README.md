# FileUpload Package for Laravel

[![Packagist](https://img.shields.io/packagist/v/sanil/fileupload.svg)](https://packagist.org/packages/sanil/fileupload)
[![PHP Version Support](https://img.shields.io/packagist/php-v/sanil/fileupload.svg)](https://www.php.net/)

A powerful and flexible file upload management package for Laravel. The **FileUpload** package allows you to easily upload, resize images, and manage file storage in your Laravel applications. 

With support for multiple disks (local, S3, etc.), automatic image resizing, and seamless integration with your Laravel models, this package will make handling file uploads a breeze.

## Features

- Seamless integration with Laravel models via **morphMany** relationships.
- Supports multiple disk configurations (local, public, S3, etc.).
- Automatic image resizing for thumbnails or custom sizes.
- Store files with configurable visibility (public or private).
- Provides easy-to-use methods to retrieve and delete uploaded files.
- Configurable image size presets for resizing (using Spatie Image).

## Installation

To install the **FileUpload** package in your Laravel project, follow the steps below:

### 1. Install via Composer

Run the following command to install the package from **Packagist**:

```bash
composer require sanil/fileupload
