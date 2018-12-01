<style>
.uk-modal-json .uk-modal-dialog {
  height: 85%;
}
</style>

<div class="uk-margin" if="{entry}">
    <label class="uk-text-small">@lang('Devel')</label>
    <div class="uk-margin-small-top">
    <a onclick="{ showEntryJson }" class="extrafields-indicator uk-text-nowrap">
      <i class="uk-icon-code uk-icon-justify"></i>@lang('JSON preview')
    </div>
</div>

<div class="uk-modal uk-modal-json uk-height-viewport">
  <div class="uk-modal-dialog uk-modal-dialog-large">
    <a href="" class="uk-modal-close uk-close"></a>
    <strong>@lang('JSON Preview')</strong>
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
    editor.setOption("readOnly", true);
    editor.setSize($this.modal.dialog[0].clientWidth - 50, $this.modal.dialog[0].clientHeight - 70);
    editor.refresh();
    $this.trigger('ready');
  }

</script>
