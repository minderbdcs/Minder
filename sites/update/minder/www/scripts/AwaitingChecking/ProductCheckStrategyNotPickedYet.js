var ProductCheckStrategyNotPickedYet = Backbone.View.extend({
    initialize: function(options) {
        this.messageBus = options.messageBus;
        this.bindMessageBusEvents();

        if (!options.silent) {
            showErrors(['Not all items have been picked yet.']);
        }
    },

    bindMessageBusEvents: function() {
        this.messageBus.onCheckProdIdRequest(this.onAny, this);
        this.messageBus.onCheckAllProdIdRequest(this.onAny, this);
        this.messageBus.onScreenButtonCheckAll(this.onAny, this);
        this.messageBus.notifySubscribeToProdEanTypeRequest(this, this.onAny);
    },

    onAny: function() {
        showErrors(['Not all items have been picked yet.']);
    },

    remove: function() {
        this.messageBus.notifyStopSubscriptionToBarcodeTypeRequest(this);
        return Backbone.View.prototype.remove.call(this);
    }

});