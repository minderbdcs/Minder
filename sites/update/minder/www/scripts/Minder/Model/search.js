Minder_Model_Search = $.inherit(
    Minder_Model,
    {
        _searchResultModel: null,

        attachResultModel: function(model) {
            this._searchResultModel = model;
        },

        getField: function(name) {
            if (typeof this._fields[name] == 'undefined')
                return null;

            return this._fields[name];
        },

        getFieldValue: function(name) {
            if (typeof this._fields[name] == 'undefined')
                return null;

            return this._fields[name].SEARCH_VALUE;
        },

        getFieldValues: function() {
            var result = {};

            $.each(this._fields, function(name, field){
                result[name] = field.SEARCH_VALUE;
            });

            return result;
        },

        setFieldValue: function(name, value, origin) {
            if (typeof this._fields[name] == 'undefined')
                return null;

            this._setFieldValue(name, value);
            this.notifyFieldsChanged(origin);
        },

        _setFieldValue: function(name, value) {
            this._fields[name].SEARCH_VALUE = value;
        },

        setFieldValues: function(valued, origin) {
            var _this = this;
            $.each(valued, function(name, value) {
                _this._setFieldValue(name, value);
            });
            this.notifyFieldsChanged(origin);
        },

        getSearchFields: function() {
            var filteredValues = {};

            $.each(this.getFieldValues(), function(fieldName, fieldValue){
                if (fieldValue !== null)
                    filteredValues[fieldName] = fieldValue;
            });

            return filteredValues
        },

        makeSearch: function() {
            this.notifySearch(this);
        },

        clearSearch: function(origin) {
            var _this = this;
            $.each(this._fields, function(name) {
                _this._setFieldValue(name, '');
            });
            this.notifyFieldsChanged(origin);
        },

        notifySearch: function(origin) {
            this.notify(Minder_Model_Search.searchEvent, origin);
        }
    },
    {
        'searchEvent': 'search'
    }
);