<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="file-manager-type" content="{{ $type ?? request()->input('type', '') }}">

    <title>{{ config('app.name', 'File Manager') }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- Bootstrap Icons (1.11.3) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- File Manager CSS (with BS5.3 compatibility & dark mode layer) -->
    <link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">

    <style>
        :root {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: var(--bs-body-bg, #f8f9fa);
        }
        #fm-main-block {
            height: 100vh;
            padding: 0;
        }
        #fm {
            height: 100%;
            border-radius: 0;
        }
        .bi {
            display: inline-block;
            vertical-align: -0.125em;
        }
    </style>

    <script>
        // Automatic system dark / light mode detection
        (function() {
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-bs-theme', prefersDark ? 'dark' : 'light');
            if (window.matchMedia) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                    document.documentElement.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
                });
            }
        })();
    </script>
</head>
<body>
<div class="container-fluid p-0 h-100">
    <div class="row g-0 h-100">
        <div class="col-12 h-100" id="fm-main-block">
            <div id="fm"></div>
        </div>
    </div>
</div>

<!-- File manager bundle -->
<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    function resizeFm() {
        var el = document.getElementById('fm-main-block');
        if (el) el.style.height = window.innerHeight + 'px';
    }
    resizeFm();
    window.addEventListener('resize', resizeFm);

    // Forward type query parameter (e.g. ?type=images or ?type=files) to Axios headers
    var urlParams = new URLSearchParams(window.location.search);
    var typeParam = urlParams.get('type') || document.querySelector('meta[name="file-manager-type"]')?.content;
    if (typeParam && window.fm && window.fm.$store) {
        var currentHeaders = Object.assign({}, window.fm.$store.state.fm.settings.headers || {});
        currentHeaders['X-FileManager-Type'] = typeParam;
        window.fm.$store.commit('fm/settings/manualSettings', { headers: currentHeaders });
    }

    // TinyMCE 5 / 6 / 7 file picker callback
    if (window.fm && window.fm.$store) {
        window.fm.$store.commit('fm/setFileCallBack', function(fileUrl) {
            var filename = fileUrl.split('/').pop();

            // 1. If opened inside TinyMCE dialog iframe (window.parent)
            if (window.parent && window.parent !== window) {
                // Post custom message so tinymce openUrl onMessage(api, message) receives it:
                // callback(message.content, { text: message.text })
                // DO NOT use 'insertContent' which bypasses the modal dialog and inserts raw html straight into editor!
                window.parent.postMessage({
                    mceAction: 'customAction',
                    content: fileUrl,
                    url: fileUrl,
                    text: filename
                }, '*');

                // Close the openUrl file picker modal so user is returned to the image/file modal with the field populated
                window.parent.postMessage({
                    mceAction: 'close'
                }, '*');
            }

            // 2. If opened via popup window (window.opener)
            if (window.opener) {
                if (typeof window.opener.tinymceFilePickerCallback === 'function') {
                    window.opener.tinymceFilePickerCallback(fileUrl, { text: filename });
                } else if (window.opener.postMessage) {
                    window.opener.postMessage({
                        mceAction: 'customAction',
                        content: fileUrl,
                        url: fileUrl,
                        text: filename
                    }, '*');
                    window.opener.postMessage({ mceAction: 'close' }, '*');
                }
                window.close();
            }

            // 3. Fallback for TinyMCE 4 or custom windowManager handlers
            var win = (window.parent && window.parent !== window) ? window.parent : window.opener;
            if (win && win.tinymce && win.tinymce.activeEditor) {
                try {
                    var params = win.tinymce.activeEditor.windowManager.getParams();
                    if (params && typeof params.setUrl === 'function') {
                        params.setUrl(fileUrl);
                        win.tinymce.activeEditor.windowManager.close();
                    } else if (params && typeof params.oninsert === 'function') {
                        params.oninsert(fileUrl);
                        win.tinymce.activeEditor.windowManager.close();
                    }
                } catch (e) {}
            }
        });
    }
  });
</script>
</body>
</html>