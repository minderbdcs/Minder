(function(Minder2){

    Minder2.Model.DataRow = function(name, metadata, values) {
        this._name = name || '';
        this._fieldsMetadata = metadata || {};
        this._values = {};
        this._savedValues = {};
        this.setData(values || {}, true);
        this._listeners = {};
    };

    Minder2.Model.DataRow.prototype.DATA_CHANGED_EVENT = 'DATA_CHANGED_EVENT';


    Minder2.Model.DataRow.prototype._getFieldMetadata = function(fieldName) {
        if (typeof this._fieldsMetadata[fieldName] == 'undefined')
            return null;

        return this._fieldsMetadata[fieldName];
    };

    Minder2.Model.DataRow.prototype._setFieldValue = function(fieldName, fieldValue, stored) {
        this._values[fieldName] = fieldValue;

        if (stored)
            this._savedValues[fieldName] = fieldValue;

        return this;
    };

    Minder2.Model.DataRow.prototype._findTwins = function(fieldName) {
        var
            fieldMetadata = this._getFieldMetadata(fieldName),
            tmpFieldMetadata,
            result = [],
            fieldIndex;

        if (fieldMetadata == null)
            return result;

        for (fieldIndex in this._fieldsMetadata) {
            if (!this._fieldsMetadata.hasOwnProperty(fieldIndex))
                continue;

            if (fieldIndex == fieldName)
                continue;

            tmpFieldMetadata = this._getFieldMetadata(fieldIndex);

            if (tmpFieldMetadata.SSV_NAME == fieldMetadata.SSV_NAME &&
                tmpFieldMetadata.SSV_TABLE == fieldMetadata.SSV_TABLE) {
                result.push(fieldIndex);
            }
        }

        return result;
    };

    Minder2.Model.DataRow.prototype._notify = function(event) {
        $(this).trigger(event, this);
    };

    Minder2.Model.DataRow.prototype._notifyDataChanged = function(fieldName) {
        var event = $.Event(this.DATA_CHANGED_EVENT);
        event.fieldName = fieldName;
        event.target = this;
        this._notify(event);
    };

    Minder2.Model.DataRow.prototype.setFieldValue = function(fieldName, fieldValue) {
        var foundTwins = this._findTwins(fieldName),
            twinIndex;

        this._setFieldValue(fieldName, fieldValue)._notifyDataChanged(fieldName);

        for (twinIndex = 0; twinIndex < foundTwins.length; twinIndex++) {
            this._setFieldValue(foundTwins[twinIndex], fieldValue)._notifyDataChanged(foundTwins[twinIndex]);
        }
    };

    Minder2.Model.DataRow.prototype.getFieldValue = function(fieldName) {
        return this._values[fieldName];
    };

    Minder2.Model.DataRow.prototype.getName = function() {
        return this._name;
    };

    Minder2.Model.DataRow.prototype._getEventListeners = function(type) {
        if (typeof this._listeners[type] == 'undefined')
            return [];

        return this._listeners[type];
    };

    Minder2.Model.DataRow.prototype._setEventListeners = function(type, listeners) {
        this._listeners[type] = listeners.filter(function(element) {return element;});
        return this;
    };

    Minder2.Model.DataRow.prototype.removeEventListener = function(type, handle, flag) {
        var eventListeners = this._getEventListeners(type),
            iterator;

        for (iterator = 0; iterator < eventListeners.length; iterator++)
            if (eventListeners[iterator] === handle)
                eventListeners[iterator] = null;

        this._setEventListeners(type, eventListeners);
    };

    Minder2.Model.DataRow.prototype.setData = function(data, stored) {
        var fieldName, fieldTwins, index;

        for (fieldName in data) {
            if (!data.hasOwnProperty(fieldName))
                continue;

            this._setFieldValue(fieldName, data[fieldName], stored);

            fieldTwins = this._findTwins(fieldName);

            for (index = 0; index < fieldTwins.length; index++)
                this._setFieldValue(fieldTwins[index], data[fieldName], stored);
        }

        this._notifyDataChanged(null);

        return this;
    };

    Minder2.Model.DataRow.prototype.getData = function() {
        return this._values;
    };

    Minder2.Model.DataRow.prototype.cancelChanges = function() {
        this.setData(this._savedValues);
        return this;
    }

})(Minder2);