var NonEdiCheckStatisticsView = Backbone.View.extend({
    messageBus: null,

    initialize: function(options) {
        this.messageBus = options.messageBus;

        this.messageBus.onCheckingStatusChanged(this.onCheckingStatusChanged, this);
        this.messageBus.onCheckingComplete(this.onCheckingComplete, this);
    },

    onCheckingStatusChanged: function(status) {
        if (status.isEdi) {
            return;
        }

        if (status.lastProdId) {
            this.$('.items-to-scan').text(status.uncheckedProducts);
            this.$('.prod-id').text(status.lastProdId);
            this.$el.show();
        } else {
            this.$('.items-to-scan').text('');
            this.$('.prod-id').text('');
            this.$el.hide();
        }
    },

    onCheckingComplete: function(status) {
        this.$('.items-to-scan').text('');
        this.$('.prod-id').text('');
        this.$el.hide();
    }
});