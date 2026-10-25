# Installation

1. Install package using Composer:

```bash
composer require duyld2108/laravel-file-manager
```

2. Publish configuration file:

```bash
php artisan vendor:publish --tag=fm-config
```

3. Publish compiled and minimized JS and CSS files:

```bash
php artisan vendor:publish --tag=fm-assets
```

4. Create symbolic link for storage:

```bash
php artisan storage:link
```

5. Set up the view where you want to embed the File Manager:

Add the CSRF Token and required styles to the `<head>`:

```html
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Bootstrap 5.3+ CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<!-- Bootstrap Icons (1.11.3) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- File Manager CSS -->
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
```

Add the container div and the File Manager script to the `<body>`:

```html
<div id="fm" style="height: 600px;"></div>

<!-- File Manager JS -->
<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>
```

## What's next

- [Configuration](./configuration.md)
- [Editor Integration (CKEditor, TinyMCE, Summernote)](./wysiwyg.md)
