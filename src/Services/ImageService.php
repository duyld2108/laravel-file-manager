<?php

namespace Duyld2108\LaravelFileManager\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Throwable;

class ImageService
{
    /**
     * Decode image binary with support for Intervention Image v4.x and v3.x
     *
     * @param string $binaryContent
     * @return mixed
     */
    public function decode(string $binaryContent): mixed
    {
        $facadeRoot = Image::getFacadeRoot() ?? Image::class;

        if (method_exists($facadeRoot, 'decodeBinary')) {
            return Image::decodeBinary($binaryContent);
        }

        if (method_exists($facadeRoot, 'decode')) {
            return Image::decode($binaryContent);
        }

        return Image::read($binaryContent);
    }

    /**
     * Optimize uploaded image (Auto Resize + Format Conversion + Quality Compression)
     *
     * @param string $binaryContent
     * @param string $originalExtension
     * @param array $config
     * @return array ['binary' => string, 'extension' => string, 'optimized' => bool]
     */
    public function optimizeImage(string $binaryContent, string $originalExtension, array $config): array
    {
        try {
            $image = $this->decode($binaryContent);
        } catch (Throwable $e) {
            // If image decoding fails (e.g. unsupported vector or corrupted image), keep original
            return [
                'binary'    => $binaryContent,
                'extension' => $originalExtension,
                'optimized' => false,
            ];
        }

        $originalSize = strlen($binaryContent);

        // 1. Auto-resize (scaleDown: maintains aspect ratio, never upscales)
        $maxWidth = !empty($config['max_width']) ? (int) $config['max_width'] : null;
        $maxHeight = !empty($config['max_height']) ? (int) $config['max_height'] : null;

        if ($maxWidth || $maxHeight) {
            if (method_exists($image, 'scaleDown')) {
                $image->scaleDown($maxWidth, $maxHeight);
            } elseif (method_exists($image, 'scale')) {
                $image->scale($maxWidth, $maxHeight);
            }
        }

        // 2. Format conversion & Quality compression
        $targetFormat = !empty($config['format']) ? strtolower((string) $config['format']) : null;
        $quality = !empty($config['quality']) ? max(1, min(100, (int) $config['quality'])) : 85;
        $targetExtension = $originalExtension;

        if ($targetFormat && in_array($targetFormat, ['webp', 'avif', 'jpg', 'jpeg', 'png'], true)) {
            $targetExtension = $targetFormat === 'jpeg' ? 'jpg' : $targetFormat;
        }

        $encodedBinary = $this->encodeToFormat($image, $targetFormat ?: $originalExtension, $quality);

        // 3. Keep original if converted binary is larger (when keep_original_when_larger is true)
        if (!empty($config['keep_original_when_larger']) && strlen($encodedBinary) >= $originalSize) {
            return [
                'binary'    => $binaryContent,
                'extension' => $originalExtension,
                'optimized' => false,
            ];
        }

        return [
            'binary'    => $encodedBinary,
            'extension' => $targetExtension,
            'optimized' => true,
        ];
    }

    /**
     * Encode image using Intervention Image v4 (Format enum) or fallback media types
     *
     * @param mixed $image
     * @param string $formatName
     * @param int $quality
     * @return string
     */
    public function encodeToFormat(mixed $image, string $formatName, int $quality = 85): string
    {
        $formatName = strtolower($formatName);

        // Check Intervention Image v4 Format enum
        if (enum_exists('\Intervention\Image\Format') && method_exists($image, 'encodeUsingFormat')) {
            $formatEnum = match ($formatName) {
                'webp'        => \Intervention\Image\Format::WEBP,
                'avif'        => \Intervention\Image\Format::AVIF,
                'jpg', 'jpeg' => \Intervention\Image\Format::JPEG,
                'png'         => \Intervention\Image\Format::PNG,
                'gif'         => \Intervention\Image\Format::GIF,
                'bmp'         => \Intervention\Image\Format::BMP,
                default       => null,
            };

            if ($formatEnum !== null) {
                if (in_array($formatName, ['webp', 'avif', 'jpg', 'jpeg'])) {
                    return (string) $image->encodeUsingFormat($formatEnum, quality: $quality);
                }
                return (string) $image->encodeUsingFormat($formatEnum);
            }
        }

        // Fallback to Media Type methods (v3 / v4 compatible)
        $mimeType = match ($formatName) {
            'webp'        => 'image/webp',
            'avif'        => 'image/avif',
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'bmp'         => 'image/bmp',
            default       => null,
        };

        if ($mimeType) {
            if (method_exists($image, 'encodeUsingMediaType')) {
                return (string) $image->encodeUsingMediaType($mimeType, quality: $quality);
            }
            if (method_exists($image, 'encodeByMediaType')) {
                return (string) $image->encodeByMediaType($mimeType, quality: $quality);
            }
        }

        // Generic encode
        return (string) $image->encode();
    }

    /**
     * Get relative thumbnail path inside .thumbs directory
     *
     * @param string $path
     * @param string $thumbFolder
     * @return string
     */
    public function getThumbPath(string $path, string $thumbFolder = '.thumbs'): string
    {
        $dir = dirname($path);
        $filename = basename($path);

        if ($dir === '.' || empty($dir) || $dir === '/') {
            return $thumbFolder . '/' . $filename;
        }

        return $dir . '/' . $thumbFolder . '/' . $filename;
    }

    /**
     * Create and store thumbnail on disk cache
     *
     * @param string $disk
     * @param string $path
     * @param string $binaryContent
     * @param array $thumbConfig
     * @return string Raw binary of thumbnail
     */
    public function createAndStoreThumbnail(
        string $disk,
        string $path,
        string $binaryContent,
        array $thumbConfig = []
    ): string {
        $image = $this->decode($binaryContent);
        $width = !empty($thumbConfig['width']) ? (int) $thumbConfig['width'] : 120;
        $height = !empty($thumbConfig['height']) ? (int) $thumbConfig['height'] : 120;

        if (method_exists($image, 'coverDown')) {
            $image->coverDown($width, $height);
        } elseif (method_exists($image, 'cover')) {
            $image->cover($width, $height);
        }

        $thumbBinary = (string) $image->encode();

        if (!empty($thumbConfig['cache_on_disk'])) {
            $thumbFolder = $thumbConfig['thumb_folder'] ?? '.thumbs';
            $thumbPath = $this->getThumbPath($path, $thumbFolder);
            try {
                Storage::disk($disk)->put($thumbPath, $thumbBinary);
            } catch (Throwable $e) {
                // Log and continue gracefully
            }
        }

        return $thumbBinary;
    }

    /**
     * Retrieve thumbnail from disk cache or generate and cache it
     *
     * @param string $disk
     * @param string $path
     * @param array $thumbConfig
     * @return string Raw binary of thumbnail
     */
    public function getThumbnail(string $disk, string $path, array $thumbConfig = []): string
    {
        $thumbFolder = $thumbConfig['thumb_folder'] ?? '.thumbs';
        $thumbPath = $this->getThumbPath($path, $thumbFolder);

        // Check if thumbnail exists in disk cache
        if (!empty($thumbConfig['cache_on_disk']) && Storage::disk($disk)->exists($thumbPath)) {
            return Storage::disk($disk)->get($thumbPath);
        }

        // Generate from original
        $originalBinary = Storage::disk($disk)->get($path);
        return $this->createAndStoreThumbnail($disk, $path, $originalBinary, $thumbConfig);
    }
}
