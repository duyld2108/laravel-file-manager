<?php

namespace Duyld2108\LaravelFileManager\Services;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class UploadValidator
{
    /**
     * Default disallowed extensions that could lead to Remote Code Execution
     */
    protected const DEFAULT_DISALLOWED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'html', 'htm', 'shtml', 'dhtml', 'xhtml',
        'exe', 'sh', 'bash', 'bat', 'cmd', 'ps1', 'cgi', 'pl', 'py', 'rb',
        'js', 'vbs', 'jar', 'apk', 'msi', 'com', 'scr', 'htaccess', 'htpasswd', 'env'
    ];

    /**
     * Default disallowed MIME types for executable files
     */
    protected const DEFAULT_DISALLOWED_MIMETYPES = [
        'text/x-php',
        'application/x-httpd-php',
        'application/x-php',
        'text/html',
        'application/xhtml+xml',
        'application/x-executable',
        'application/x-msdownload',
        'application/x-sh',
        'application/x-csh',
        'application/x-bat',
        'application/javascript',
        'text/javascript',
        'application/x-perl',
        'text/x-perl',
        'text/x-python',
        'application/x-python-code',
    ];

    /**
     * Validate uploaded file for security, extension, and MIME type
     *
     * @param UploadedFile $file
     * @param array $securityConfig
     * @param array $allowedFileTypes
     * @param int|null $maxFileSizeKb
     * @throws InvalidArgumentException
     */
    public static function validate(
        UploadedFile $file,
        array $securityConfig = [],
        array $allowedFileTypes = [],
        ?int $maxFileSizeKb = null
    ): void {
        // 1. Verify upload status
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException("File upload failed with error code: {$file->getError()}");
        }

        // 2. Max size check
        if ($maxFileSizeKb !== null && $maxFileSizeKb > 0) {
            $fileSizeKb = $file->getSize() / 1024;
            if ($fileSizeKb > $maxFileSizeKb) {
                throw new InvalidArgumentException("File size exceeds maximum allowed limit ({$maxFileSizeKb} KB).");
            }
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $filename = $file->getClientOriginalName();

        // 3. Prevent null byte or hidden traversal in filename
        if (str_contains($filename, "\0") || str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            throw new InvalidArgumentException("Filename contains invalid characters.");
        }

        // 4. Strict check on extension characters
        if (!empty($extension) && preg_match('/[^a-zA-Z0-9]/', $extension)) {
            throw new InvalidArgumentException("File extension contains invalid characters.");
        }

        // 5. Check against disallowed extensions
        $disallowedExtensions = array_unique(array_merge(
            self::DEFAULT_DISALLOWED_EXTENSIONS,
            $securityConfig['disallowed_extensions'] ?? []
        ));

        if (in_array($extension, $disallowedExtensions, true)) {
            throw new InvalidArgumentException("File extension .{$extension} is forbidden for security reasons.");
        }

        // 6. Check if extension starts with 'php' (e.g. php8, phtml, phar)
        if (str_starts_with($extension, 'php')) {
            throw new InvalidArgumentException("Executable scripts starting with 'php' are strictly blocked.");
        }

        // 7. Check if extension ends in html (e.g. xhtml, shtml)
        if (preg_match('/[a-z0-9]html$/i', $extension)) {
            throw new InvalidArgumentException("HTML variant scripts are blocked.");
        }

        // 8. Validate real MIME type via PHP fileinfo
        $realMime = strtolower((string) $file->getMimeType());
        $disallowedMimetypes = array_unique(array_merge(
            self::DEFAULT_DISALLOWED_MIMETYPES,
            $securityConfig['disallowed_mimetypes'] ?? []
        ));

        if (in_array($realMime, $disallowedMimetypes, true)) {
            throw new InvalidArgumentException("File MIME type '{$realMime}' is prohibited.");
        }

        // 9. If allowedFileTypes whitelist is configured
        if (!empty($allowedFileTypes)) {
            $allowedNormalized = array_map('strtolower', $allowedFileTypes);
            if (!in_array($extension, $allowedNormalized, true)) {
                throw new InvalidArgumentException("File type .{$extension} is not in the allowed types list.");
            }
        }
    }
}
