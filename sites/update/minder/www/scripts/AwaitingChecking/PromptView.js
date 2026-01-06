//todo: add support for EdiAll status change

var PromptView = Backbone.View.extend({
    messageBus: null,

    nextCheckAbleSscc: null,

    initialize: function(options) {
        this.messageBus = options.messageBus;
        this.nextCheckAbleSscc = {'PS_OUT_SSCC': ''};

        this.messageBus.onCheckingStatusChanged(this.onCheckingStatusChanged, this);
        this.messageBus.onLinesDespatchStatus(this.onLinesDespatchStatus, this);
        this.messageBus.onSsccLabelPrinted(this.onSsccLabelPrinted, this);
        this.messageBus.onEdiOneOrderStatisticsChanged(this.onEdiOneOrderStatisticsChanged, this);
        this.messageBus.onLoadOrderRequest(this.onLoadOrderRequest, this);
        this.messageBus.onReLoadOrderRequest(this.onLoadOrderRequest, this);
        this.messageBus.onExecuteOrderSearchRequest(this.onLoadOrderRequest, this);
    },

    onLoadOrderRequest: function() {
        this.showPrompt(1);
    },

    onEdiOneOrderStatisticsChanged: function(orderStatistics) {
        if (orderStatistics.hasPrintedSscc()) {
            this.showPrompt(3, [{'placeholder': '%nextSscc%', 'value': orderStatistics.getNextPrintedSscc()}]);
        } else {
            this.showPrompt(2);
        }
    },

    onCheckingStatusChanged: function(status) {
        this.nextCheckAbleSscc = status.nextCheckAbleSscc;

        if (status.waitingForSuspendConfirmation) {
            this.showPrompt(29);
        } else if (status.recordingSerialNumbers) {
            this.showPrompt(21, [
                {'placeholder': '%total%', 'value': status.pickedAmount},
                {'placeholder': '%left%', 'value': status.uncheckedProducts}
            ]);
        } else if (status.dimensionsStarted) {
            this.showPrompt(5);
        } else if (status.started) {
            this.showPrompt(4);
        } else if (status.hasPrintedSscc) {
            this.showPrompt(3, [{'placeholder': '%nextSscc%', 'value': this.nextCheckAbleSscc.PS_OUT_SSCC}]);
        } else {
            this.showPrompt(2);
        }
    },

    onSsccLabelPrinted: function() {
        this.showPrompt(3, [{'placeholder': '%nextSscc%', 'value': this.nextCheckAbleSscc.PS_OUT_SSCC}]);
    },

    onLinesDespatchStatus: function(linesStatus) {
        if (linesStatus.readyForDespatchAmount > 0) {
            this.$el.show();
        } else {
            this.$el.hide();
        }
    },

    showPrompt: function(code, data) {
        this.$el.minderScreenPrompts('showPrompt', code, data);
    }
});