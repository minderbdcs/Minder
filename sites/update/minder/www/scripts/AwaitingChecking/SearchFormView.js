var SearchFormView = Backbone.View.extend({

    messageBus: null,

    initialize: function(options) {
        this.messageBus = options.messageBus;

        this.bindUiEvents();
    },

    bindUiEvents: function() {
        var giSearchMap = window.mdrGISearchMap || [],
            self = this;

        this.$el.delegate('#SEARCH_RESULTS_FORM_search_btn', 'click', $.proxy(this.onSearchButtonClick, this));
        $(document).bind('do-search', $.proxy(this.onDoSearch, this));

        giSearchMap.forEach(function(searchMapEntry){
            if (searchMapEntry.dataId) {
                self.messageBus.notifySubscribeToBarcodeNameRequest(self, self.onBarcodeName, searchMapEntry.dataId);
            }

            if (searchMapEntry.dataType) {
                self.messageBus.notifySubscribeToBarcodeTypeRequest(self, self.onBarcodeType, searchMapEntry.dataType);
            }
        });
    },

    onBarcodeType: function(dataIdentifier) {
        var giSearchMap = window.mdrGISearchMap || [],
            searchField = _.findWhere(giSearchMap, {dataType: dataIdentifier.param_type});

        if (!searchField) {
            return;
        }

        this.messageBus.notifyBarcodeServed();

        this._doSearch(searchField, dataIdentifier);
    },

    onBarcodeName: function(dataIdentifier) {
        var giSearchMap = window.mdrGISearchMap || [],
            searchField = _.findWhere(giSearchMap, {dataId: dataIdentifier.param_name});

        if (!searchField) {
            return;
        }

        this.messageBus.notifyBarcodeServed();

        this._doSearch(searchField, dataIdentifier);
    },

    _doSearch: function(searchField, dataIdentifier) {
        var searchEvent;

        searchEvent = $.Event('do-search');
        searchEvent.searchParams = {};
        searchEvent.searchParams[searchField.fieldName] = dataIdentifier.param_filtered_value;
        $(document).trigger(searchEvent);
    },

    onSearchButtonClick: function(evt) {
        evt.preventDefault();

        var searchEvent = $.Event('do-search');
        searchEvent.searchParams = {};

        $.each(this.$el.serializeArray(), function(index, item){
            searchEvent.searchParams[item.name] = item.value;
        });

        $(document).trigger(searchEvent);
    },

    onDoSearch: function(evt) {
        var searchParams = evt.searchParams;
        searchParams.action = 'search';

        this.messageBus.notifyExecuteSearch(searchParams);
    }
});