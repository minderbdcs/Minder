var ProductCheckStrategyGeneral = Backbone.View.extend({
    initialize: function(options) {
        this.immidiateCheck = true;
        this.messageBus = options.messageBus;
        this.prodId = options.prodId;
        this.bindMessageBusEvents();
    },

    bindMessageBusEvents: function() {
        this.messageBus.onCheckProdIdRequest(this.onCheckProdIdRequest, this);
        this.messageBus.onScreenButtonCheckAll(this.onScreenButtonCheckAll, this);

        this.messageBus.notifySubscribeToProdEanTypeRequest(this, this.onBarcodeTypeProdEan);
    },

    onCheckProdIdRequest: function(prodId) {
        if (prodId == this.prodId) {
            this.messageBus.notifyDoProductCheckRequest(this.prodId);
        } else {
            this.messageBus.notifyCheckNextProdIdRequest(prodId);
        }
    },

    onScreenButtonCheckAll: function() {
        if (this.prodId) {
            this.messageBus.notifyCheckAllProdIdRequest(this.prodId);
        }
    },

    onBarcodeTypeProdEan: function(dataIdentifier) {
        this.messageBus.notifyCheckNextProdEanRequest(dataIdentifier.param_filtered_value);
    },

    remove: function() {
        this.messageBus.notifyStopSubscriptionToBarcodeTypeRequest(this);
        return Backbone.View.prototype.remove.call(this);
    }
});