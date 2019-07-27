<field-collectionselect>

    <select onchange="{changeOption}" show="{element == 'select'}" ref="input" class="uk-width-1-1 {opts.cls}" value="{ value }">
        <option value="">{loading ? "loading..." : App.i18n.get("- Select -")}</option>
        <option each="{ option,idx in Object.values(options) }" value="{ option._id }" selected="{ value && value._id && value._id === option._id }">{ option.display }</option>
    </select>

    <script>

        var $this = this;
        this.options = {};
        this.element = 'select';
        this.value = {
            _id: '',
            type: '',
            display: '',
        };

        this.on('mount', function() {
            this.refs.input.value = this.root.$value;
            this.element = opts.element || 'select';
            limit = opts.limit || 25;

            App.callmodule('helpers:getCollectionEntries', [opts.link, limit], 'collectionSelect').then(function(data) {
                if (data && data.result) {
                    display = opts.display || 'title';
                    data.result.forEach(function(entry) {
                        if (entry._id && entry[display]) {
                            $this.options[entry._id] = {
                                _id: entry._id,
                                link: opts.link,
                                display: entry[display]
                            };
                        }
                    });
                    $this.loading = false;
                    $this.update();
                }
            });

            this.update();
        });

        this.on('update', function() {
            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }
        });

        this.$updateValue = function(value, field) {
            this.value = value;
            this.update();
        }.bind(this);

        changeOption(e) {
            if ($this.options && $this.options[e.target.value]) {
                $this.value = $this.options[e.target.value];
                $this.$setValue($this.value);
            }
        }

    </script>

</field-collectionselect>
