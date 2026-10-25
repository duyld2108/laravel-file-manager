<?php

use Duyld2108\LaravelFileManager\Services\ConfigService\DefaultConfigRepository;
use Duyld2108\LaravelFileManager\Services\ACLService\ConfigACLRepository;

return [

    /**
     * Set Config repository
     *
     * Default - DefaultConfigRepository get config from this file
     */
    'configRepository'  => DefaultConfigRepository::class,

    /**
     * ACL rules repository
     *
     * Default - ConfigACLRepository (see rules in - aclRules)
     */
    'aclRepository'     => ConfigACLRepository::class,

    //********* Default configuration for DefaultConfigRepository **************

    /**
     * LFM Route prefix
     * !!! WARNING - if you change it, you should compile frontend with new prefix(baseUrl) !!!
     */
    'routePrefix'       => 'file-manager',

    /**
     * List of disk names that you want to use
     * (from config/filesystems)
     */
    'diskList'          => ['public'],

    /**
     * Default disk for left manager
     *
     * null - auto select the first disk in the disk list
     */
    'leftDisk'          => null,

    /**
     * Default disk for right manager
     *
     * null - auto select the first disk in the disk list
     */
    'rightDisk'         => null,

    /**
     * Default path for left manager
     *
     * null - root directory
     */
    'leftPath'          => null,

    /**
     * Default path for right manager
     *
     * null - root directory
     */
    'rightPath'         => null,

    /**
     * File manager modules configuration
     *
     * 1 - only one file manager window
     * 2 - one file manager window with directories tree module
     * 3 - two file manager windows
     */
    'windowsConfig'     => 2,

    /**
     * File upload - Max file size in KB
     *
     * null - no restrictions
     */
    'maxUploadFileSize' => null,

    /**
     * File upload - Allow these file types
     *
     * [] - no restrictions
     */
    'allowFileTypes'    => [],

    /**
     * Show / Hide system files and folders
     */
    'hiddenFiles'       => true,

    /***************************************************************************
     * Middleware
     *
     * Add your middleware name to array -> ['web', 'auth', 'admin']
     * !!!! RESTRICT ACCESS FOR NON ADMIN USERS !!!!
     */
    'middleware'        => ['web'],

    /***************************************************************************
     * ACL mechanism ON/OFF
     *
     * default - false(OFF)
     */
    'acl'               => false,

    /**
     * Hide files and folders from file-manager if user doesn't have access
     *
     * ACL access level = 0
     */
    'aclHideFromFM'     => true,

    /**
     * ACL strategy
     *
     * blacklist - Allow everything(access - 2 - r/w) that is not forbidden by the ACL rules list
     *
     * whitelist - Deny anything(access - 0 - deny), that not allowed by the ACL rules list
     */
    'aclStrategy'       => 'blacklist',

    /**
     * ACL Rules cache
     *
     * null or value in minutes
     */
    'aclRulesCache'     => null,

    //********* Default configuration for DefaultConfigRepository END **********


    /***************************************************************************
     * ACL rules list - used for default ACL repository (ConfigACLRepository)
     *
     * 1 it's user ID
     * null - for not authenticated user
     *
     * 'disk' => 'disk-name'
     *
     * 'path' => 'folder-name'
     * 'path' => 'folder1*' - select folder1, folder12, folder1/sub-folder, ...
     * 'path' => 'folder2/*' - select folder2/sub-folder,... but not select folder2 !!!
     * 'path' => 'folder-name/file-name.jpg'
     * 'path' => 'folder-name/*.jpg'
     *
     * * - wildcard
     *
     * access: 0 - deny, 1 - read, 2 - read/write
     */
    'aclRules'          => [
        null => [
            //['disk' => 'public', 'path' => '/', 'access' => 2],
        ],
        1    => [
            //['disk' => 'public', 'path' => 'images/arch*.jpg', 'access' => 2],
            //['disk' => 'public', 'path' => 'files/*', 'access' => 1],
        ],
    ],

    /**
     * Enable slugification of filenames of uploaded files.
     *
     */
    'slugifyNames'      => false,

    /***************************************************************************
     * Image Optimization & Auto-Resize on Upload
     *
     * Automatically resize large images and optionally convert format (e.g. WebP/AVIF).
     * Powered by Intervention Image v4.1+.
     */
    'image_optimization' => [
        // Enable or disable auto optimization
        'enabled'                   => true,

        // Target format to convert to: 'webp', 'avif', 'jpg', 'png', or null (keep original format)
        'format'                    => 'webp',

        // Output quality for lossy formats (1-100)
        'quality'                   => 85,

        // Maximum dimensions (pixels) - image scales down proportionally, never upscales
        'max_width'                 => 2048,
        'max_height'                => 2048,

        // Keep original file if optimized version is larger in bytes
        'keep_original_when_larger' => true,

        // Supported raster mimetypes for optimization
        'mimetypes'                 => [
            'image/jpeg',
            'image/pjpeg',
            'image/png',
            'image/webp',
            'image/bmp',
        ],
    ],

    /***************************************************************************
     * Disk Thumbnail Caching
     *
     * Pre-generate and cache thumbnails on disk to maximize performance and save CPU/RAM.
     */
    'thumbnails' => [
        // Cache thumbnails directly on disk
        'cache_on_disk' => true,

        // Hidden folder name for cached thumbnails
        'thumb_folder'  => '.thumbs',

        // Thumbnail dimensions in pixels
        'width'         => 120,
        'height'        => 120,
    ],

    /***************************************************************************
     * Security & Upload Restrictions (Inspired by UniSharp)
     *
     * Multi-layered protection against malicious uploads and Remote Code Execution (RCE).
     */
    'security' => [
        // Validate actual MIME types via PHP fileinfo
        'validate_mime'         => true,

        // Extra extensions to block (in addition to built-in dangerous extensions list)
        'disallowed_extensions' => [
            'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
            'html', 'htm', 'shtml', 'dhtml', 'xhtml',
            'exe', 'sh', 'bash', 'bat', 'cmd', 'ps1', 'cgi', 'pl', 'py', 'rb',
            'js', 'vbs', 'jar', 'apk', 'msi', 'htaccess', 'htpasswd', 'env'
        ],

        // Extra MIME types to block
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

    /***************************************************************************
     * Types & Directory Separation (UniSharp style)
     *
     * Allow separating directories and filtering by file types (e.g. images vs files).
     * When separate_types is true and ?type=images or ?type=files is passed:
     * - The file manager opens the corresponding subfolder ('images' or 'files')
     * - The view filters and displays only allowed files for that type
     * - Uploads are restricted to the allowed file types for that folder
     * When separate_types is false or no type query parameter is provided,
     * the unified directory and all allowed files are displayed (default behavior).
     */
    'separate_types' => false,

    'types' => [
        'images' => [
            'folder'             => 'images',
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'bmp', 'tiff'],
            'allowed_mimetypes'  => [
                'image/jpeg', 'image/pjpeg', 'image/png', 'image/gif',
                'image/webp', 'image/svg+xml', 'image/avif', 'image/bmp', 'image/tiff',
            ],
        ],
        'files' => [
            'folder'             => 'files',
            'allowed_extensions' => [], // Empty = allow all valid (non-blacklisted) extensions
            'allowed_mimetypes'  => [],
        ],
    ],
];
