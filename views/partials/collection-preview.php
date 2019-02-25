<script>
  this.previewUrl = {{ json_encode($previewUrl) }};

  this.on('mount', function() {
    contentpreview = this.collection.contentpreview;
    if (contentpreview && contentpreview.enabled) {
      url = new URL(contentpreview.url);
      this.collection.contentpreview.url = previewUrl + url.pathname;
    }
  });
</script>