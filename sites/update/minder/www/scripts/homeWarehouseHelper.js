/**
 * @name Minder.Helper.HomeWarehouseSelect
 */
(function($){
    minderNamespace('Minder.Helper.HomeWarehouseSelect', function(){
        const WH_ID= 'WH_ID';
        const DESCRIPTION = 'DESCRIPTION';
        const LOCN_ID = 'LOCN_ID';
        const LOCN_NAME = 'LOCN_NAME';

        const HOME_WAREHOUSE_CHANGED = 'HOME_WAREHOUSE_CHANGED';

        var _defaults = {
            $homeWhId: $('#home_wh_id'),
            $homeLocnId: $('#home_locn_id'),
            homeWhId: '',
            homeLocnId: '',
	    homeWarehouses: (new Mdr.OptionsDataSource([]))
        };

        this.init = function(settings) {
            $.extend(this, _defaults, settings);

            this.$homeWhId.change($.proxy(this.onHomeWhIdChange, this));
            this.$homeLocnId.change($.proxy(this.onHomeLocnIdChange, this));
            
            fillHomeWarehouses(this.$homeWhId, this.homeWarehouses, this.homeWhId);
            filterHomeLocations.call(this, this.homeLocnId);
            disableOptions.call(this);
        };

        function filterHomeLocations(value) {
            var filter = {};

            if (this.$homeWhId.val()) {
                filter[WH_ID] = this.$homeWhId.val();            }

            this.homeWarehouses.setFilter(filter);
            fillHomeLocations(this.$homeLocnId, this.homeWarehouses, value);
        }

        this.onHomeWhIdChange = function(evt) {
            this.$homeWhId.val($(evt.target).val());
            filterHomeLocations.call(this, this.$homeLocnId.val());
            this.$homeLocnId.change();
        };


        this.onHomeLocnIdChange = function() {
            disableOptions.call(this);
        };

        this.addHomeWarehouseChangedListener = function(namespace, callback) {
            var fullName = HOME_WAREHOUSE_CHANGED + '.' + namespace;
            this.$homeWhId.unbind(fullName).bind(fullName, callback);
        };

        /**
         *
         * @param $container
         * @param {Mdr.OptionsDataSource} dataSource
         * @param value
         */
        function fillHomeWarehouses($container, dataSource, value) {
            if ($container.length < 1) {
                return;
            }
            $container.empty();

            $container.each(function(){
                dataSource.fillOptions(this, {
                    valueColumn: WH_ID,
                    labelColumn: DESCRIPTION,
		    addEmpty: false,
                    sortBy: DESCRIPTION
                });
            });

            $container.val(value);
            $container.trigger(HOME_WAREHOUSE_CHANGED);
        }

        function fillHomeLocations($container, dataSource, value) {
            if ($container.length < 1) {
                return;
            }
            $container.empty();
            dataSource.fillOptions($container.get(0), {
                valueColumn: LOCN_ID,
                labelColumn: LOCN_NAME,
		addEmpty: false,
                sortBy: LOCN_NAME
            });

            $container.val(value);
        }

        function disableOptions() {
            var tmpSource = new Mdr.OptionsDataSource(this.homeWarehouses.source),
                filter = {};

            if (this.$homeWhId.val()) {
                filter[WH_ID] = this.$homeWhId.val();
            }

            this.homeWarehouses.setFilter(filter);

            if (this.$homeWhId.val()) {
                fillHomeWarehouses(this.$homeWhId, tmpSource, this.$homeWhId.val());
            } else {
                fillHomeWarehouses(this.$homeWhId, this.homeWarehouses, this.$homeWhId.val());
            }

        }

    });
})(jQuery);
