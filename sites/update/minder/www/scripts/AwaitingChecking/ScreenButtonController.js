var ScreenButtonController = (function() {

    var ScreenButtonController = function(options) {
        this.initialize(options);
    };

    _.extend(
        ScreenButtonController.prototype,
        Backbone.Events,
        {
            messageBus: null,

            screenButtonServed: false,

            initialize: function(options){
                this.messageBus = options.messageBus;

                this.messageBus.onScreenButtonServed(this.onScreenButtonServed, this);
                this.messageBus.notifySubscribeToScreenButtonNameRequest(this, this.onBarcodeScreenButton);
            },

            onBarcodeScreenButton: function(dataIdentifier) {
                var acceptTestResult = /^ACCEPT(\d+)/.exec(dataIdentifier.param_filtered_value);

                if (acceptTestResult) {
                    this.messageBus.notifyQuickAcceptCarrierServiceRequest(acceptTestResult[1]);
                } else {
                    switch (dataIdentifier.param_filtered_value) {
                        case 'ACCEPT':
                            this.onAcceptButton();
                            break;
                        case 'CANCEL':
                            this.onCancelButton();
                            break;
                        case 'CANCEL_DESPATCH':
                            this.onCancelDespatchButton();
                            break;
                        default:
                            this.onScreenButton(dataIdentifier);
                    }
                }

                this.messageBus.notifyBarcodeServed();
            },

            onScreenButton: function(dataIdentifier) {
                var foundButton = $('input[type="button"]').filter('[data-barcode="'+ dataIdentifier.param_filtered_value + '"]').filter(':first');

                if (foundButton.length > 0) {
                    foundButton.click();
                } else {
                    this.messageBus.notifyScreenButton(dataIdentifier.param_filtered_value);
                }
            },

            onAcceptButton: function() {
                this.screenButtonServed = false;
                this.messageBus.notifyScreenButton('ACCEPT');

                if (!this.screenButtonServed) {
                    this.messageBus.notifyConnoteAcceptRequest(); //todo: move to CheckingStrategy
                }
            },

            onCancelButton: function() {
                this.screenButtonServed = false;
                this.messageBus.notifyScreenButton('CANCEL');

                if (!this.screenButtonServed) {
                    this.messageBus.notifyConnoteCancelRequest();  //todo: move to CheckingStrategy
                }
            },

            onCancelDespatchButton: function() {
                this.messageBus.notifyCancelDespatch();
            },

            onScreenButtonServed: function() {
                this.screenButtonServed = true;
            }
        }
    );

    return ScreenButtonController;

})();