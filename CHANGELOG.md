# Changelog

All notable changes to `duyld2108/laravel-file-manager` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.2] - 2026-10-25

### Changed
- **Reverted Entire Icon Set to Bootstrap Icons (1.11.3)**:
  - Switched back completely from Font Awesome to Bootstrap Icons (`bi bi-*`) across all Blade views, CSS, and the Vue SPA bundle (`file-manager.js`).
  - Restored clean Bootstrap Icons styling across context menu, navbar, folder tree, and all file type extensions (`bi-file-earmark-*`).

### Fixed
- **Grid View Up Level ('..') Overflow**:
  - Fixed oversized up-level button in Grid view that caused the first row to spill over and distort flex-wrap alignment.
  - Standardized `.fm-grid-item` dimensions (`125px` x `122px`) with flex-column alignment.
  - Centered `.fm-item-icon` within `70px` height and scaled `bi-arrow-90deg-up` to `2.5rem` with `line-height: 1`.
- **TinyMCE 5 / 6 / 7 Image Picker Callback**:
  - Fixed critical bug where selecting an image inserted raw HTML/link straight into the editor body canvas rather than into the TinyMCE "Insert/Edit Image" modal dialog.
  - Replaced `mceAction: 'insertContent'` with `mceAction: 'customAction'` and `mceAction: 'close'`, allowing TinyMCE's `file_picker_callback` (`onMessage(api, message)`) to receive the URL and populate the modal's "Source" input field.
  - Added multi-environment support for dialog iframes (`window.parent.postMessage`), popup windows (`window.opener.tinymceFilePickerCallback`), and legacy TinyMCE 4 `windowManager.getParams().setUrl()`.

### Added
- **UniSharp-Style Directory Separation & File Type Filtering (`separate_types`)**:
  - Added `separate_types` (default: `false` for full backward compatibility) and `types` configuration in `config/file-manager.php`.
  - When `separate_types` is enabled and `?type=images` (or `?type=files`) is passed from WYSIWYG editors:
    - Automatically opens the corresponding subfolder (`images/` or `files/`).
    - Filters displayed files to only allowed extensions for that type (e.g. only images in image mode).
    - Restricts uploads to permitted extensions/mimetypes for that category.
    - Persists active type in session across requests while keeping default unified directory management intact when disabled.
- **Enhanced About Modal**:
  - Renamed package in About modal to `"Laravel File Manager (Enhanced Edition)"` across all languages matching README.
  - Updated developer information block and package version to `1.0.2`.

---
## [1.0.1] - 2026-10-25

### Changed
- **Complete Font Awesome Migration**:
  - Replaced all 37 Bootstrap Icons (`bi bi-*`) across the compiled Vue.js SPA bundle (`file-manager.js`) with Font Awesome 6 icons (`fas fa-*`, `far fa-*`).
  - Replaced all context menu icons, tree branch expand/collapse icons, table/grid view icons, and all 118 file extension / MIME type icons with Font Awesome 6 standards (`fa-file-image`, `fa-file-alt`, `fa-file-code`, `fa-file-audio`, `fa-file-video`, `fa-file-archive`, `fa-file-pdf`, `fa-file-word`, `fa-file-excel`, `fa-file-powerpoint`, `fa-file`).
  - Replaced all `.bi` and `.bi-*` selectors in `file-manager.css` with `.fas`, `.far`, and `.fa-*` selectors.

### Removed
- **Bootstrap Icons Dependency**:
  - Removed `<link rel="stylesheet" href="...bootstrap-icons.min.css">` from all Blade templates (`index.blade.php`, `fmButton.blade.php`, `ckeditor.blade.php`, `summernote.blade.php`, `tinymce.blade.php`, `tinymce5.blade.php`).
  - Removed Bootstrap Icons setup instructions from documentation (`docs/installation.md`).

### Fixed
- **Bootstrap 5.3 Modal Background Bug**:
  - Resolved transparent / see-through `.modal-content` background bug caused by Bootstrap 5.3 CSS variables (`--bs-modal-bg: var(--bs-body-bg)`).
  - Enforced solid, crisp background colors (`#ffffff` in Light Mode, `#1e293b` in Dark Mode).
  - Added modern modal backdrop blur (`backdrop-filter: blur(4px)`), centered dialogs, and deep box shadows.
- **Bootstrap 4 to Bootstrap 5 Compatibility Shims**:
  - Added backward-compatibility shims for utilities removed in Bootstrap 5: `.text-right`, `.text-left`, `.float-left`, `.float-right`, `.btn-block`, `.form-group`, `.input-group-append`, `.input-group-prepend`, and legacy spacing classes (`.pl-0..5`, `.pr-0..5`, `.ml-0..5`, `.mr-0..5`).
  - Fixed dark mode hover state in Properties modal (`.fm-modal-properties .modal-body .row:hover`).
  - Fixed close button (`.btn-close`) inverted colors for dark mode.
- **UTF-8 BOM Elimination**:
  - Stripped UTF-8 Byte Order Marks from all PHP files to prevent PHP fatal syntax errors (`Namespace declaration statement has to be the very first statement`).

### Added
- **Standalone Root Route**:
  - Added `Route::get('/', [FileManagerController::class, 'index'])->name('fm.index');` and corresponding `index.blade.php` for direct `/file-manager` browser access.

---

## [1.0.0] - 2026-10-24

### Added
- **Intervention Image v4.1+ Support**:
  - Re-architected `ImageService.php` to support `intervention/image-laravel: ^4.1` and `intervention/image: ^4.0`.
  - Replaced deprecated `read()` method with transparent `decodeBinary()`, `scaleDown()`, and `encodeUsingFormat()`.
  - Maintained backward compatibility with Intervention Image v3.
- **Automated Image Resizing on Upload**:
  - Proportional downsizing of large images based on configurable `max_width` and `max_height`.
  - Non-destructive `scaleDown` algorithm preserving original aspect ratio.
- **Format Conversion (WebP & AVIF)**:
  - Automatic format conversion for raster uploads (`.jpg`, `.jpeg`, `.png`, `.bmp`) to `.webp` or `.avif`.
  - Configurable lossy compression quality with `keep_original_when_larger` safety check.
- **Disk-based Thumbnail Caching**:
  - Pre-generated 120x120px thumbnails saved into `.thumbs/` directory on storage disks.
  - Greatly reduces server CPU and RAM usage during folder browsing.
  - Automatic thumbnail deletion and renaming synchronization.
- **Multi-layered Security Hardening**:
  - Real MIME inspection via PHP `fileinfo` (`$file->getMimeType()`).
  - Executable upload blacklist blocking `.php*`, `.phtml`, `.phar`, `.html`, `.exe`, `.sh`, `.bat`, `.cmd`, `.cgi`, `.pl`, `.js`, `.env`, `.htaccess`.
  - Zip Slip vulnerability mitigation (CVE-2025-65346) verifying extraction paths.
- **Standalone Namespace & Architecture**:
  - Completely refactored all classes into independent `Duyld2108\LaravelFileManager` namespace.
  - Standalone `config/file-manager.php` with independent service provider registration.
- **System Compatibility**:
  - PHP: `^8.2`, `^8.3`, `^8.4`.
  - Laravel: `^10.0`, `^11.0`, `^12.0`, `^13.0`.
