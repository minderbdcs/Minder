var PageView = Backbone.View.extend({
    messageBus: null,
    checkingStrategy: null,
    isSysAdmin: false,

    checkingStatus: {},
    searchParams: null,

    foundOrders: 0,
    orderSearchDialogIsActive: false,

    storeCheckStatusUrl: '',
    getRetailUnitUrl: '',
    printNextSsccLabelUrl: '',

    forceCheckProducts: [],

    initialize: function(options) {
        this.messageBus = new PageMessageBus();
        this.checkingStatus = {};
        this.storeCheckStatusUrl = options.storeCheckStatusUrl;
        this.isSysAdmin = options.isSysAdmin;
        this.getRetailUnitUrl = options.getRetailUnitUrl;
        this.ediOneOptions = options.ediOne;
        this.ediAllOptions = options.ediAll;
        this.nonEdiOptions = options.nonEdi;
        this.noCheckingOptions = options.noChecking;
        this.printNextSsccLabelUrl = options.printNextSsccUrl;
        this.printAllSsccUrl = options.printAllSsccUrl;

        new BarcodeView(_.extend({
            messageBus: this.messageBus,
            el: '#barcode'
        }, options.barcode));

        this.bindMessageBusEvents();
        this.bindLegacyEvents();
        this.bindUiEvents();

        new EdiLinesView(_.extend({
            messageBus: this.messageBus,
            el: "#EDI_LINES_LIST_CONTAINER"
        }, options.ediLines));

        new PromptView({
            messageBus: this.messageBus,
            el: ".order-check-prompt"
        });

        new SearchOrderByProdIdDialogView(_.extend({
            messageBus: this.messageBus,
            el: "#search_order_by_prod_id_dialog"
        }, options.searchOrderByProdIdDialog));

        new ProductNotFoundDialogView({
            messageBus: this.messageBus,
            el: "#prod_id_not_found_dialog"
        });

        new NonEdiCheckStatisticsView({
            messageBus: this.messageBus,
            el: ".product-info"
        });

        new EdiCheckStatisticsView({
            messageBus: this.messageBus,
            el: ".sscc-info, .product-info"
        });

        new ScreenButtonController(_.extend({
            messageBus: this.messageBus
        }, options.screenButtonController));

        new ConfirmDespatchDialogView(_.extend({
            messageBus: this.messageBus,
            el: '#confirm_despatch_dialog'
        }, options.confirmDespatchDialog));

        new ConfirmCarrierDialogView(_.extend({
            messageBus: this.messageBus,
            el: '#confirm_carriers_dialog'
        }, options.confirmCarrierDialog));

        new ConfirmDataIdentifierDialogView(_.extend({
            messageBus: this.messageBus,
            el: '#confirm_data_identifier_dialog'
        }, options.confirmDataIdentifierDialog));

        new SearchFormView(_.extend({
            messageBus: this.messageBus,
            el: '#SEARCH_RESULTS_FORM'
        }, options.searchForm));

        new ConnoteView(_.extend({
            messageBus: this.messageBus,
            el: '#CONNOTE_SCREEN_CONTAINER'
        }, options.connote));

        new LinesView(_.extend({
            messageBus: this.messageBus,
            el: '#LINES_LIST_CONTAINER'
        }, options.lines));

        new OrdersView(_.extend({
                messageBus: this.messageBus,
                el: '#ORDERS_LIST_CONTAINER'
        }, options.order));
    },

    bindLegacyEvents: function() {
        Minder.Despatches.AwaitingChecking.SsccPackDetails.onSsccLabelCancelAccept('page-controller', $.proxy(this.onSsccLabelCancelAccept, this));
        Minder.AwaitingChecking.Dlg.ChangeCarrierDialog.addSuccessListener('page-controller', $.proxy(this.onCarrierChanged, this));
        Minder.Dlg.RePackSscc.onRepack('page-controller', $.proxy(this.onSsccRepacked, this));
    },

    bindUiEvents: function () {
        $(document).bind('print-next-sscc', $.proxy(this.onUiPrintNextSscc, this));
        $(document).bind('print-all-sscc-labels', $.proxy(this.onUiPrintAllSsccLabels, this));
        $(document).bind('cancel-sscc-check', $.proxy(this.onUiCancelSsccCheck, this));
    },

    onUiPrintAllSsccLabels: function() {
        $.post(this.printAllSsccUrl, {}, $.proxy(this.printAllSsccCallback, this), 'json');
    },

    printAllSsccCallback: function(response) {
        showResponseMessages(response);

        if (response.errors && response.errors.length > 0) {
            return;
        }

        this.messageBus.notifyEdiOneOrderStatisticsChanged(new OrderStatistics(response.orderStatistics));
    },

    onUiCancelSsccCheck: function() {
        this.messageBus.notifyUiCancelSsccCheckRequest();
    },

    onUiPrintNextSscc: function() {
        $.post(this.printNextSsccLabelUrl, {}, $.proxy(this.printNextSsccCallback, this), 'json');
    },

    printNextSsccCallback: function(response) {
        showResponseMessages(response);

        if (response.errors && response.errors.length > 0) {
            return;
        }

        this.messageBus.notifyEdiOneOrderStatisticsChanged(new OrderStatistics(response.orderStatistics));
    },

    onSsccRepacked: function(evt) {
        this.messageBus.notifySsccRePacked(evt.checkDetails, evt.ssccMap);
    },

    onCarrierChanged: function() {
        this.messageBus.notifyReLoadOrderRequest();
    },

    onSsccLabelCancelAccept: function() {
        this.messageBus.notifySsccDimensionsCanceled();
    },

    bindMessageBusEvents: function() {
        this.messageBus.onChangeCarrier(this.onChangeCarrier, this);
        this.messageBus.onExecuteSearch(this.onExecuteSearch, this);
        this.messageBus.onConnoteAccepted(this.onConnoteAccepted, this);
        this.messageBus.onOrdersSelectionChanged(this.onOrdersSelectionChanged, this);
        this.messageBus.onOrderSearchExecuted(this.onOrderSearchExecuted, this);
        this.messageBus.onCheckingStatusChanged(this.onCheckingStatusChanged, this);
        this.messageBus.onConfirmDespatchCancelled(this.onConfirmDespatchCancelled, this);
        this.messageBus.onConsignmentLabel(this.onConsignmentLabel, this);
        this.messageBus.onSearchOrderByProdIdDialogOpen(this.onSearchOrderByProdIdDialogOpen, this);
        this.messageBus.onSearchOrderByProdIdDialogClose(this.onSearchOrderByProdIdDialogClose, this);
        this.messageBus.onSearchOrderByProdIdDialogSelectOrder(this.onSearchOrderByProdIdDialogSelectOrder, this);
        this.messageBus.onScreenButtonCancel(this.onScreenButtonCancel, this);
        this.messageBus.onSwitchToCheckStrategyRequest(this.onSwitchToCheckStrategyRequest, this);

        this.messageBus.notifySubscribeToProdIdTypeRequest(this, this.onBarcodeTypeProdId);
        this.messageBus.notifySubscribeToSsccTypeRequest(this, this.onBarcodeTypeSscc);
        this.messageBus.notifySubscribeToCarrierIdNameRequest(this, this.onBarcodeCarrierId);

    },

    onSearchOrderByProdIdDialogSelectOrder: function(order, products) {
        if (order) {
            this.forceCheckProducts = products;
            $('#barcode').minderBarcodeInputEmulateSuccessEvent('SALESORDER', order);
        } else {
            showWarnings(['No order selected']);
        }
    },

    onSearchOrderByProdIdDialogOpen: function() {
        this.orderSearchDialogIsActive = true;
    },

    onSearchOrderByProdIdDialogClose: function() {
        this.orderSearchDialogIsActive = false;
    },

    onConsignmentLabel: function(label) {
        if (Minder.AwaitingChecking.Dlg.ExitCarrier.isActive()) {
            Minder.AwaitingChecking.Dlg.ExitCarrier.setPackId(label.param_filtered_value);
        } else {
            this.messageBus.notifySetConsignmentRequest(label.param_filtered_value);
            this.messageBus.notifySetConsignmentCarrierRequest(label.param_name);

            //selectCarrierByConnoteLabelAndAccept(label.param_name);
        }
    },

    onChangeCarrier: function() {
        Minder.AwaitingChecking.Dlg.ChangeCarrierDialog.show();
    },

    onConnoteAccepted: function() {
        this.checkingStatus = {};

        if (this.searchParams) {
            this.messageBus.notifyExecuteOrderSearchRequest(this.searchParams);
        } else {
            this.messageBus.notifyLoadOrderRequest();
        }
    },

    onConfirmDespatchCancelled: function() {
        this.checkingStatus = {};

        if (this.searchParams) {
            this.messageBus.notifyExecuteOrderSearchRequest(this.searchParams);
        }
    },

    onExecuteSearch: function(searchParams) {
        if (this.shouldRequestDespatchConfirmation()) {
            this.searchParams = searchParams;
            this.messageBus.notifyConfirmDespatchRequest();
        } else {
            this.messageBus.notifyExecuteOrderSearchRequest(searchParams);
        }
    },

    shouldRequestDespatchConfirmation: function() {
        return this.checkingStatus.shouldCheckEachItem && this.checkingStatus.allItemsChecked && (this.checkingStatus.totalSelectedItems > 0);
    },

    onOrderSearchExecuted: function() {
        this.searchParams = null;
    },

    onSwitchToCheckStrategyRequest: function(orders, type) {
        if (this.checkingStrategy) {
            this.checkingStrategy.destroy();
        }

        if (type == 'edi-one') {
            this.checkingStrategy = new CheckStrategyEdiOne({
                messageBus: this.messageBus,
                getRetailUnitUrl: this.getRetailUnitUrl,
                selectedOrdersAmount: orders.getSelectedAmount(),
                canBeChecked: orders.canBeChecked(),
                weightIsRequired: orders.weightIsRequired(),
                volumeIsRequired: orders.volumeIsRequired()
            }, this.ediOneOptions);

        } else {
            this.checkingStrategy = new CheckStrategyEdiAll({
                messageBus: this.messageBus,
                getRetailUnitUrl: this.getRetailUnitUrl,
                selectedOrdersAmount: orders.getSelectedAmount(),
                canBeChecked: orders.canBeChecked()
            }, this.ediAllOptions);
        }
    },

    onOrdersSelectionChanged: function(orders) {
        var orderStatistics = orders.getEdiOrderStatistics();

        if (this.checkingStrategy) {
            this.checkingStrategy.destroy();
        }

        this.foundOrders = orders.getFoundOrdersAmount();

        if (orders.shouldCheckEachLine()) {
            if (orders.shouldCreateSscc()) {
                if ((parseInt(orderStatistics.totalSscc) || 0) > 1000) {
                    this.checkingStrategy = new CheckStrategyEdiOne({
                        messageBus: this.messageBus,
                        getRetailUnitUrl: this.getRetailUnitUrl,
                        selectedOrdersAmount: orders.getSelectedAmount(),
                        canBeChecked: orders.canBeChecked(),
                        weightIsRequired: orders.weightIsRequired(),
                        volumeIsRequired: orders.volumeIsRequired()
                    }, this.ediOneOptions);
                } else {
                    this.checkingStrategy = new CheckStrategyEdiAll({
                        messageBus: this.messageBus,
                        getRetailUnitUrl: this.getRetailUnitUrl,
                        selectedOrdersAmount: orders.getSelectedAmount(),
                        canBeChecked: orders.canBeChecked()
                    }, this.ediAllOptions);
                }
            } else {
                this.checkingStrategy = new CheckStrategyNonEdi({
                    messageBus: this.messageBus,
                    getRetailUnitUrl: this.getRetailUnitUrl,
                    canBeChecked: orders.canBeChecked(),
                    shouldRecordSerialNumber: orders.shouldRecordSerialNumber(),
                    forceCheckProducts: this.forceCheckProducts,
                    selectedOrdersAmount: orders.getSelectedAmount(),
                    isSysAdmin: this.isSysAdmin
                }, this.nonEdiOptions);
            }
        } else {
            this.checkingStrategy = new CheckStrategyNone({
                messageBus: this.messageBus,
                selectedOrdersAmount: orders.getSelectedAmount(),
                isSysAdmin: this.isSysAdmin
            }, this.noCheckingOptions);
        }

        this.forceCheckProducts = [];
    },

    onCheckingStatusChanged: function(status) {
        this.checkingStatus = status;
        //$.post(
        //    this.storeCheckStatusUrl,
        //    status,
        //    null,
        //    'json'
        //);
    },

    onBarcodeTypeSscc: function(dataIdentifier) {
        this.messageBus.notifyBarcodeServed();
        if (Minder.Dlg.RePackSscc.isVisible()) {
            Minder.Dlg.RePackSscc.addSsccLabel(dataIdentifier.param_filtered_value);
        } else {
            this.messageBus.notifyCheckSsccLabelRequest(dataIdentifier.param_filtered_value);
        }

    },

    onBarcodeTypeProdId: function(dataIdentifier) {
        this.messageBus.notifyBarcodeServed();
        if (Minder.Dlg.RePackSscc.isVisible()) {
            Minder.Dlg.RePackSscc.setProduct(dataIdentifier.param_filtered_value);
        } else if (this.orderSearchDialogIsActive) {
            this.messageBus.notifySearchOrderByProdId(dataIdentifier.param_filtered_value);
        } else if (this.foundOrders < 1) {
            this.messageBus.notifyCheckingProductNotFound(dataIdentifier.param_filtered_value);
        } else {
            this.messageBus.notifyCheckProdIdRequest(dataIdentifier.param_filtered_value);
        }
    },

    onBarcodeCarrierId: function(dataIdentifier) {
        this.messageBus.notifyBarcodeServed();
        Minder.AwaitingChecking.Dlg.ExitCarrier.showDialog();
        Minder.AwaitingChecking.Dlg.ExitCarrier.setCarrier(dataIdentifier.param_filtered_value);
    },

    onScreenButtonCancel: function() {
        if (Minder.Dlg.RePackSscc.isVisible()) {
            this.notifyScreenButtonServed();
            Minder.Dlg.RePackSscc.close();
        }else if (Minder.AwaitingChecking.Dlg.ExitCarrier.isActive()) {
            this.notifyScreenButtonServed();
            Minder.AwaitingChecking.Dlg.ExitCarrier.closeDialog();
        }
    }
});