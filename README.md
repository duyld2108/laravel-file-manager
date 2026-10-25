# Laravel File Manager (Enhanced Edition)

[![Latest Stable Version](https://img.shields.io/packagist/v/duyld2108/laravel-file-manager.svg?style=flat-square)](https://packagist.org/packages/duyld2108/laravel-file-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/duyld2108/laravel-file-manager.svg?style=flat-square)](https://packagist.org/packages/duyld2108/laravel-file-manager)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2%7C%5E8.3%7C%5E8.4-777bb4.svg?style=flat-square)](https://www.php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-%5E10%7C%5E11%7C%5E12-ff2d20.svg?style=flat-square)](https://laravel.com)

An advanced, enterprise-ready File Manager package for **Laravel 10, 11, and 12+**. 

Originally created by Aleksandr Manekin, this enhanced edition brings full compatibility with **Intervention Image v4.1+**, **Automated Image Resizing on upload**, **Format Conversion (WebP / AVIF)**, **Disk-based Thumbnail Caching**, and **Multi-layered Security Hardening** based on UniSharp standards and recent CVE audits.

---

## 🌟 Key Features & Improvements

* **Intervention Image v4.1+ & PHP 8.3/8.4 Support**:
  - Fully compatible with `intervention/image-laravel: ^4.1` and `intervention/image: ^4.0`.
  - Replaced deprecated `read()` with transparent decoding (`decodeBinary()`, `scaleDown()`, and `encodeUsingFormat()`).
  - Seamless fallback support for Intervention Image v3.
* **Auto Image Resizing on Upload**:
  - Automatically downsizes massive smartphone and camera photos (e.g. 4000x3000px, 15MB) to configurable dimensions (`max_width: 2048`, `max_height: 2048`).
  - Employs proportional `scaleDown` algorithm: preserves aspect ratio and never stretches or upscales smaller images.
* **Modern Format Conversion (WebP & AVIF)**:
  - Automatically converts raster images (`.jpg`, `.jpeg`, `.png`, `.bmp`) to modern next-gen web formats (**WebP** or **AVIF**).
  - Configurable lossy compression quality (default: `85%`), saving up to 70% storage and bandwidth.
  - Built-in `keep_original_when_larger` safety check: if the optimized version is larger than the original, the original is retained.
* **Disk-based Thumbnail Caching**:
  - Thumbnails (120x120px) are pre-generated upon upload and saved directly into a hidden `.thumbs/` directory on your storage disk.
  - Eliminates on-the-fly thumbnail generation in RAM/CPU, reducing server resource consumption by over 90% when browsing folders with hundreds of images.
  - Automatically syncs thumbnail deletion on file removal and thumbnail renaming on file rename.
* **Multi-Layered Security Hardening (UniSharp Standard)**:
  - **MIME-Type Verification**: Uses PHP's `fileinfo` extension (`$file->getMimeType()`) to inspect actual magic bytes, rejecting forged client headers.
  - **Executable Upload Blocking**: Strictly prohibits `.php`, `.php3`-`.php8`, `.phtml`, `.phar`, `.html`, `.exe`, `.sh`, `.bat`, `.cmd`, `.cgi`, `.pl`, `.js`, `.env`, `.htaccess` files.
  - **Zip Slip Defense (CVE-2025-65346)**: Inspects all entry paths inside zip archives prior to extraction, completely neutralizing Directory Traversal attacks.
* **Modernized UI & Dark Mode**:
  - Blade views upgraded to **Bootstrap 5.3.3** with the clean **Inter** font family.
  - **Auto Dark Mode**: Detects system preferences (`prefers-color-scheme: dark`) and activates dark theme automatically.
  - Dynamic responsive container height recalculation on window resize.
* **Drop-in Replacement**:
  - Uses the same internal namespaces and Blade view prefixes as `alexusmai/laravel-file-manager`. Upgrading requires zero changes to existing controllers or views!

---

## 📋 System Requirements

| Component | Supported Version |
| :--- | :--- |
| **PHP** | `^8.2`, `^8.3`, `^8.4` |
| **Laravel Framework** | `^10.0`, `^11.0`, `^12.0`, `^13.0` |
| **Intervention Image Laravel** | `^1.2.0` or `^4.0` / `^4.1` |
| **PHP Extensions** | `ext-fileinfo`, `ext-zip`, `ext-json`, `GD` or `Imagick` |
| **Flysystem** | `league/flysystem: ^3.0` |

---

## 📥 Installation

Install the package via Composer:

```bash
composer require duyld2108/laravel-file-manager
```

### Publish Configuration & Assets

Publish the configuration file:
```bash
php artisan vendor:publish --tag=fm-config
```

Publish the frontend Vue.js assets (JavaScript and CSS):
```bash
php artisan vendor:publish --tag=fm-assets
```

Ensure your Laravel storage is linked to the public directory:
```bash
php artisan storage:link
```

---

## ⚙️ Configuration (`config/file-manager.php`)

After publishing, check `config/file-manager.php`. Here are the most important settings:

### 1. Image Optimization & Auto-Resize
```php
'image_optimization' => [
    // Enable or disable auto optimization on upload
    'enabled'                   => true,

    // Target format: 'webp', 'avif', 'jpg', 'png', or null (keep original format)
    'format'                    => 'webp',

    // Quality for lossy formats (1 - 100)
    'quality'                   => 85,

    // Maximum dimensions in pixels (proportional scaleDown)
    'max_width'                 => 2048,
    'max_height'                => 2048,

    // Keep original file if the converted version is larger
    'keep_original_when_larger' => true,

    // Supported raster mimetypes
    'mimetypes'                 => [
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/webp',
        'image/bmp',
    ],
],
```

### 2. Disk Thumbnail Caching
```php
'thumbnails' => [
    // Cache thumbnails on disk to avoid repeating on-the-fly image operations
    'cache_on_disk' => true,

    // Hidden directory name for cached thumbnails
    'thumb_folder'  => '.thumbs',

    // Thumbnail dimensions (pixels)
    'width'         => 120,
    'height'        => 120,
],
```

### 3. Security & Upload Filtering
```php
'security' => [
    // Validate actual MIME types via PHP fileinfo
    'validate_mime'         => true,

    // Disallowed extensions (case-insensitive)
    'disallowed_extensions' => [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'html', 'htm', 'shtml', 'dhtml', 'xhtml',
        'exe', 'sh', 'bash', 'bat', 'cmd', 'ps1', 'cgi', 'pl', 'py', 'rb',
        'js', 'vbs', 'jar', 'apk', 'msi', 'htaccess', 'htpasswd', 'env'
    ],

    // Disallowed MIME types
    'disallowed_mimetypes'  => [
        'text/x-php',
        'application/x-httpd-php',
        'application/x-php',
        'text/html',
        'application/xhtml+xml',
        'application/x-executable',
        'application/x-msdownload',
        'application/x-sh',
        'application/javascript',
        'text/javascript',
    ],
],
```

### 4. Disk & Routing Settings
```php
// Route prefix (default: /file-manager)
'routePrefix' => 'file-manager',

// Disk names from config/filesystems.php accessible in file manager
'diskList' => ['public'],

// Window layout: 1 = Single window, 2 = Window with directory tree, 3 = Two windows
'windowsConfig' => 2,

// Middleware applied to file manager routes
'middleware' => ['web', 'auth'],
```

---

## 🖥️ WYSIWYG Editor Integrations

### 1. Standalone Popup Button (Input Field Integration)

In your Blade template:
```html
<div class="input-group">
    <input type="text" id="image_label" class="form-control" name="image">
    <button class="btn btn-outline-secondary" type="button" id="button-image">Browse</button>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('button-image').addEventListener('click', (event) => {
        event.preventDefault();
        window.open('/file-manager/fm-button', 'fm', 'width=1400,height=800');
    });
});

// Callback automatically invoked when a file is selected
function fmSetLink($url) {
    document.getElementById('image_label').value = $url;
}
</script>
```

### 2. CKEditor 4 Integration

```javascript
CKEDITOR.replace('my-editor', {
    filebrowserBrowseUrl: '/file-manager/ckeditor'
});
```

### 3. TinyMCE 5 / 6 Integration

```javascript
tinymce.init({
    selector: 'textarea#my-editor',
    plugins: 'image code',
    toolbar: 'undo redo | link image | code',
    file_picker_callback: function (callback, value, meta) {
        let x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
        let y = window.innerHeight || document.documentElement.clientHeight || document.getElementsByTagName('body')[0].clientHeight;

        let cmsURL = '/file-manager/tinymce5';

        tinymce.activeEditor.windowManager.openUrl({
            url: cmsURL,
            title: 'File Manager',
            width: x * 0.8,
            height: y * 0.8,
            onMessage: (api, message) => {
                callback(message.content);
            }
        });
    }
});
```

### 4. Summernote Integration

```javascript
$('#summernote').summernote({
    toolbar: [
        ['insert', ['picture', 'link', 'video']]
    ],
    callbacks: {
        onInit: function() {
            // custom button or open /file-manager/summernote
        }
    }
});
```

---

## 🔄 Migration to `duyld2108/laravel-file-manager`

To migrate to this standalone package, simply update your root `composer.json`:
```diff
- "alexusmai/laravel-file-manager": "^3.3",
+ "duyld2108/laravel-file-manager": "^1.0",
```

In your published `config/file-manager.php`, update the repository classes to the new namespace:
```php
use Duyld2108\LaravelFileManager\Services\ConfigService\DefaultConfigRepository;
use Duyld2108\LaravelFileManager\Services\ACLService\ConfigACLRepository;
```

Or re-publish the fresh configuration file with all parameters:
```bash
php artisan vendor:publish --tag=file-manager-config --force
```

---

## 🛡️ Security Policy

If you discover a security vulnerability, please submit an issue or send an email to `duyld@tnus.edu.vn`.

---

## 📄 License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.

* Original Creator: [Aleksandr Manekin](https://github.com/alexusmai)
* Enhanced Edition Maintainer: [Le Duc Duy](https://github.com/duyld2108)
