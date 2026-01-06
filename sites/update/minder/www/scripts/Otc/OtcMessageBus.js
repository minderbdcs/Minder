/**
* Auto-generated file. Do not edit manually. Modify source instead and use grunt mustache_render task to rebuild.
*
* @source "js/events/otc-events.json"
*/
var OtcMessageBus = (function(){

    var OtcMessageBus = function() {};

    _.extend(
        OtcMessageBus.prototype,
        Backbone.Events,
        {
            notifyCloseOpenedDialogsRequest: function() {
                this.trigger('close-opened-dialogs-request');
            },

            onCloseOpenedDialogsRequest: function(callback, object) {
                object.listenTo(this, 'close-opened-dialogs-request', callback);
            },

            notifyCaptureToolImagesRequest: function(tool) {
                this.trigger('capture-tool-images-request', tool);
            },

            onCaptureToolImagesRequest: function(callback, object) {
                object.listenTo(this, 'capture-tool-images-request', callback);
            },

            notifyReloadToolRequest: function() {
                this.trigger('reload-tool-request');
            },

            onReloadToolRequest: function(callback, object) {
                object.listenTo(this, 'reload-tool-request', callback);
            },

            notifyDescriptionBarcode: function(descriptionLabel) {
                this.trigger('description-barcode', descriptionLabel);
            },

            onDescriptionBarcode: function(callback, object) {
                object.listenTo(this, 'description-barcode', callback);
            },

            notifyCostCenterBarcode: function(costCenterId) {
                this.trigger('cost-center-barcode', costCenterId);
            },

            onCostCenterBarcode: function(callback, object) {
                object.listenTo(this, 'cost-center-barcode', callback);
            },

            notifyIssueQtyBarcode: function(issueQty) {
                this.trigger('issue-qty-barcode', issueQty);
            },

            onIssueQtyBarcode: function(callback, object) {
                object.listenTo(this, 'issue-qty-barcode', callback);
            },

            notifyConsumableBarcode: function(consumableId) {
                this.trigger('consumable-barcode', consumableId);
            },

            onConsumableBarcode: function(callback, object) {
                object.listenTo(this, 'consumable-barcode', callback);
            },

            notifyToolBarcode: function(toolId) {
                this.trigger('tool-barcode', toolId);
            },

            onToolBarcode: function(callback, object) {
                object.listenTo(this, 'tool-barcode', callback);
            },

            notifyToolAltBarcode: function(toolAltBarcode) {
                this.trigger('tool-alt-barcode', toolAltBarcode);
            },

            onToolAltBarcode: function(callback, object) {
                object.listenTo(this, 'tool-alt-barcode', callback);
            },

            notifyBorrowerBarcode: function(borrowerId) {
                this.trigger('borrower-barcode', borrowerId);
            },

            onBorrowerBarcode: function(callback, object) {
                object.listenTo(this, 'borrower-barcode', callback);
            },

            notifyLocationBarcode: function(locationId) {
                this.trigger('location-barcode', locationId);
            },

            onLocationBarcode: function(callback, object) {
                object.listenTo(this, 'location-barcode', callback);
            },

            notifyLogRequest: function(message) {
                this.trigger('log-request', message);
            },

            onLogRequest: function(callback, object) {
                object.listenTo(this, 'log-request', callback);
            },

            notifySwitchToIssuesRequest: function() {
                this.trigger('switch-to-issues-request');
            },

            onSwitchToIssuesRequest: function(callback, object) {
                object.listenTo(this, 'switch-to-issues-request', callback);
            },

            notifySwitchToReturnsRequest: function() {
                this.trigger('switch-to-returns-request');
            },

            onSwitchToReturnsRequest: function(callback, object) {
                object.listenTo(this, 'switch-to-returns-request', callback);
            },

            notifySwitchToAuditRequest: function() {
                this.trigger('switch-to-audit-request');
            },

            onSwitchToAuditRequest: function(callback, object) {
                object.listenTo(this, 'switch-to-audit-request', callback);
            },

            notifyQueryBorrowerRequest: function(borrowerId) {
                this.trigger('query-borrower-request', borrowerId);
            },

            onQueryBorrowerRequest: function(callback, object) {
                object.listenTo(this, 'query-borrower-request', callback);
            },

            notifyQueryLocationRequest: function(location) {
                this.trigger('query-location-request', location);
            },

            onQueryLocationRequest: function(callback, object) {
                object.listenTo(this, 'query-location-request', callback);
            },

            notifyQueryCostCenterRequest: function(costCenter) {
                this.trigger('query-cost-center-request', costCenter);
            },

            onQueryCostCenterRequest: function(callback, object) {
                object.listenTo(this, 'query-cost-center-request', callback);
            },

            notifyQueryToolRequest: function(toolId) {
                this.trigger('query-tool-request', toolId);
            },

            onQueryToolRequest: function(callback, object) {
                object.listenTo(this, 'query-tool-request', callback);
            },

            notifyQueryLegacyToolCodeRequest: function(toolCode) {
                this.trigger('query-legacy-tool-code-request', toolCode);
            },

            onQueryLegacyToolCodeRequest: function(callback, object) {
                object.listenTo(this, 'query-legacy-tool-code-request', callback);
            }

        }
    );

    return OtcMessageBus;
})();
