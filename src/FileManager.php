<?php

namespace Duyld2108\LaravelFileManager;

use Duyld2108\LaravelFileManager\Events\Deleted;
use Duyld2108\LaravelFileManager\Services\ConfigService\ConfigRepository;
use Duyld2108\LaravelFileManager\Services\ImageService;
use Duyld2108\LaravelFileManager\Services\UploadValidator;
use Duyld2108\LaravelFileManager\Services\TransferService\TransferFactory;
use Duyld2108\LaravelFileManager\Traits\CheckTrait;
use Duyld2108\LaravelFileManager\Traits\ContentTrait;
use Duyld2108\LaravelFileManager\Traits\PathTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManager
{
    use PathTrait, ContentTrait, CheckTrait;

    /**
     * @var ConfigRepository
     */
    public ConfigRepository $configRepository;

    /**
     * @var ImageService
     */
    public ImageService $imageService;

    /**
     * FileManager constructor.
     *
     * @param ConfigRepository $configRepository
     * @param ImageService|null $imageService
     */
    public function __construct(ConfigRepository $configRepository, ?ImageService $imageService = null)
    {
        $this->configRepository = $configRepository;
        $this->imageService = $imageService ?? app(ImageService::class);
    }

    /**
     * Initialize App
     *
     * @return array
     */
    /**
     * Determine active type from request or session (e.g. 'images' or 'files')
     *
     * @return string|null
     */
    public function getActiveType(): ?string
    {
        $type = request()->input('type') 
            ?: request()->header('X-FileManager-Type') 
            ?: session('file_manager_type');

        if (!$type) {
            return null;
        }

        $type = strtolower(trim((string) $type));
        if ($type === 'image') {
            $type = 'images';
        } elseif ($type === 'file') {
            $type = 'files';
        }

        return $type;
    }

    public function initialize(): array
    {
        if (!config()->has('file-manager')) {
            return [
                'result' => [
                    'status'  => 'danger',
                    'message' => 'noConfig',
                ],
            ];
        }

        $type = $this->getActiveType();
        $separateTypes = $this->configRepository->getSeparateTypes();
        $typeConfig = ($separateTypes && $type) ? $this->configRepository->getTypeConfig($type) : null;

        $leftPath = $this->configRepository->getLeftPath();
        $rightPath = $this->configRepository->getRightPath();

        // If separate_types is enabled and a type is active, ensure folder exists and default path
        if ($typeConfig && !empty($typeConfig['folder'])) {
            $folder = $typeConfig['folder'];
            $disk = $this->configRepository->getLeftDisk() ?: ($this->configRepository->getDiskList()[0] ?? 'public');
            if (array_key_exists($disk, config('filesystems.disks'))) {
                try {
                    if (!Storage::disk($disk)->exists($folder)) {
                        Storage::disk($disk)->makeDirectory($folder);
                    }
                } catch (\Throwable $e) {}
            }
            if (empty($leftPath)) {
                $leftPath = $folder;
            }
        }

        $config = [
            'acl'           => $this->configRepository->getAcl(),
            'leftDisk'      => $this->configRepository->getLeftDisk(),
            'rightDisk'     => $this->configRepository->getRightDisk(),
            'leftPath'      => $leftPath,
            'rightPath'     => $rightPath,
            'windowsConfig' => $this->configRepository->getWindowsConfig(),
            'hiddenFiles'   => $this->configRepository->getHiddenFiles(),
            'separateTypes' => $separateTypes,
            'activeType'    => $type,
        ];

        // disk list
        foreach ($this->configRepository->getDiskList() as $disk) {
            if (array_key_exists($disk, config('filesystems.disks'))) {
                $config['disks'][$disk] = Arr::only(
                    config('filesystems.disks')[$disk], ['driver']
                );
            }
        }

        // get language
        $config['lang'] = app()->getLocale();

        return [
            'result' => [
                'status'  => 'success',
                'message' => null,
            ],
            'config' => $config,
        ];
    }

    /**
     * Get files and directories for the selected path and disk
     *
     * @param $disk
     * @param $path
     *
     * @return array
     * @throws FilesystemException
     */
    public function content($disk, $path): array
    {
        $type = $this->getActiveType();
        $separateTypes = $this->configRepository->getSeparateTypes();
        $typeConfig = ($separateTypes && $type) ? $this->configRepository->getTypeConfig($type) : null;

        if ($typeConfig && !empty($typeConfig['folder']) && (empty($path) || $path === '/')) {
            $path = $typeConfig['folder'];
            try {
                if (!Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->makeDirectory($path);
                }
            } catch (\Throwable $e) {}
        }

        $content = $this->getContent($disk, $path, $typeConfig);

        return [
            'result'      => [
                'status'  => 'success',
                'message' => null,
            ],
            'directories' => $content['directories'],
            'files'       => $content['files'],
        ];
    }

    /**
     * Get part of the directory tree
     *
     * @param $disk
     * @param $path
     *
     * @return array
     * @throws FilesystemException
     */
    public function tree($disk, $path): array
    {
        $directories = $this->getDirectoriesTree($disk, $path);

        return [
            'result'      => [
                'status'  => 'success',
                'message' => null,
            ],
            'directories' => $directories,
        ];
    }

    /**
     * Upload files
     *
     * @param string|null $disk
     * @param string|null $path
     * @param array|null  $files
     * @param bool        $overwrite
     *
     * @return array
     */
    public function upload($disk, $path, $files, $overwrite): array
    {
        $fileNotUploaded = false;
        $optConfig = $this->configRepository->getImageOptimization();
        $securityConfig = $this->configRepository->getSecurityConfig();
        $thumbConfig = $this->configRepository->getThumbnailsConfig();
        $maxSize = $this->configRepository->getMaxUploadFileSize();
        $allowedTypes = $this->configRepository->getAllowFileTypes();

        $type = $this->getActiveType();
        $separateTypes = $this->configRepository->getSeparateTypes();
        $typeConfig = ($separateTypes && $type) ? $this->configRepository->getTypeConfig($type) : null;

        if ($typeConfig && !empty($typeConfig['folder']) && (empty($path) || $path === '/')) {
            $path = $typeConfig['folder'];
        }

        if ($typeConfig && !empty($typeConfig['allowed_extensions'])) {
            $allowedTypes = !empty($allowedTypes)
                ? array_values(array_intersect($allowedTypes, $typeConfig['allowed_extensions']))
                : $typeConfig['allowed_extensions'];
        }

        foreach ($files as $file) {
            // 1. Security & MIME validation (based on UniSharp multi-layered rules)
            try {
                UploadValidator::validate($file, $securityConfig, $allowedTypes, $maxSize);
            } catch (\Throwable $e) {
                Log::warning("LFM Upload rejected: " . $e->getMessage());
                $fileNotUploaded = true;
                continue;
            }

            $extension = strtolower((string) $file->getClientOriginalExtension());
            $nameWithoutExt = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            if ($this->configRepository->getSlugifyNames()) {
                $nameWithoutExt = Str::slug($nameWithoutExt);
            }

            $binaryContent = file_get_contents($file->getRealPath());

            // 2. Auto-resize & Format conversion (WebP / AVIF) for raster images
            $mimeType = strtolower((string) $file->getMimeType());
            $isImage = str_starts_with($mimeType, 'image/') && !in_array($extension, ['svg', 'ico'], true);

            if ($isImage && !empty($optConfig['enabled'])) {
                try {
                    $optResult = $this->imageService->optimizeImage($binaryContent, $extension, $optConfig);
                    $binaryContent = $optResult['binary'];
                    $extension = $optResult['extension'];
                } catch (\Throwable $e) {
                    Log::error("LFM Image optimization error: " . $e->getMessage());
                }
            }

            $finalName = $nameWithoutExt . '.' . $extension;
            $fullPath = $path ? $path . '/' . $finalName : $finalName;

            // Check if file already exists when overwrite is disabled
            if (!$overwrite && Storage::disk($disk)->exists($fullPath)) {
                $fileNotUploaded = true;
                continue;
            }

            // Save file
            Storage::disk($disk)->put($fullPath, $binaryContent);

            // 3. Pre-generate and cache thumbnail on disk
            if ($isImage && !empty($thumbConfig['cache_on_disk'])) {
                try {
                    $this->imageService->createAndStoreThumbnail($disk, $fullPath, $binaryContent, $thumbConfig);
                } catch (\Throwable $e) {
                    // Non-fatal, thumbnail will be generated on demand if needed
                }
            }
        }

        if ($fileNotUploaded) {
            return [
                'result' => [
                    'status'  => 'warning',
                    'message' => 'notAllUploaded',
                ],
            ];
        }

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'uploaded',
            ],
        ];
    }

    /**
     * Delete files and folders
     *
     * @param $disk
     * @param $items
     *
     * @return array
     */
    public function delete($disk, $items): array
    {
        $deletedItems = [];

        foreach ($items as $item) {
            if (!Storage::disk($disk)->exists($item['path'])) {
                continue;
            } else {
                if ($item['type'] === 'dir') {
                    Storage::disk($disk)->deleteDirectory($item['path']);
                } else {
                    Storage::disk($disk)->delete($item['path']);

                    // Also remove cached thumbnail if present
                    $thumbFolder = $this->configRepository->getThumbnailsConfig()['thumb_folder'] ?? '.thumbs';
                    $thumbPath = $this->imageService->getThumbPath($item['path'], $thumbFolder);
                    if (Storage::disk($disk)->exists($thumbPath)) {
                        Storage::disk($disk)->delete($thumbPath);
                    }
                }
            }

            $deletedItems[] = $item;
        }

        event(new Deleted($disk, $deletedItems));

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'deleted',
            ],
        ];
    }

    /**
     * Copy / Cut - Files and Directories
     *
     * @param $disk
     * @param $path
     * @param $clipboard
     *
     * @return array
     */
    public function paste($disk, $path, $clipboard): array
    {
        // compare disk names
        if ($disk !== $clipboard['disk']) {

            if (!$this->checkDisk($clipboard['disk'])) {
                return $this->notFoundMessage();
            }
        }

        $transferService = TransferFactory::build($disk, $path, $clipboard);

        return $transferService->filesTransfer();
    }

    /**
     * Rename file or folder
     *
     * @param $disk
     * @param $newName
     * @param $oldName
     *
     * @return array
     */
    public function rename($disk, $newName, $oldName): array
    {
        Storage::disk($disk)->move($oldName, $newName);

        // Rename corresponding thumbnail cache if exists
        $thumbFolder = $this->configRepository->getThumbnailsConfig()['thumb_folder'] ?? '.thumbs';
        $oldThumb = $this->imageService->getThumbPath($oldName, $thumbFolder);
        $newThumb = $this->imageService->getThumbPath($newName, $thumbFolder);
        if (Storage::disk($disk)->exists($oldThumb)) {
            Storage::disk($disk)->move($oldThumb, $newThumb);
        }

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'renamed',
            ],
        ];
    }

    /**
     * Download selected file
     *
     * @param $disk
     * @param $path
     *
     * @return StreamedResponse
     */
    public function download($disk, $path): StreamedResponse
    {
        // if file name not in ASCII format
        if (!preg_match('/^[\x20-\x7e]*$/', basename($path))) {
            $filename = Str::ascii(basename($path));
        } else {
            $filename = basename($path);
        }

        return Storage::disk($disk)->download($path, $filename);
    }

    /**
     * Create thumbnails (with Disk Cache)
     *
     * @param $disk
     * @param $path
     *
     * @return Response|mixed
     * @throws BindingResolutionException
     */
    public function thumbnails($disk, $path): mixed
    {
        $thumbBinary = $this->imageService->getThumbnail(
            $disk,
            $path,
            $this->configRepository->getThumbnailsConfig()
        );

        return response()->make(
            $thumbBinary,
            200,
            [
                'Content-Type'  => Storage::disk($disk)->mimeType($path) ?? 'image/jpeg',
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }

    /**
     * Image preview (Intervention Image v4.1 compatible)
     *
     * @param $disk
     * @param $path
     *
     * @return mixed
     * @throws BindingResolutionException
     */
    public function preview($disk, $path): mixed
    {
        $binary = Storage::disk($disk)->get($path);
        $encoded = (string) $this->imageService->decode($binary)->encode();

        return response()->make(
            $encoded,
            200,
            [
                'Content-Type'  => Storage::disk($disk)->mimeType($path) ?? 'image/jpeg',
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }

    /**
     * Get file URL
     *
     * @param $disk
     * @param $path
     *
     * @return array
     */
    public function url($disk, $path): array
    {
        return [
            'result' => [
                'status'  => 'success',
                'message' => null,
            ],
            'url'    => Storage::disk($disk)->url($path),
        ];
    }

    /**
     * Create new directory
     *
     * @param $disk
     * @param $path
     * @param $name
     *
     * @return array
     */
    public function createDirectory($disk, $path, $name)
    {
        $directoryName = $this->newPath($path, $name);

        if (Storage::disk($disk)->exists($directoryName)) {
            return [
                'result' => [
                    'status'  => 'warning',
                    'message' => 'dirExist',
                ],
            ];
        }

        Storage::disk($disk)->makeDirectory($directoryName);
        $directoryProperties = $this->directoryProperties(
            $disk,
            $directoryName
        );

        // add directory properties for the tree module
        $tree          = $directoryProperties;
        $tree['props'] = ['hasSubdirectories' => false];

        return [
            'result'    => [
                'status'  => 'success',
                'message' => 'dirCreated',
            ],
            'directory' => $directoryProperties,
            'tree'      => [$tree],
        ];
    }

    /**
     * Create new file
     *
     * @param $disk
     * @param $path
     * @param $name
     *
     * @return array
     */
    public function createFile($disk, $path, $name): array
    {
        $path = $this->newPath($path, $name);

        if (Storage::disk($disk)->exists($path)) {
            return [
                'result' => [
                    'status'  => 'warning',
                    'message' => 'fileExist',
                ],
            ];
        }

        Storage::disk($disk)->put($path, '');
        $fileProperties = $this->fileProperties($disk, $path);

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'fileCreated',
            ],
            'file'   => $fileProperties,
        ];
    }

    /**
     * Update file
     *
     * @param $disk
     * @param $path
     * @param $file
     *
     * @return array
     */
    public function updateFile($disk, $path, $file): array
    {
        Storage::disk($disk)->putFileAs(
            $path,
            $file,
            $file->getClientOriginalName()
        );

        $filePath       = $this->newPath($path, $file->getClientOriginalName());
        $fileProperties = $this->fileProperties($disk, $filePath);

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'fileUpdated',
            ],
            'file'   => $fileProperties,
        ];
    }

    /**
     * Stream file - for audio and video
     *
     * @param $disk
     * @param $path
     *
     * @return StreamedResponse
     */
    public function streamFile($disk, $path): StreamedResponse
    {
        // if file name not in ASCII format
        if (!preg_match('/^[\x20-\x7e]*$/', basename($path))) {
            $filename = Str::ascii(basename($path));
        } else {
            $filename = basename($path);
        }

        return Storage::disk($disk)->response($path, $filename, ['Accept-Ranges' => 'bytes']);
    }
}
