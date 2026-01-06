var CheckStrategyNone = Backbone.Model.extend({

    messageBus : null,
    defaults: {
        isSysAdmin: false
    },
    linesErrors: [],

    acceptUrl: '',

    initialize: function(attributes, options) {
        this.messageBus = attributes.messageBus;
        this.messageBus.onLinesDespatchStatus(this.onLinesDespatchStatus, this);
        this.messageBus.onConnoteButtonClick(this.onConnoteButtonClick, this);
        this.linesErrors = [];
        this.acceptUrl = options.acceptUrl;

        if (attributes.selectedOrdersAmount > 0) {
            this.messageBus.notifyLoadLinesRequest();
        }
        this.listenTo(this, 'destroy', this.onDestroy);
    },

    onLinesDespatchStatus: function(despatchStatus) {
        var status ={
            shouldCheckEachItem: false,
            allItemsChecked: true,
            totalSelectedItems: 0 //todo: get selected items from lines despatch status
        };

        this.linesErrors = [].concat(despatchStatus.errors || [], despatchStatus.warnings || []);

        this.messageBus.notifyCheckingStarted();
        this.messageBus.notifyCheckingStatusChanged(status);
        this.messageBus.notifyCheckingComplete(status);

        this.showConnote(true);
    },

    onConnoteButtonClick: function() {
        this.showConnote(false);
    },

    showConnote: function(silent) {
        if (this.linesErrors.length > 0) {
            if (!silent) {
                showErrors(this.linesErrors);
            }

            if (!this.attributes.isSysAdmin) {
                return;
            }
        }

        this.messageBus.notifyShowConnote(this.acceptUrl);
    },

    onDestroy: function() {
        this.stopListening();
    }
});