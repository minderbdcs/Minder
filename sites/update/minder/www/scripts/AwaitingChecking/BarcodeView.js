var BarcodeView = Backbone.View.extend({
    messageBus: null,
    barcodeServed: false,

    initialize: function(options) {
        this.subscriptions = new BarcodeSubscriptionCollection();

        this.messageBus = options.messageBus;
        this.$el.bind('parse-success', $.proxy(this.onBarcodeSuccess, this));
        this.$el.bind('parse-error', $.proxy(this.onBarcodeError, this));
        this.messageBus.onBarcodeServed(this.onBarcodeServed, this);
        this.messageBus.onDataIdentifierConfirmed(this.onDataIdentifierConfirmed, this);

        this.messageBus.onSubscribeToBarcodeNameRequest(this.onSubscribeToBarcodeNameRequest, this);
        this.messageBus.onSubscribeToBarcodeTypeRequest(this.onSubscribeToBarcodeTypeRequest, this);

        this.messageBus.onStopSubscriptionToBarcodeNameRequest(this.onStopSubscriptionToBarcodeNameRequest, this);
        this.messageBus.onStopSubscriptionToBarcodeTypeRequest(this.onStopSubscriptionToBarcodeTypeRequest, this);
    },

    onStopSubscriptionToBarcodeNameRequest: function(subscriber, barcodeName) {
        var spec = { subscriberId: this._getSubscriberListenId(subscriber) };

        if (!!barcodeName) { spec.dataName = barcodeName; }

        this.subscriptions.remove(this.subscriptions.where(spec));
    },

    onStopSubscriptionToBarcodeTypeRequest: function(subscriber, barcodeType) {
        var spec = { subscriberId: this._getSubscriberListenId(subscriber) };

        if (!!barcodeType) { spec.dataType = barcodeType; }

        this.subscriptions.remove(this.subscriptions.where(spec));
    },

    onSubscribeToBarcodeNameRequest: function(subscriber, callback, barcodeName) {
        this.subscriptions.add({dataName: barcodeName, subscriberId: this._getSubscriberListenId(subscriber)}, {parse: true});
        this.messageBus.onBarcodeName(barcodeName, callback, subscriber);
    },

    onSubscribeToBarcodeTypeRequest: function(subscriber, callback, barcodeType) {
        this.subscriptions.add({dataType: barcodeType, subscriberId: this._getSubscriberListenId(subscriber)}, {parse: true});
        this.messageBus.onBarcodeType(barcodeType, callback, subscriber);
    },

    onBarcodeSuccess: function(evt) {
        var paramDescription = evt.parseResult.paramDesc,
            allBarcode = this.$el.minderGetAllValidParams(paramDescription.param_raw_value),
            allExpected = this._filterExpected(allBarcode);

        this.$el.val('').focus();

        if (allExpected.length > 1) {
            this.messageBus.notifyConfirmDataIdentifierRequest(allExpected);
        } else if (allExpected < 1) {
            this._processDataIdentifier(paramDescription);
            this.messageBus.notifyRevokeConfirmDataIdentifierRequest();
        } else {
            this._processDataIdentifier(allExpected.shift());
            this.messageBus.notifyRevokeConfirmDataIdentifierRequest();
        }
    },

    onBarcodeError: function(evt) {
        showErrors([evt.parseResult.errorMsg], 10000);
        this.$el.val('').focus();
    },

    onDataIdentifierConfirmed: function(dataIdentifier) {
        this._processDataIdentifier(dataIdentifier);
        this.$el.focus();
    },

    _processDataIdentifier: function(paramDescription) {
        var connoteTest = /^CONNOTE_\w+/;

        this.barcodeServed = false;

        this.messageBus.notifyBarcodeName(paramDescription.param_name, paramDescription);

        if (!this.barcodeServed) {
            this.messageBus.notifyBarcodeType(paramDescription.param_type, paramDescription);
        }

        if (!this.barcodeServed) {
            this.messageBus.notifyBarcode(paramDescription);
        }

        if (!this.barcodeServed) {
            if (connoteTest.test(paramDescription.param_name)) {
                this.messageBus.notifyConsignmentLabel(paramDescription);
            }
        }
    },

    onBarcodeServed: function() {
        this.barcodeServed = true;
    },

    _filterExpected: function(barcodeList) {
        var dataName = _.filter(this.subscriptions.pluck('dataName'), function(dataName){return !!dataName;}),
            dataType = _.filter(this.subscriptions.pluck('dataType'), function(dataType){return !!dataType;});

        return barcodeList.filter(function(barcode){
            return (_.indexOf(dataName, barcode.param_name) > -1) || (_.indexOf(dataType, barcode.param_type) > -1);
        });
    },

    _getSubscriberListenId: function(subscriber) {
        return (subscriber._listenId = subscriber._listenId || _.uniqueId('l'));
    }
});

var BarcodeSubscription = Backbone.Model.extend({
    defaults: {
        'dataName': '',
        'dataType': '',
        'subscriberId': ''
    },

    parse: function(attributes) {
        attributes.id = attributes.id || [attributes.subscriberId, attributes.dataType, attributes.dataName].join('/');

        return attributes;
    }
});

var BarcodeSubscriptionCollection = Backbone.Collection.extend({
    model: BarcodeSubscription
});