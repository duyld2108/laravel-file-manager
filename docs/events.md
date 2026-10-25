# Events

### BeforeInitialization

> Duyld2108\LaravelFileManager\Events\BeforeInitialization

Example:

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\BeforeInitialization',
    function ($event) {
        
    }
);
```

### DiskSelected

> Duyld2108\LaravelFileManager\Events\DiskSelected

Example:

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\DiskSelected',
    function ($event) {
        \Log::info('DiskSelected:', [$event->disk()]);
    }
);
```

### FilesUploading

> Duyld2108\LaravelFileManager\Events\FilesUploading

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\FilesUploading',
    function ($event) {
        \Log::info('FilesUploading:', [
            $event->disk(),
            $event->path(),
            $event->files(),
            $event->overwrite(),
        ]);
    }
);
```

### FilesUploaded

> Duyld2108\LaravelFileManager\Events\FilesUploaded

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\FilesUploaded',
    function ($event) {
        \Log::info('FilesUploaded:', [
            $event->disk(),
            $event->path(),
            $event->files(),
            $event->overwrite(),
        ]);
    }
);
```

### Deleting

> Duyld2108\LaravelFileManager\Events\Deleting

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\Deleting',
    function ($event) {
        \Log::info('Deleting:', [
            $event->disk(),
            $event->items(),
        ]);
    }
);
```

### Deleted

> Duyld2108\LaravelFileManager\Events\Deleted

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\Deleted',
    function ($event) {
        \Log::info('Deleted:', [
            $event->disk(),
            $event->items(),
        ]);
    }
);
```

### Paste

> Duyld2108\LaravelFileManager\Events\Paste

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\Paste',
    function ($event) {
        \Log::info('Paste:', [
            $event->disk(),
            $event->path(),
            $event->clipboard(),
        ]);
    }
);
```

### Rename

> Duyld2108\LaravelFileManager\Events\Rename

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\Rename',
    function ($event) {
        \Log::info('Rename:', [
            $event->disk(),
            $event->newName(),
            $event->oldName(),
            $event->type(), // 'file' or 'dir'
        ]);
    }
);
```

### Download

> Duyld2108\LaravelFileManager\Events\Download

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\Download',
    function ($event) {
        \Log::info('Download:', [
            $event->disk(),
            $event->path(),
        ]);
    }
);
```

*When using a text editor, the file you are editing is also downloaded! And this event is triggered!*

### DirectoryCreating

> Duyld2108\LaravelFileManager\Events\DirectoryCreating

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\DirectoryCreating',
    function ($event) {
        \Log::info('DirectoryCreating:', [
            $event->disk(),
            $event->path(),
            $event->name(),
        ]);
    }
);
```

### DirectoryCreated

> Duyld2108\LaravelFileManager\Events\DirectoryCreated

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\DirectoryCreated',
    function ($event) {
        \Log::info('DirectoryCreated:', [
            $event->disk(),
            $event->path(),
            $event->name(),
        ]);
    }
);
```

### FileCreating

> Duyld2108\LaravelFileManager\Events\FileCreating

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\FileCreating',
    function ($event) {
        \Log::info('FileCreating:', [
            $event->disk(),
            $event->path(),
            $event->name(),
        ]);
    }
);
```

### FileCreated

> Duyld2108\LaravelFileManager\Events\FileCreated

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\FileCreated',
    function ($event) {
        \Log::info('FileCreated:', [
            $event->disk(),
            $event->path(),
            $event->name(),
        ]);
    }
);
```

### FileUpdate

> Duyld2108\LaravelFileManager\Events\FileUpdate

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\FileUpdate',
    function ($event) {
        \Log::info('FileUpdate:', [
            $event->disk(),
            $event->path(),
        ]);
    }
);
```

### Zip

> Duyld2108\LaravelFileManager\Events\Zip

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\Zip',
    function ($event) {
        \Log::info('Zip:', [
            $event->disk(),
            $event->path(),
            $event->name(),
            $event->elements(),
        ]);
    }
);
```

### ZipCreated

> Duyld2108\LaravelFileManager\Events\ZipCreated

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\ZipCreated',
    function ($event) {
        \Log::info('ZipCreated:', [
            $event->disk(),
            $event->path(),
            $event->name(),
            $event->elements(),
        ]);
    }
);
```

### ZipFailed

> Duyld2108\LaravelFileManager\Events\ZipCreated

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\ZipFailed',
    function ($event) {
        \Log::info('ZipFailed:', [
            $event->disk(),
            $event->path(),
            $event->name(),
            $event->elements(),
        ]);
    }
);
```

### Unzip

> Duyld2108\LaravelFileManager\Events\Unzip

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\Unzip',
    function ($event) {
        \Log::info('Unzip:', [
            $event->disk(),
            $event->path(),
            $event->folder(),
        ]);
    }
);
```

### UnzipCreated

> Duyld2108\LaravelFileManager\Events\UnzipCreated

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\UnzipCreated',
    function ($event) {
        \Log::info('UnzipCreated:', [
            $event->disk(),
            $event->path(),
            $event->folder(),
        ]);
    }
);
```

### UnzipFailed

> Duyld2108\LaravelFileManager\Events\UnzipFailed

```php
\Event::listen('Duyld2108\LaravelFileManager\Events\UnzipFailed',
    function ($event) {
        \Log::info('UnzipFailed:', [
            $event->disk(),
            $event->path(),
            $event->folder(),
        ]);
    }
);
```
