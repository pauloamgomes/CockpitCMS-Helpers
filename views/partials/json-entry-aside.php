<style>
.uk-modal-json .uk-modal-dialog {
  height: 85%;
}
.button-update {
  float: right;
}
</style>

<div class="uk-modal uk-modal-json uk-height-viewport">
  <div class="uk-modal-dialog uk-modal-dialog-large">
    <a href="" class="uk-modal-close uk-close"></a>
    <strong>@lang('JSON Data')</strong>
    <button if="{permissions.jsonEdit}" onclick="{updateData}" class="button-update uk-button uk-button-small uk-button-primary uk-margin-right">
      <i class="uk-icon-save"></i> @lang('Update')</button>
    <div class="uk-margin uk-flex uk-flex-middle" if="{entry}">
      <codemirror ref="codemirrorjson" syntax="json"></codemirror>
    </div>
  </div>
</div>


<script>
  var $this = this;

  this.on('mount', function() {
    $this.modal = UIkit.modal(App.$('.uk-modal-json', this.root), {modal:true});
    $this.update();
  });

  this.showEntryJson = function() {
    $this.modal.show();
    editor = $this.refs.codemirrorjson.editor;
    editor.setValue(JSON.stringify($this.entry, null, 2), true);
    editor.setOption("readOnly", !$this.permissions.jsonEdit);
    editor.setSize($this.modal.dialog[0].clientWidth - 50, $this.modal.dialog[0].clientHeight - 70);
    editor.refresh();
    $this.trigger('ready');
  }

  this.updateData = function(e) {
    editor = $this.refs.codemirrorjson.editor;
    try {
      _.extend($this.entry, JSON.parse(editor.getValue()));
      $this.update();

      var elements = App.$('cp-field[type=wysiwyg]');
      elements.each(function(key, el) {
        var id = App.$(el).find('textarea').first().attr('id');
        tinyMCE.get(id).setContent(el.$getValue());
      });

      $this.modal.hide();
      $this.update();
      App.ui.notify(App.i18n.get("Collection entry data updated! Save to persist changes."));
    } catch(e) {
      App.ui.notify(App.i18n.get("Cannot update! Invalid json structure"), "danger");
      return;
    }
  }


</script>
