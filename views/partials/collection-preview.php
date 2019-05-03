<script>
  this.previewUrl = {{ json_encode($previewUrl) }};
  this.previewToken = {{ json_encode($previewToken) }};

  this.on('mount', function() {
    contentpreview = this.collection.contentpreview;
    if (contentpreview && contentpreview.enabled) {
      url = new URL(contentpreview.url);
      if (this.previewToken) {
        this.collection.contentpreview.url = previewUrl + url.pathname + "?previewToken=" + previewToken ;
      } else {
        this.collection.contentpreview.url = previewUrl + url.pathname;
      }
    }
  });
</script>
