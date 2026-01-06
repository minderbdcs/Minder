var EdiCheckStatisticsView = Backbone.View.extend({
    messageBus: null,

    initialize: function(options) {
        this.messageBus = options.messageBus;

        this.messageBus.onCheckingStatusChanged(this.onCheckingStatusChanged, this);
        this.messageBus.onCheckingComplete(this.onCheckingComplete, this);
        this.messageBus.onEdiOnePackDimensionsAccepted(this.onEdiOnePackDimensionsAccepted, this);
        this.messageBus.onEdiOneCheckStatusReset(this.onEdiOneCheckStatusReset, this);
    },

    onEdiOneCheckStatusReset: function() {
        this.$('.sscc-label-no').text('');
        this.$('.total-items-to-scan').text('');
        this.$('.items-to-scan').text('');
        this.$('.prod-id').text('');
        this.$el.hide();
    },

    onCheckingStatusChanged: function(status) {
        if (!status.isEdi) {
            return;
        }

        if (status.lastSscc) {
            this.$('.sscc-label-no').text(status.lastSscc[0].PS_OUT_SSCC);
            this.$('.total-items-to-scan').text(status.uncheckedSsccItems);
            this.$('.items-to-scan').text(status.uncheckedProducts);
            this.$('.prod-id').text(status.uncheckedProdId);
            this.$el.show();
        } else {
            this.$('.sscc-label-no').text('');
            this.$('.total-items-to-scan').text('');
            this.$('.items-to-scan').text('');
            this.$('.prod-id').text('');
            this.$el.hide();
        }
    },

    onEdiOnePackDimensionsAccepted: function() {
        this.$('.sscc-label-no').text('');
        this.$('.total-items-to-scan').text('');
        this.$('.items-to-scan').text('');
        this.$('.prod-id').text('');
        this.$el.hide();
    },

    onCheckingComplete: function(status) {
        this.$('.sscc-label-no').text('');
        this.$('.total-items-to-scan').text('');
        this.$('.items-to-scan').text('');
        this.$('.prod-id').text('');
        this.$el.hide();
    }
});