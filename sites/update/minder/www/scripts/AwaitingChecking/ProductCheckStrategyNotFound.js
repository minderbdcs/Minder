var ProductCheckStrategyNotFound = Backbone.View.extend({
    initialize: function(options) {
        this.messageBus = options.messageBus;
        this.bindMessageBusEvents();

        this.messageBus.notifyCheckingProductNotFound(options.prodId);
    },

    bindMessageBusEvents: function() {
        this.messageBus.onCheckProdIdRequest(this.onAny, this);
        this.messageBus.onCheckAllProdIdRequest(this.onAny, this);
        this.messageBus.onScreenButtonCheckAll(this.onAny, this);
        this.messageBus.notifySubscribeToProdEanTypeRequest(this, this.onAny);
    },

    onAny: function() {
        //do nothing until checking strategy changed
    },

    remove: function() {
        this.messageBus.notifyStopSubscriptionToBarcodeTypeRequest(this);
        return Backbone.View.prototype.remove.call(this);
    }

});