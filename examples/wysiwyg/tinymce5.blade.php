<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <title>File Manager and TinyMCE 5 / 6 / 7</title>
</head>
<body>

<div class="container py-4">
    <h2 class="mb-4">TinyMCE 5 / 6 / 7 Integration with File Manager</h2>
    <form method="post">
        <textarea id="my-textarea" class="tinymce"></textarea>
    </form>
</div>

<!-- TinyMCE 5 / 6 CDN -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
      selector: '#my-textarea',
      height: 500,
      plugins: 'image link media table code preview fullscreen lists',
      toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media | code fullscreen',
      relative_urls: false,
      file_picker_callback: function(callback, value, meta) {
        var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
        var y = window.innerHeight || document.documentElement.clientHeight || document.getElementsByTagName('body')[0].clientHeight;

        // Automatically pass &type=images or &type=files based on TinyMCE picker type
        var cmsURL = '/file-manager/tinymce5?editor=' + meta.fieldname;
        if (meta.filetype === 'image') {
          cmsURL += '&type=images';
        } else {
          cmsURL += '&type=files';
        }

        tinyMCE.activeEditor.windowManager.openUrl({
          url: cmsURL,
          title: 'File Manager',
          width: x * 0.8,
          height: y * 0.8,
          resizable: 'yes',
          close_previous: 'no',
          onMessage: function(api, message) {
            // Populate the modal input field with selected file URL
            callback(message.content, { text: message.text });
          }
        });
      }
    });
  });
</script>
</body>
</html>