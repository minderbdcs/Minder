//todo: disable row selection
var EdiLinesView = (function() {
    var RecordMap = Backbone.Model.extend({
        idAttribute: 'RECORD_ID',

        initialize: function() {
            this.map = {};
            this.heap = [];
        }
    });

    var RecordMapCollection = Backbone.Collection.extend({
        model: RecordMap,

        addEntry: function(entryData) {
            var recordMap = this.add({'RECORD_ID': entryData.RECORD_ID});
            if (!recordMap.map[entryData.ROW_ID]) {
                recordMap.heap.push(entryData);
                recordMap.map[entryData.ROW_ID] = entryData;
            }
        }
    });


    return Backbone.View.extend({
        messageBus: null,
        pageController: null,
        startSsccCheckUrl: '',
        cancelSsccCheckUrl: '',
        acceptUrl: '',

        active: false,

        initialize: function(options) {
            this.messageBus = options.messageBus;
            this.startSsccCheckUrl = options.startSsccCheckUrl;
            this.cancelSsccCheckUrl = options.cancelSsccCheckUrl;
            this.acceptUrl = options.acceptUrl;
            this.recordMap = new RecordMapCollection();

            this.pageController = $.extend({}, Minder.AbstractPageController);
            this.pageController.initScreenVarianceSearchResults(options.searchResults, true);
            this.pageController.onDataReady(options.data);
            this.pageController.getLoadUrl = function(){return options.loadUrl;};
            this.pageController.getRowSelectUrl = function(){return options.selectRowUrl;};
            this.pageController.setDataRenderedListener('edi-view', $.proxy(this.onDataRendered, this));

            this.bindMessageBusEvents();
        },

        bindMessageBusEvents: function() {
            this.messageBus.onOrdersBeforeSearch(this.onOrdersBeforeSearch, this);
            this.messageBus.onLoadEdiLinesRequest(this.onLoadEdiLinesRequest, this);
            this.messageBus.onStartSsccCheckRequest(this.onStartSsccCheckRequest, this);
            this.messageBus.onPackSsccUpdated(this.onPackSsccUpdated, this);
            this.messageBus.onPackSsccListStatusUpdateRequest(this.onPackSsccListStatusUpdateRequest, this);
            this.messageBus.onEdiOnePackAcceptRequest(this.onEdiOnePackAcceptRequest, this);
            this.messageBus.onOrdersSelectionChanged(this.onOrdersSelectionChanged, this);
            this.messageBus.onCheckingStrategyEdiAll(this.onCheckingStrategyEdiAll, this);
            this.messageBus.onCheckingStrategyEdiOne(this.onCheckingStrategyEdiOne, this);
            this.messageBus.onCancelSsccCheckRequest(this.onCancelSsccCheckRequest, this);
        },

        onCheckingStrategyEdiAll: function() {
            this.$el.hide();
        },

        onCheckingStrategyEdiOne: function() {
            this.$el.show();
        },

        onOrdersSelectionChanged: function(orders) {
            if (orders.getSelectedAmount() < 1) {
                this.$el.hide();
            }
        },

        _doPackSsccStatusUpdate: function(packSscc) {
            var mapEntry = this.recordMap.get(packSscc.get('RECORD_ID')),
                $orderLines = this.$('.data-set').find('tr');

            if (mapEntry) {
                mapEntry.heap.forEach(function(entry){
                    var $lines = $orderLines.filter('.ROW-ID-' + entry.ROW_ID);
                    if (packSscc.isChecking()) {
                        $lines.removeClass('bad-line');
                        if (packSscc.getUncheckedQty() > 0) {
                            $lines.addClass('unchecked');
                        } else {
                            $lines.removeClass('unchecked');
                        }
                    } else {
                        $lines.addClass('bad-line');
                    }
                });
            }
        },

        onPackSsccListStatusUpdateRequest: function(packSsccList) {
            var self = this;
            packSsccList.forEach(function(packSscc) {
                self._doPackSsccStatusUpdate(packSscc);
            });
        },

        onPackSsccUpdated: function(packSscc) {
            this._doPackSsccStatusUpdate(packSscc);
        },

        onDataRendered: function(event) {
            var self = this;

            this.recordMap.reset();

            event.response.recordIdMap.forEach(function(entry){
                self.recordMap.addEntry(entry);
            });

            this.messageBus.notifyEdiDespatchDataChanged(event.response);
        },

        onLoadEdiLinesRequest: function() {
            this.active = true;
            this.pageController.showAll();
            this.pageController.loadData(this.pageController.getRegisteredNamespaces());
        },

        onOrdersBeforeSearch: function() {
            this.active = false;
            this.pageController.hideAll();
        },

        onStartSsccCheckRequest: function(ssccLabel) {
            var dataToSend = this.pageController.prepareLoadDataToSend(this.pageController.getRegisteredNamespaces());
            dataToSend['sscc'] = ssccLabel;
            $.post(this.startSsccCheckUrl, dataToSend, $.proxy(this.startSsccCheckCallback, this), 'json');
        },

        startSsccCheckCallback: function(response) {
            this.pageController.onDataReady(response);
        },

        onCancelSsccCheckRequest: function(ssccLabel) {
            var dataToSend = this.pageController.prepareLoadDataToSend(this.pageController.getRegisteredNamespaces());
            dataToSend['sscc'] = ssccLabel;
            $.post(this.cancelSsccCheckUrl, dataToSend, $.proxy(this.startSsccCheckCallback, this), 'json');
        },

        cancelSsccCheckCallback: function(response) {
            this.pageController.onDataReady(response);
        },

        onEdiOnePackAcceptRequest: function(acceptRequest) {
            _.extend(acceptRequest, this.pageController.prepareLoadDataToSend(this.pageController.getRegisteredNamespaces()));

            $.post(
                this.acceptUrl,
                acceptRequest,
                $.proxy(this.acceptRequestCallback, this),
                'json'
            );
        },

        acceptRequestCallback: function(response) {
            this.pageController.onDataReady(response);
        }

    });
})();