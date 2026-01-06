Minder_Model = $.inherit(
    {
        _fields: null,

        __constructor: function(fields) {
            this._fields = {};
            this._setFields(fields);
        },

        getFieldValue: function(name) {
            return this._fields[name];
        },

        getFields: function() {
            return this._fields;
        },

        setFields: function(fields, origin) {
            this._setFields(fields);
            this.notifyFieldsChanged(origin);
            return this;
        },

        _setFields: function(fields) {
            $.extend(this._fields, fields);
            return this;
        },

        setFieldValue: function(name, value, origin) {
            this._setFieldValue(name, value);
            this.notifyFieldsChanged(origin);
            return this;
        },

        _setFieldValue: function(name, value) {
            this._fields[name] = value;
            return this;
        },

        notifyFieldsChanged: function(origin) {
            this.notify(Minder_Model.fieldsChangedEvent, origin);
        },

        notify: function(event, origin) {
            var evt = $.Event(event);
            evt.sender = origin;
            $(this).trigger(evt);
        }
    },
    {
        fieldsChangedEvent: 'fields-changed'
    }
);