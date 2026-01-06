var OrdersView = Backbone.View.extend(_.extend({
    messageBus: null,
    reportUrl: '',
    loadUrl: '',
    _weightIsRequired: false,
    _volumeIsRequired: false,
    _shouldCheckEachLine: false,
    _shouldCreateSscc: false,
    _canBeChecked: false,
    _isEdi: false,
    _shouldRecordSerialNumber: false,

    _ediOrderStatistics: {

    },

    selection: {
        url: '',
        namespace: '',
        action: '',
        controller: ''
    },

    initialize: function(options) {
        this.messageBus = options.messageBus;
        this.reportUrl = options.reportUrl;
        this.loadUrl = options.loadUrl;
        this.selection = _.extend({}, this.selection, options.selection);

        this.showEmbedMessages();
        this.bindUiEvents();
        this.bindMessageBusEvents();

        this.initUi();
        this.messageBus.notifyOrdersSelectionChanged(this);
    },

    bindMessageBusEvents: function() {
        this.messageBus.onCancelDespatch(this.onCancelDespatch, this);
        this.messageBus.onExecuteOrderSearchRequest(this.onExecuteOrderSearchRequest, this);
        this.messageBus.onLoadOrderRequest(this.onLoadOrderRequest, this);
        this.messageBus.onReLoadOrderRequest(this.onReLoadOrderRequest, this);
        this.messageBus.onEdiOneOrderStatisticsChanged(this.onEdiOneOrderStatisticsChanged, this);
        this.messageBus.onOrdersSelectionChanged(this.onOrdersSelectionChanged, this);
        this.messageBus.onCheckingStrategyEdiAll(this.onCheckingStrategyEdiAll, this);
        this.messageBus.onCheckingStrategyEdiOne(this.onCheckingStrategyEdiOne, this);
    },

    initUi: function() {
        this._shouldCheckEachLine = this.$('[name="shouldCheckEachLine"]').val() === 'true';
        this._shouldRecordSerialNumber = this.$('[name="shouldRecordSerialNumber"]').val() === 'true';
        this._shouldCreateSscc = this.$('[name="shouldCreateSSCC"]').val() === 'true';
        this._canBeChecked = this.$('[name="canBeChecked"]').val() === 'true';
        this._volumeIsRequired = this.$('[name="volumeIsRequired"]').val() === 'true';
        this._weightIsRequired = this.$('[name="weightIsRequired"]').val() === 'true';
        this._isEdi = this.$('[name="isEdi"]').val() === 'true';
        this._ediOrderStatistics = JSON.parse(this.$('.ediOrderStatistics').text() || "{}");

        this.$('#order_tabs > ul').tabs();
        this.$('.pick_orders').tablesorter({headers:{0:{sorter:false}}, widgets: ['zebra']});

        this.$('.row_selector, .select_all_rows').minderRowSelector(
            this.selection.url,
            {
                'scopeSelector'       : '#order_tabs',
                'selectionNamespace'  : this.selection.namespace,
                'selectionAction'     : this.selection.action,
                'selectionController' : this.selection.controller
            },
            {
                'onSuccess' : $.proxy(this.rowSelectionCallback, this)
            }
        );

        this.updateSelectionState();
    },

    rowSelectionCallback: function(response) {
        this._weightIsRequired      = response.weightRequired;
        this._volumeIsRequired      = response.volumeRequired;
        this._shouldCheckEachLine   = response.shouldCheckEachLine;
        this._shouldRecordSerialNumber = response.shouldRecordSerialNumber;
        this._shouldCreateSscc      = response.shouldCreateSSCC;
        this._canBeChecked          = response.canBeChecked;
        this._isEdi                 = response.isEdi;

        this.messageBus.notifyOrdersSelectionChanged(this);
    },

    resetSearch: function() {
        this.$el.html('');
        this.$el.load(
            this.loadUrl,
            {'action' : 'CANCEL-DESPATCH'},
            $.proxy(this.resetSearchCallback, this)
        );
    },

    resetSearchCallback: function() {
        this.showEmbedMessages();
        this.initUi();
        this.messageBus.notifyOrdersSelectionChanged(this);
    },

    bindUiEvents: function() {
        this.$el.delegate('#order_report_csv_btn, #order_report_xls_btn', 'click', $.proxy(this.onReportButtonClick, this));
        this.$el.delegate('[name="show_by"], [name="pageselector"]', 'change', $.proxy(this.onPaginationChange, this));
        this.$el.delegate('#find_order_by_prod_id', 'click', $.proxy(this.onFindOrderByProdIdClick, this));
        this.$el.delegate('[name="orderCheckMethod"]', 'change', $.proxy(this.onOrderCheckMethodClick, this));
    },

    onOrderCheckMethodClick: function(evt) {
        evt.preventDefault();

        this.messageBus.notifySwitchToCheckStrategyRequest(this, $(evt.target).val());

        return false;
    },

    onLoadOrderRequest: function() {
        this.onExecuteOrderSearchRequest({'action' : 'GET-ORDERS'});
    },

    onReLoadOrderRequest: function() {
        this.onExecuteOrderSearchRequest({'action' : 'RELOAD-ORDERS'});
    },

    onExecuteOrderSearchRequest: function(searchParams) {
        this._forgetEmbedMessages();
        this.$el.html('');

        this.messageBus.notifyOrdersBeforeSearch();
        this.$el.load(this.loadUrl, searchParams, $.proxy(this.onOrderExecuteSearchCallback, this));
    },

    onOrderExecuteSearchCallback: function() {
        this.showEmbedMessages();

        this.initUi();

        this.messageBus.notifyOrderSearchExecuted();
        this.messageBus.notifyOrdersSelectionChanged(this);
    },

    onFindOrderByProdIdClick: function() {
        this.messageBus.notifySearchOrderByProdId();
    },

    onPaginationChange: function() {
        this.getOrdersResultForm().submit();
    },

    onReportButtonClick: function(evt) {
        var $orderResultsForm = this.getOrdersResultForm();

        $orderResultsForm.find('#REPORT_FORMAT').remove();
        $orderResultsForm.prepend('<input type="hidden" id="REPORT_FORMAT" name="report_format" value="' + $(evt.target).val() + '" />');
        var oldAction = $orderResultsForm.attr('action');
        $orderResultsForm.attr('action', this.reportUrl).submit();
        $orderResultsForm.attr('action', oldAction);
    },

    getOrdersResultForm: function() {
        return this.$('#orders_results');
    },

    onCancelDespatch: function() {
        this.resetSearch();
    },

    shouldRecordSerialNumber: function() {
        return this._shouldRecordSerialNumber;
    },

    shouldCheckEachLine: function() {
        return this._shouldCheckEachLine;
    },

    shouldCreateSscc: function() {
        return this._shouldCreateSscc;
    },

    canBeChecked: function() {
        return this._canBeChecked;
    },

    volumeIsRequired: function() {
        return this._volumeIsRequired;
    },

    weightIsRequired: function() {
        return this._weightIsRequired;
    },

    isEdi: function() {
        return this._isEdi;
    },

    getEdiOrderStatistics: function() {
        return _.clone(this._ediOrderStatistics);
    },

    getFoundOrdersAmount: function() {
        return parseInt(this.$('.total_count').text()) || 0;
    },

    onEdiOneOrderStatisticsChanged: function(orderStatistics) {
        var $container = this.$('.edi-one-statistics');

        $container.find('.total_count').text(orderStatistics.get('totalPickItems'));
        $container.find('.waiting-despatch').text(orderStatistics.get('readyForDespatchItems'));
        $container.find('.picked-qty').text(orderStatistics.get('pickedQty'));
        $container.find('.unchecked-qty').text(orderStatistics.getUncheckedQty());
        $container.find('.total-weight').text(orderStatistics.getTotalWeight().toFixed(4));
        $container.find('.total-volume').text(orderStatistics.getTotalVolume().toFixed(4));

        if (this.isEdi()) {
            $container.show();
        } else {
            $container.hide();
        }
    },

    onOrdersSelectionChanged: function(orders) {
        if (orders.isEdi() && orders.getSelectedAmount() > 0) {
            this.$('.order-switch-panel').show();
            this.$('.edi-one-statistics').show();
        } else {
            this.$('.order-switch-panel').hide();
            this.$('.edi-one-statistics').hide();
        }
    },

    _switchTo: function(type) {
        var $switches = this.$('[name="orderCheckMethod"]');

        $switches.removeAttr('checked');

        $switches.filter('[value="' + type + '"]').attr('checked', 'checked');
    },

    onCheckingStrategyEdiAll: function() {
        this._switchTo('edi-all');
    },

    onCheckingStrategyEdiOne: function() {
        this._switchTo('edi-one');
    }
}, EmbedMessagesAwareMixin, SelectionMixin));