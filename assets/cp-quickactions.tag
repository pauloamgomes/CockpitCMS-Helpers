<cp-quickactions>
  <div data-uk-dropdown>
    <a class="uk-text-muted"><i class="uk-icon-ellipsis-h"></i></a>
    <div class="uk-dropdown">
      <ul class="uk-nav uk-nav-dropdown uk-dropdown-close" each="{group in actions}">
        <li class="uk-nav-header">{ group.group }</li>
        <li each="{ action in group.actions }">
          <a href="{ action.path }">{ action.label }</a>
        </li>
      </ul>
    </div>
  </div>

  <script>
    var $this = this;
    $this.actions = [];

    this.on('mount', function() {
      App.callmodule('helpers:getQuickActions', {}, 'quickactions').then(function(data) {
         if (data && data.result) {
          $this.actions = data.result;
         }
        $this.update();
       });
    });

  </script>

</cp-quickactions>
