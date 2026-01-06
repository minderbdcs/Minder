/**
 * @name Minder.Helper.SsnTypeSelect
 */
(function($){
    minderNamespace('Minder.Helper.SsnTypeSelect', function(){
        const SSN_TYPE_CODE = 'SSN_TYPE_CODE';
        const SSN_TYPE_DESCRIPTION = 'SSN_TYPE_DESCRIPTION';
        const GENERIC_CODE = 'GENERIC_CODE';
        const GENERIC_DESCRIPTION = 'GENERIC_DESCRIPTION';
        const SSN_SUB_TYPE_CODE = 'SSN_SUB_TYPE_CODE';
        const SSN_SUB_TYPE_DESCRIPTION = 'SSN_SUB_TYPE_DESCRIPTION';

        const SSN_TYPE_CHANGED = 'SSN_TYPE_CHANGED';

        var _defaults = {
            $ssnType: $('#ssn_type'),
            $generic: $('#generic'),
            $ssnSubType: $('#ssn_sub_type'),
            ssnType: '',
            generic: '',
            ssnSubType: '',
            ssnSubTypes: (new Mdr.OptionsDataSource([]))
        };

        this.init = function(settings) {
            $.extend(this, _defaults, settings);

            this.$ssnType.change($.proxy(this.onSsnTypeChange, this));
            this.$generic.change($.proxy(this.onGenericChange, this));
            this.$ssnSubType.change($.proxy(this.onSsnSubTypeChange, this));

            fillSsnTypes(this.$ssnType, this.ssnSubTypes, this.ssnType);
            filterGenerics.call(this, this.generic);
            filterSsnSubTypes.call(this, this.ssnSubType);
            disableOptions.call(this);
        };

        function filterGenerics(value) {
            var filter = {};

            if (this.$ssnType.val()) {
                filter[SSN_TYPE_CODE] = this.$ssnType.val();
            }

            this.ssnSubTypes.setFilter(filter);
            fillGenerics(this.$generic, this.ssnSubTypes, value);
        }

        this.onSsnTypeChange = function(evt) {
            this.$ssnType.val($(evt.target).val());
            filterGenerics.call(this, this.$generic.val());
            this.$generic.change();
        };

        function filterSsnSubTypes(value) {
            var filter = {};

            if (this.$ssnType.val()) {
                filter[SSN_TYPE_CODE] = this.$ssnType.val();
            }

            if (this.$generic.val()) {
                filter[GENERIC_CODE] = this.$generic.val();
            }

            this.ssnSubTypes.setFilter(filter);

            fillSubTypes(this.$ssnSubType, this.ssnSubTypes, value);
        }

        this.onGenericChange = function() {
            filterSsnSubTypes.call(this, this.$ssnSubType.val());
            this.$ssnSubType.change();
        };

        this.onSsnSubTypeChange = function() {
            disableOptions.call(this);
        };

        this.addSsnTypeChangedListener = function(namespace, callback) {
            var fullName = SSN_TYPE_CHANGED + '.' + namespace;
            this.$ssnType.unbind(fullName).bind(fullName, callback);
        };

        /**
         *
         * @param $container
         * @param {Mdr.OptionsDataSource} dataSource
         * @param value
         */
        function fillSsnTypes($container, dataSource, value) {
            if ($container.length < 1) {
                return;
            }
            $container.empty();

            $container.each(function(){
                dataSource.fillOptions(this, {
                    valueColumn: SSN_TYPE_CODE,
                    labelColumn: SSN_TYPE_DESCRIPTION,
                    sortBy: SSN_TYPE_DESCRIPTION
                });
            });

            $container.val(value);
            $container.trigger(SSN_TYPE_CHANGED);
        }

        function fillGenerics($container, dataSource, value) {
            if ($container.length < 1) {
                return;
            }
            $container.empty();
            dataSource.fillOptions($container.get(0), {
                valueColumn: GENERIC_CODE,
                labelColumn: GENERIC_DESCRIPTION,
                sortBy: GENERIC_DESCRIPTION
            });

            $container.val(value);
        }

        function fillSubTypes($container, dataSource, value) {
            if ($container.length < 1) {
                return;
            }
            $container.empty();
            dataSource.fillOptions($container.get(0), {
                valueColumn: SSN_SUB_TYPE_CODE,
                labelColumn: SSN_SUB_TYPE_DESCRIPTION,
                sortBy: SSN_SUB_TYPE_DESCRIPTION
            });

            $container.val(value);
        }

        function disableOptions() {
            var tmpSource = new Mdr.OptionsDataSource(this.ssnSubTypes.source),
                filter = {};

            if (this.$ssnType.val()) {
                filter[SSN_TYPE_CODE] = this.$ssnType.val();
            }

            if (this.$generic.val()) {
                filter[GENERIC_CODE] = this.$generic.val();
            }

            if (this.$ssnSubType.val()) {
                filter[SSN_SUB_TYPE_CODE] = this.$ssnSubType.val();
            }

            this.ssnSubTypes.setFilter(filter);

            if (this.$ssnType.val()) {
                fillSsnTypes(this.$ssnType, tmpSource, this.$ssnType.val());
            } else {
                fillSsnTypes(this.$ssnType, this.ssnSubTypes, this.$ssnType.val());
            }

            if (this.$generic.val()) {
                if (this.$ssnType.val()) {
                    tmpSource.setFilter({SSN_TYPE_CODE: this.$ssnType.val()});
                }
                fillGenerics(this.$generic, tmpSource, this.$generic.val());
            } else {
                fillGenerics(this.$generic, this.ssnSubTypes, this.$generic.val());
            }
        }

    });
})(jQuery);