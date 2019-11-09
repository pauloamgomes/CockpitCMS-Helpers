<div class="uk-margin comments">
    <div class="uk-margin-top">
        <label class="uk-text-small">@lang('Actions')</label>
        <div class="uk-margin-small-top">
            @if($permissions['entryCreate'])
            <div>
                <a href="/collections/entry/{collection.name}" target="_blank" class="uk-text-muted uk-text-nowrap uk-button uk-button-small uk-button-link">
                    <i class="uk-icon-plus-circle uk-icon-justify"></i>@lang('Create new') <i>{collection.label || collection.name}</i>
                </a>
            </div>
            @endif
            @if($permissions['entryEdit'])
            <div if="{entry && entry._id}">
                <button onclick="{ cloneEntry }" class="uk-text-muted uk-text-nowrap uk-button uk-button-small uk-button-link">
                    <i class="uk-icon-clone uk-icon-justify"></i>@lang('Duplicate')
                </button>
            </div>
            @endif
            @if($permissions['entryDelete'])
            <div if="{entry && entry._id}">
                <button onclick="{ deleteEntry }" class="uk-flex-item-1 uk-text-muted uk-text-nowrap uk-button uk-button-small uk-button-link uk-text-danger">
                    <i class="uk-icon-trash uk-icon-justify"></i>@lang('Delete')
                </button>
            </div>
            @endif
            @if($permissions['jsonView'])
            <div>
                <button onclick="{ showEntryJson }" class="uk-flex-item-1 uk-text-muted uk-text-nowrap uk-button uk-button-small uk-button-link">
                    <i class="uk-icon-code uk-icon-justify"></i>@lang('Inspect JSON')
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    var $this = this;
    this.permissions = {{ json_encode($permissions) }};
    this.expandedComment = null;
    this.entryComments = [];

    this.deleteEntry = function() {
        App.ui.confirm("Are you sure?", function() {
            App.request('/collections/delete_entries/'+$this.collection.name, {filter: {'_id':$this.entry._id}}).then(function(data) {
                if (data === 1) {
                    App.ui.notify('Collection entry removed!', 'success');
                    window.setTimeout(function() {
                        window.location.href = App.route('/collections/entries/' + $this.collection.name);
                    }, 500);
                }
            });
        });
    }

    this.cloneEntry = function() {
        var _entry = App.$.extend({}, $this.entry);
        delete _entry._id;

        App.request('/collections/save_entry/' + $this.collection.name, {"entry": _entry}).then(function(data) {
            if (data._id) {
                App.ui.notify('Collection entry duplicated!', 'success');
                window.setTimeout(function() {
                    window.open(App.route('/collections/entry/' + $this.collection.name + '/' + data._id));
                }, 500);
            }
        });
    }

    this.inspectEntry = function() {
        console.log('inspect', $this.entry._id);
    }

</script>
