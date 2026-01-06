var PageMessageBus = (function(){

    var PageMessageBus = function() {

    };

    _.extend(
        PageMessageBus.prototype,
        Backbone.Events,
        {
            notifyLoadLinesRequest: function() {
                this.trigger('load-lines-request');
            },

            onLoadLinesRequest: function(callback, object) {
                object.listenTo(this, 'load-lines-request', callback);
            },

            notifyLoadEdiLinesRequest: function() {
                this.trigger('load-edi-lines-request');
            },

            onLoadEdiLinesRequest: function(callback, object) {
                object.listenTo(this, 'load-edi-lines-request', callback);
            },

            notifyLoadOrderRequest: function() {
                this.trigger('load-order-request');
            },

            onLoadOrderRequest: function(callback, object) {
                object.listenTo(this, 'load-order-request', callback);
            },

            notifyReLoadOrderRequest: function() {
                this.trigger('re-load-order-request');
            },

            onReLoadOrderRequest: function(callback, object) {
                object.listenTo(this, 're-load-order-request', callback);
            },

            notifyExecuteOrderSearchRequest: function(searchParams) {
                this.trigger('execute-order-search-request', searchParams);
            },

            onExecuteOrderSearchRequest: function(callback, object) {
                object.listenTo(this, 'execute-order-search-request', callback);
            },

            notifyOrdersBeforeSearch: function() {
                this.trigger('order:before-search');
            },

            onOrdersBeforeSearch: function(callback, object) {
                object.listenTo(this, 'order:before-search', callback);
            },

            notifyOrderSearchExecuted: function() {
                this.trigger('order:search-executed');
            },

            onOrderSearchExecuted: function(callback, object) {
                object.listenTo(this, 'order:search-executed', callback);
            },

            notifyOrdersSelectionChanged: function(orders) {
                this.trigger('order:selection-change', orders);
            },

            onOrdersSelectionChanged: function(callback, object) {
                object.listenTo(this, 'order:selection-change', callback);
            },

            notifyLinesSelectionChanged: function(lines) {
                this.trigger('lines:selection-change', lines);
            },

            onLinesSelectionChanged: function(callback, object) {
                object.listenTo(this, 'lines:selection-change', callback);
            },

            notifyLinesBeforeLoad: function() {
                this.trigger('lines:before-load');
            },

            onLinesBeforeLoad: function(callback, object) {
                object.listenTo(this, 'lines:before-load', callback);
            },

            notifyEdiDespatchDataChanged: function(data) {
                this.trigger('edi-despatch-data-changed', data);
            },

            onEdiDespatchDataChanged: function(callback, object) {
                object.listenTo(this, 'edi-despatch-data-changed', callback);
            },

            notifyLinesDespatchStatus: function(status) {
                this.trigger('lines:despatch-status', status);
            },

            onLinesDespatchStatus: function(callback, object) {
                object.listenTo(this, 'lines:despatch-status', callback);
            },

            notifyConnoteButtonClick: function() {
                this.trigger('connote-button-click', status);
            },

            onConnoteButtonClick: function(callback, object) {
                object.listenTo(this, 'connote-button-click', callback);
            },

            notifySearchOrderByProdId: function(prodId) {
                this.trigger('search-order-by-prod-id', prodId);
            },

            onSearchOrderByProdId: function(callback, object) {
                object.listenTo(this, 'search-order-by-prod-id', callback);
            },

            onCancelDespatch: function(callback, object) {
                object.listenTo(this, 'cancel-despatch', callback);
            },

            notifyCancelDespatch: function() {
                this.trigger('cancel-despatch');
            },

            onSsccLabelPrinted: function(callback, object) {
                object.listenTo(this, 'sscc-labels-printed', callback);
            },

            notifySsccLabelsPrinted: function() {
                this.trigger('sscc-labels-printed');
            },

            onChangeCarrier: function(callback, object) {
                object.listenTo(this, 'change-carrier', callback);
            },

            notifyChangeCarrier: function() {
                this.trigger('change-carrier');
            },

            onShowConnote: function(callback, object) {
                object.listenTo(this, 'show-connote', callback);
            },

            notifyShowConnote: function(acceptUrl) {
                this.trigger('show-connote', acceptUrl);
            },

            onConnoteAcceptRequest: function(callback, object) {
                object.listenTo(this, 'connote:accept-request', callback);
            },

            notifyConnoteAcceptRequest: function() {
                this.trigger('connote:accept-request');
            },

            onConnoteCancelRequest: function(callback, object) {
                object.listenTo(this, 'connote:cancel-request', callback);
            },

            notifyConnoteCancelRequest: function() {
                this.trigger('connote:cancel-request');
            },

            onConnoteAccepted: function(callback, object) {
                object.listenTo(this, 'connote-accepted', callback);
            },

            notifyConnoteAccepted: function() {
                this.trigger('connote-accepted');
            },

            onSetConsignmentRequest: function(callback, object) {
                object.listenTo(this, 'set-consignment-request', callback);
            },

            notifySetConsignmentRequest: function(consignment) {
                this.trigger('set-consignment-request', consignment);
            },

            onSetConsignmentCarrierRequest: function(callback, object) {
                object.listenTo(this, 'set-consignment-carrier-request', callback);
            },

            notifySetConsignmentCarrierRequest: function(carrierId) {
                this.trigger('set-consignment-carrier-request', carrierId);
            },

            onConsignmentLabel: function(callback, object) {
                object.listenTo(this, 'consignment-label', callback);
            },

            notifyConsignmentLabel: function(label) {
                this.trigger('consignment-label', label);
            },

            notifyConfirmDataIdentifierRequest: function(dataIdentifiers) {
                this.trigger('confirm-data-identifier-request', dataIdentifiers);
            },

            onConfirmDataIdentifierRequest: function(callback, object) {
                object.listenTo(this, 'confirm-data-identifier-request', callback);
            },

            notifyRevokeConfirmDataIdentifierRequest: function() {
                this.trigger('revoke-confirm-data-identifier-request');
            },

            onRevokeConfirmDataIdentifierRequest: function(callback, object) {
                object.listenTo(this, 'revoke-confirm-data-identifier-request', callback);
            },

            notifyDataIdentifierConfirmed: function(dataIdentifier) {
                this.trigger('data-identifier-confirmed', dataIdentifier);
            },

            onDataIdentifierConfirmed: function(callback, object) {
                object.listenTo(this, 'data-identifier-confirmed', callback);
            },

            onBarcodeServed: function(callback, object) {
                object.listenTo(this, 'barcode-served', callback);
            },

            notifyBarcodeServed: function() {
                this.trigger('barcode-served');
            },

            notifyBarcodeSuccess: function(dataIdentifiers) {
                this.trigger('barcode-success', dataIdentifiers);
            },

            onBarcodeSuccess: function(callback, object) {
                object.listenTo(this, 'barcode-success', callback);
            },

            notifyBarcode: function(dataIdentifier) {
                this.trigger('barcode', dataIdentifier);
            },

            onBarcode: function(callback, object) {
                object.listenTo(this, 'barcode', callback);
            },

            notifyBarcodeName: function(name, dataIdentifier) {
                this.trigger('barcode:' + name, dataIdentifier);
            },

            onBarcodeName: function(name, callback, object) {
                object.listenTo(this, 'barcode:' + name, callback);
            },

            notifyBarcodeType: function(type, dataIdentifier) {
                this.trigger('barcode-type:' + type, dataIdentifier);
            },

            onBarcodeType: function(type, callback, object) {
                object.listenTo(this, 'barcode-type:' + type, callback);
            },


            notifySubscribeToBarcodeNameRequest: function(subscriber, callback, barcodeName) {
                this.trigger('subscribe-to-barcode-name-request', subscriber, callback, barcodeName);
            },

            onSubscribeToBarcodeNameRequest: function(callback, object) {
                object.listenTo(this, 'subscribe-to-barcode-name-request', callback);
            },

            notifySubscribeToScreenButtonNameRequest: function(subscriber, callback) {
                this.trigger('subscribe-to-barcode-name-request', subscriber, callback, 'SCREEN_BUTTON');
            },

            notifySubscribeToCarrierIdNameRequest: function(subscriber, callback) {
                this.trigger('subscribe-to-barcode-name-request', subscriber, callback, 'CARRIER_ID');
            },

            notifyStopSubscriptionToBarcodeNameRequest: function(subscriber, barcodeName) {
                this.trigger('subscribe-to-barcode-name-request', subscriber, barcodeName);
            },

            onStopSubscriptionToBarcodeNameRequest: function(callback, object) {
                object.listenTo(this, 'subscribe-to-barcode-name-request', callback);
            },


            notifySubscribeToBarcodeTypeRequest: function(subscriber, callback, barcodeType) {
                this.trigger('subscribe-to-barcode-type-request', subscriber, callback, barcodeType);
            },

            onSubscribeToBarcodeTypeRequest: function(callback, object) {
                object.listenTo(this, 'subscribe-to-barcode-type-request', callback);
            },

            notifySubscribeToSerialNumberTypeRequest: function(subscriber, callback) {
                this.trigger('subscribe-to-barcode-type-request', subscriber, callback, 'SERIAL_NUMBER');
            },

            notifySubscribeToProdEanTypeRequest: function(subscriber, callback) {
                this.trigger('subscribe-to-barcode-type-request', subscriber, callback, 'PROD_EAN');
            },

            notifySubscribeToProdIdTypeRequest: function(subscriber, callback) {
                this.trigger('subscribe-to-barcode-type-request', subscriber, callback, 'PROD_ID');
            },

            notifySubscribeToSsccTypeRequest: function(subscriber, callback) {
                this.trigger('subscribe-to-barcode-type-request', subscriber, callback, 'SSCC');
            },



            notifyStopSubscriptionToBarcodeTypeRequest: function(subscriber, barcodeType) {
                this.trigger('subscribe-to-barcode-type-request', subscriber, barcodeType);
            },

            onStopSubscriptionToBarcodeTypeRequest: function(callback, object) {
                object.listenTo(this, 'subscribe-to-barcode-type-request', callback);
            },


            notifyExecuteSearch: function(searchParams) {
                this.trigger('execute-search', searchParams);
            },

            onExecuteSearch: function(callback, object) {
                object.listenTo(this, 'execute-search', callback);
            },

            notifyCheckProdIdRequest: function(prodId) {
                this.trigger('check-prod-id', prodId);
            },

            onCheckProdIdRequest: function(callback, object) {
                object.listenTo(this, 'check-prod-id', callback);
            },

            notifyDoProductCheckRequest: function(prodId) {
                this.trigger('do-product-check', prodId);
            },

            onDoProductCheckRequest: function(callback, object) {
                object.listenTo(this, 'do-product-check', callback);
            },

            notifyCheckNextProdIdRequest: function(prodId) {
                this.trigger('check-next-prod-id', prodId);
            },

            onCheckNextProdIdRequest: function(callback, object) {
                object.listenTo(this, 'check-next-prod-id', callback);
            },

            notifyCheckNextProdEanRequest: function(prodEan) {
                this.trigger('check-next-prod-ean-request', prodEan);
            },

            onCheckNextProdEanRequest: function(callback, object) {
                object.listenTo(this, 'check-next-prod-ean-request', callback);
            },

            notifyDoSerialNumberCheckRequest: function(prodId, serialNumber) {
                this.trigger('do-serial-number-check-request', prodId, serialNumber);
            },

            onDoSerialNumberCheckRequest: function(callback, object) {
                object.listenTo(this, 'do-serial-number-check-request', callback);
            },

            notifyCheckSsccLabelRequest: function(ssccLabel) {
                this.trigger('check-sscc-label', ssccLabel);
            },

            onCheckSsccLabelRequest: function(callback, object) {
                object.listenTo(this, 'check-sscc-label', callback);
            },

            notifyCheckAllProdIdRequest: function(prodId) {
                this.trigger('check-all-prod-id', prodId);
            },

            onCheckAllProdIdRequest: function(callback, object) {
                object.listenTo(this, 'check-all-prod-id', callback);
            },

            notifyCheckingStarted: function(status) {
                this.trigger('checking:started', status);
            },

            onCheckingStarted: function(callback, object) {
                object.listenTo(this, 'checking:started', callback);
            },

            notifyCheckingStatusChanged: function(status) {
                this.trigger('checking:status-changed', status);
            },

            onCheckingProductNotFound: function(callback, object) {
                object.listenTo(this, 'checking:product-not-found', callback);
            },

            notifyCheckingProductNotFound: function(prodId) {
                this.trigger('checking:product-not-found', prodId);
            },

            onCheckingStatusChanged: function(callback, object) {
                object.listenTo(this, 'checking:status-changed', callback);
            },

            notifyCheckingComplete: function(status) {
                this.trigger('checking:complete', status);
            },

            onCheckingComplete: function(callback, object) {
                object.listenTo(this, 'checking:complete', callback);
            },

            notifyResetCheckLineStatusesRequest: function() {
                this.trigger('reset-check-line-statuses-request', status);
            },

            onResetCheckLineStatusesRequest: function(callback, object) {
                object.listenTo(this, 'reset-check-line-statuses-request', callback);
            },

            notifyConfirmSerialNumberSuspendRequest: function(prodId, continueWithProdId, continueWithProdEan) {
                this.trigger('confirm-serial-number-suspend-request', prodId, continueWithProdId, continueWithProdEan);
            },

            onConfirmSerialNumberSuspendRequest: function(callback, object) {
                object.listenTo(this, 'confirm-serial-number-suspend-request', callback);
            },

            notifySerialNumberSuspendConfirmed: function(continueWithProdId, continueWithProdEan) {
                this.trigger('serial-number-suspend-confirmed', continueWithProdId, continueWithProdEan);
            },

            onSerialNumberSuspendConfirmed: function(callback, object) {
                object.listenTo(this, 'serial-number-suspend-confirmed', callback);
           },

            notifyCommitSerialNumbersRequest: function(status, continueWithProdId) {
                this.trigger('commit-serial-number-request', status, continueWithProdId);
            },

            onCommitSerialNumberRequest: function(callback, object) {
                object.listenTo(this, 'commit-serial-number-request', callback);
            },

            notifyCarrierConfirmRequest: function(carrierId, token) {
                this.trigger('carrier-confirm:request', carrierId, token);
            },

            onCarrierConfirmRequest: function(callback, object) {
                object.listenTo(this, 'carrier-confirm:request', callback);
            },

            notifyCarrierConfirmed: function() {
                this.trigger('carrier-confirm:confirmed');
            },

            onCarrierConfirmed: function(callback, object) {
                object.listenTo(this, 'carrier-confirm:confirmed', callback);
            },

            notifyCarrierRejected: function() {
                this.trigger('carrier-confirm:rejected');
            },

            onCarrierRejected: function(callback, object) {
                object.listenTo(this, 'carrier-confirm:rejected', callback);
            },

            notifyOrderShipViaChanged: function(orderShipVia) {
                this.trigger('order:ship-via-changed', orderShipVia);
            },

            onOrderShipViaChanged: function(callback, object) {
                object.listenTo(this, 'order:ship-via-changed', callback);
            },

            notifyConfirmDespatchRequest: function() {
                this.trigger('confirm-despatch:request');
            },

            onConfirmDespatchRequest: function(callback, object) {
                object.listenTo(this, 'confirm-despatch:request', callback);
            },

            notifyConfirmDespatchConfirmed: function() {
                this.trigger('confirm-despatch:confirmed');
            },

            onConfirmDespatchConfirmed: function(callback, object) {
                object.listenTo(this, 'confirm-despatch:confirmed', callback);
            },

            notifyConfirmDespatchCancelled: function() {
                this.trigger('confirm-despatch:cancelled');
            },

            onConfirmDespatchCancelled: function(callback, object) {
                object.listenTo(this, 'confirm-despatch:cancelled', callback);
            },

            notifyQuickAcceptCarrierServiceRequest: function(carrierServiceId) {
                this.trigger('quick-accept-carrier-service-request', carrierServiceId);
            },

            onQuickAcceptCarrierServiceRequest: function(callback, object) {
                object.listenTo(this, 'quick-accept-carrier-service-request', callback);
            },

            notifyScreenButton: function(name) {
                this.trigger('screen-button:' + name);
            },

            onScreenButtonFindOrder: function(callback, object) {
                object.listenTo(this, 'screen-button:FIND_ORDER', callback);
            },

            onScreenButtonSelect: function(callback, object) {
                object.listenTo(this, 'screen-button:SELECT', callback);
            },

            notifyScreenButtonCheckAll: function() {
                this.notifyScreenButton('ALLCHKD');
            },

            onScreenButtonCheckAll: function(callback, object) {
                object.listenTo(this, 'screen-button:ALLCHKD', callback);
            },

            onScreenButtonResetCheck: function(callback, object) {
                object.listenTo(this, 'screen-button:RESET_CHECK', callback);
            },

            onScreenButtonCancel: function(callback, object) {
                object.listenTo(this, 'screen-button:CANCEL', callback);
            },

            onScreenButtonContinue: function(callback, object) {
                object.listenTo(this, 'screen-button:CONTINUE', callback);
            },

            onScreenButtonAccept: function(callback, object) {
                object.listenTo(this, 'screen-button:ACCEPT', callback);
            },

            onScreenButtonChecked: function(callback, object) {
                object.listenTo(this, 'screen-button:CHECKED', callback);
            },

            onScreenButtonConnote: function(callback, object) {
                object.listenTo(this, 'screen-button:CONNOTE', callback);
            },

            notifyScreenButtonServed: function() {
                this.trigger('screen-button-served');
            },

            onScreenButtonServed: function(callback, object) {
                object.listenTo(this, 'screen-button-served', callback);
            },

            notifyProductNotFoundDialogOpen: function() {
                this.trigger('product-not-found-dialog:open');
            },

            onProductNotFoundDialogOpen: function(callback, object) {
                object.listenTo(this, 'product-not-found-dialog:open', callback);
            },

            notifyProductNotFoundDialogClose: function() {
                this.trigger('product-not-found-dialog:close');
            },

            onProductNotFoundDialogClose: function(callback, object) {
                object.listenTo(this, 'product-not-found-dialog:close', callback);
            },

            notifySearchOrderByProdIdDialogOpen: function() {
                this.trigger('search-order-by-prod-id-dialog:open');
            },

            onSearchOrderByProdIdDialogOpen: function(callback, object) {
                object.listenTo(this, 'search-order-by-prod-id-dialog:open', callback);
            },

            notifySearchOrderByProdIdDialogClose: function() {
                this.trigger('search-order-by-prod-id-dialog:close');
            },

            onSearchOrderByProdIdDialogClose: function(callback, object) {
                object.listenTo(this, 'search-order-by-prod-id-dialog:close', callback);
            },

            notifySearchOrderByProdIdDialogSelectOrder: function(order, products) {
                this.trigger('search-order-by-prod-id-dialog:select-order', order, products);
            },

            onSearchOrderByProdIdDialogSelectOrder: function(callback, object) {
                object.listenTo(this, 'search-order-by-prod-id-dialog:select-order', callback);
            },

            notifySsccDimensionsCanceled: function() {
                this.trigger('sscc-dimensions:canceled');
            },

            onSsccDimensionsCanceled: function(callback, object) {
                object.listenTo(this, 'sscc-dimensions:canceled', callback);
            },

            notifySsccDimensionsAccepted: function(packSscc) {
                this.trigger('sscc-dimensions:accepted', packSscc);
            },

            onSsccDimensionsAccepted: function(callback, object) {
                object.listenTo(this, 'sscc-dimensions:accepted', callback);
            },

            notifySsccRePacked: function(checkLineDetails, packSscc) {
                this.trigger('sscc-dimensions:re-packed', checkLineDetails, packSscc);
            },

            onSsccRePacked: function(callback, object) {
                object.listenTo(this, 'sscc-dimensions:re-packed', callback);
            },

            startSsccCheckRequest: function(ssccLabel) {
                this.trigger('lock-sscc-for-check-request', ssccLabel);
            },

            onStartSsccCheckRequest: function(callback, object) {
                object.listenTo(this, 'lock-sscc-for-check-request', callback);
            },

            notifyPackSsccUpdated: function(packSscc) {
                this.trigger('pack-sscc-updated', packSscc);
            },

            onPackSsccUpdated: function(callback, object) {
                object.listenTo(this, 'pack-sscc-updated', callback);
            },

            notifyPackSsccListStatusUpdateRequest: function(packSsccList) {
                this.trigger('pack-sscc-list-update-request', packSsccList);
            },

            onPackSsccListStatusUpdateRequest:function(callback, object) {
                object.listenTo(this, 'pack-sscc-list-update-request', callback);
            },

            notifyEdiOnePackAcceptRequest: function(request) {
                this.trigger('edi-one-pack-accept-request', request);
            },

            onEdiOnePackAcceptRequest: function(callback, object) {
                object.listenTo(this, 'edi-one-pack-accept-request', callback);
            },

            notifyEdiOneDimensionsAccepted: function() {
                this.trigger('edi-one-dimensions-accepted');
            },

            onEdiOnePackDimensionsAccepted: function(callback, object) {
                object.listenTo(this, 'edi-one-dimensions-accepted', callback);
            },

            notifyEdiOneOrderStatisticsChanged: function(orderStatistics) {
                this.trigger('edi-one-order-statistics-changed', orderStatistics);
            },

            onEdiOneOrderStatisticsChanged: function(callback, object) {
                object.listenTo(this, 'edi-one-order-statistics-changed', callback);
            },

            notifyCheckingStrategyEdiOne: function() {
                this.trigger('checking-strategy:edi-one');
            },

            onCheckingStrategyEdiOne: function(callback, object) {
                object.listenTo(this, 'checking-strategy:edi-one', callback);
            },

            notifyCheckingStrategyEdiAll: function() {
                this.trigger('checking-strategy:edi-all');
            },

            onCheckingStrategyEdiAll: function(callback, object) {
                object.listenTo(this, 'checking-strategy:edi-all', callback);
            },

            notifySwitchToCheckStrategyRequest: function(orders, type) {
                this.trigger('switch-to-check-strategy-request', orders, type);
            },

            onSwitchToCheckStrategyRequest: function(callback, object) {
                object.listenTo(this, 'switch-to-check-strategy-request', callback);
            },

            notifyUiCancelSsccCheckRequest: function() {
                this.trigger('ui-cancel-sscc-check-request');
            },

            onUiCancelSsccCheckRequest: function(callback, object) {
                object.listenTo(this, 'ui-cancel-sscc-check-request', callback);
            },

            notifyCancelSsccCheckRequest: function(outSscc) {
                this.trigger('cancel-sscc-check-request', outSscc);
            },

            onCancelSsccCheckRequest: function(callback, object) {
                object.listenTo(this, 'cancel-sscc-check-request', callback);
            },

            notifyEdiOneCheckStatusReset: function(checkStatus) {
                this.trigger('edi-one-check-status-reset', checkStatus);
            },

            onEdiOneCheckStatusReset: function(callback, object) {
                object.listenTo(this, 'edi-one-check-status-reset', callback);
            }
        }
    );
    return PageMessageBus;
})();
