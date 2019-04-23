<field-singletonselectlink>

    <select onchange="{changeOption}" ref="input" class="uk-width-1-1 {opts.cls}" value="{ value }">
        <option value="">{loading ? "loading..." : App.i18n.get("- Select -")}</option>
        <option each="{ option,idx in options }" value="{ option.value }" selected="{ value === option.value }">{ option.label }</option>
    </select>

    <script>

        var $this = this;
        this.options = [];
        this.value = '';

        this.on('mount', function() {
            this.refs.input.value = this.root.$value;
            limit = opts.limit || 25;
            group = opts.group || null;

            App.callmodule('helpers:getSingletons', [group, limit], 'singletonSelect').then(function(data) {
                if (data && data.result) {
                    data.result.forEach(function(singleton) {
                        $this.options.push(singleton);
                    })
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
            $this.value = e.target.value;
            $this.$setValue($this.value);
        }

    </script>

</field-singletonselectlink>
