<?php

namespace Duyld2108\LaravelFileManager\Services\ConfigService;

/**
 * Class DefaultConfigRepository
 *
 * @package Duyld2108\LaravelFileManager\Services\ConfigService
 */
class DefaultConfigRepository implements ConfigRepository
{
    /**
     * LFM Route prefix
     * !!! WARNING - if you change it, you should compile frontend with new prefix(baseUrl) !!!
     *
     * @return string
     */
    final public function getRoutePrefix(): string
    {
        return config('file-manager.routePrefix');
    }

    /**
     * Get disk list
     *
     * ['public', 'local', 's3']
     *
     * @return array
     */
    final public function getDiskList(): array
    {
        return config('file-manager.diskList');
    }

    /**
     * Default disk for left manager
     *
     * null - auto select the first disk in the disk list
     *
     * @return string|null
     */
    final public function getLeftDisk(): ?string
    {
        return config('file-manager.leftDisk');
    }

    /**
     * Default disk for right manager
     *
     * null - auto select the first disk in the disk list
     *
     * @return string|null
     */
    final public function getRightDisk(): ?string
    {
        return config('file-manager.rightDisk');
    }

    /**
     * Default path for left manager
     *
     * null - root directory
     *
     * @return string|null
     */
    final public function getLeftPath(): ?string
    {
        return config('file-manager.leftPath');
    }

    /**
     * Default path for right manager
     *
     * null - root directory
     *
     * @return string|null
     */
    final public function getRightPath(): ?string
    {
        return config('file-manager.rightPath');
    }

    /**
     * File manager modules configuration
     *
     * 1 - only one file manager window
     * 2 - one file manager window with directories tree module
     * 3 - two file manager windows
     *
     * @return int
     */
    final public function getWindowsConfig(): int
    {
        return config('file-manager.windowsConfig');
    }

    /**
     * File upload - Max file size in KB
     *
     * null - no restrictions
     */
    final public function getMaxUploadFileSize(): ?int
    {
        return config('file-manager.maxUploadFileSize');
    }

    /**
     * File upload - Allow these file types
     *
     * [] - no restrictions
     */
    final public function getAllowFileTypes(): array
    {
        return config('file-manager.allowFileTypes');
    }

    /**
     * Show / Hide system files and folders
     *
     * @return bool
     */
    final public function getHiddenFiles(): bool
    {
        return config('file-manager.hiddenFiles');
    }

    /**
     * Middleware
     *
     * Add your middleware name to array -> ['web', 'auth', 'admin']
     * !!!! RESTRICT ACCESS FOR NON ADMIN USERS !!!!
     *
     * @return array
     */
    final public function getMiddleware(): array
    {
        return config('file-manager.middleware');
    }

    /**
     * ACL mechanism ON/OFF
     *
     * default - false(OFF)
     *
     * @return bool
     */
    final public function getAcl(): bool
    {
        return config('file-manager.acl');
    }

    /**
     * Hide files and folders from file-manager if user doesn't have access
     *
     * ACL access level = 0
     *
     * @return bool
     */
    final public function getAclHideFromFM(): bool
    {
        return config('file-manager.aclHideFromFM');
    }

    /**
     * ACL strategy
     *
     * blacklist - Allow everything(access - 2 - r/w) that is not forbidden by the ACL rules list
     *
     * whitelist - Deny anything(access - 0 - deny), that not allowed by the ACL rules list
     *
     * @return string
     */
    final public function getAclStrategy(): string
    {
        return config('file-manager.aclStrategy');
    }

    /**
     * ACL rules repository
     *
     * default - config file(ConfigACLRepository)
     *
     * @return string
     */
    final public function getAclRepository(): string
    {
        return config('file-manager.aclRepository');
    }

    /**
     * ACL Rules cache
     *
     * null or value in minutes
     *
     * @return int|null
     */
    final public function getAclRulesCache(): ?int
    {
        return config('file-manager.aclRulesCache');
    }

    /**
     * Whether to slugify filenames
     *
     * boolean
     *
     * @return bool|null
     */
    final public function getSlugifyNames(): ?bool
    {
        return config('file-manager.slugifyNames', false);
    }

    final public function getImageOptimization(): array
    {
        return config('file-manager.image_optimization', [
            'enabled'                   => true,
            'format'                    => 'webp',
            'quality'                   => 85,
            'max_width'                 => 2048,
            'max_height'                => 2048,
            'keep_original_when_larger' => true,
        ]);
    }

    final public function getThumbnailsConfig(): array
    {
        return config('file-manager.thumbnails', [
            'cache_on_disk' => true,
            'thumb_folder'  => '.thumbs',
            'width'         => 120,
            'height'        => 120,
        ]);
    }

    final public function getSecurityConfig(): array
    {
        return config('file-manager.security', [
            'validate_mime'         => true,
            'disallowed_extensions' => [],
            'disallowed_mimetypes'  => [],
        ]);
    }

    final public function getSeparateTypes(): bool
    {
        return (bool) config('file-manager.separate_types', false);
    }

    final public function getTypesConfig(): array
    {
        return config('file-manager.types', [
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
                'allowed_extensions' => [],
                'allowed_mimetypes'  => [],
            ],
        ]);
    }

    final public function getTypeConfig(string $type): ?array
    {
        $types = $this->getTypesConfig();
        $normalized = strtolower(trim($type));
        if ($normalized === 'image') {
            $normalized = 'images';
        } elseif ($normalized === 'file') {
            $normalized = 'files';
        }

        return $types[$normalized] ?? null;
    }
}