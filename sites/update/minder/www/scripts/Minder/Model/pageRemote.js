Minder_Model_PageRemote = $.inherit(
    Minder_Model_Page,
    {
        __constructor: function(fields) {
            this.__base(fields);
        },

        _getServiceUrl: function() {
            return this.getFieldValue('serviceUrl');
        },

        _callRemote: function(method, params, callback) {
            $.post(
                this._getServiceUrl(),
                {
                    'method': method,
                    'params': params
                },
                callback,
                'json'
            );
        },

        notifyFieldsChanged: function(origin) {
            this.notify(Minder_Model.fieldsChangedEvent, origin);
            this._callRemote('savePageState', {'fields': this.getFields()});
        },

        notifyCompanyLimitChanged: function(origin) {
            this.__base(origin);
            this._callRemote('setCompanyLimit', [this.getCompanyLimit()]);
        },

        notifyWarehouseLimitChanged: function(origin) {
            this.__base(origin);
            this._callRemote('setWarehouseLimit', [this.getWarehouseLimit()]);
        },

        notifyCurrentPrinterChanged: function(origin) {
            this.__base(origin);
            this._callRemote('setCurrentPrinter', [this.getCurrentPrinter()]);
        }
    },
    {
        //static members
    }
);