var ProductCheckStrategySerialNumber = Backbone.View.extend({
    initialize: function(options) {
        this.recordingSerialNumber = true;
        this.messageBus = options.messageBus;
        this.prodId = options.prodId;
        this.bindMessageBusEvents();
    },

    bindMessageBusEvents: function() {
        this.messageBus.onCheckProdIdRequest(this.onCheckProdIdRequest, this);
        this.messageBus.notifySubscribeToSerialNumberTypeRequest(this, this.onBarcodeTypeSerialNumber);
        this.messageBus.notifySubscribeToProdEanTypeRequest(this, this.onBarcodeTypeProdEan);
    },

    onCheckProdIdRequest: function(prodId) {
        if (prodId != this.prodId) {
            this.messageBus.notifyConfirmSerialNumberSuspendRequest(this.prodId, prodId);
        }
    },

    onBarcodeTypeSerialNumber: function(dataIdentifier) {
        if (dataIdentifier) {
            this.messageBus.notifyBarcodeServed();
            this.messageBus.notifyDoSerialNumberCheckRequest(this.prodId, dataIdentifier.param_filtered_value);
        }
    },

    onBarcodeTypeProdEan: function(dataIdentifier) {
        this.messageBus.notifyConfirmSerialNumberSuspendRequest(this.prodId, null, dataIdentifier.param_filtered_value);
    },

    remove: function() {
        this.messageBus.notifyStopSubscriptionToBarcodeTypeRequest(this);
        return Backbone.View.prototype.remove.call(this);
    }

});