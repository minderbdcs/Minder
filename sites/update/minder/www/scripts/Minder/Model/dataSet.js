Minder_Model_DataSet = $.inherit(
    Minder_Model,
    {
        _dataRows: null,

        __constructor: function(data) {
            this._setData(data);
        },

        //-------------------------------------------------------------------------------------------
        // Public interface
        //-------------------------------------------------------------------------------------------
        setData: function(data, origin) {
            this._setData(data);
            this.notify(this.__self.dataChangedEvent, origin);
        },

        updateData: function(data, origin) {
            this._updateData(data);
            this.notify(this.__self.dataChangedEvent, origin);
        },

        getCellValue: function(rowId, fieldName) {
            var row = this._findRow(rowId);
            if (row === null) return null;

            return row[fieldName];

        },

        rowSelected: function(rowId) {
            var row = this._findRow(rowId);
            if (row === null) return false;

            return row.__selected;
        },

        getDataRows: function() {
            var _this = this;
            var result = [];

            $.each(this._dataRows, function(index, row){
                result.push(_this._cloneDataRow(row));
            });

            return result;
        },

        getRow: function(rowId) {
            var row = this._findRow(rowId);

            if (row === null) return null;

            return this._cloneDataRow(row);
        },

        selectRow: function(rowId, origin, selected) {
            this._selectRow(rowId, selected);
            this.notify(this.__self.rowSelectedEvent, origin);
        },

        selectAll: function(origin, selected) {
            this._selectAll(selected);
            this.notify(this.__self.rowSelectedEvent, origin);
        },

        allRowsSelected: function() {
            var selected = true;
            $.each(this._dataRows, function(index, row) {
                selected &= !!row.__selected;
            });

            return selected;
        },

        count: function() {
            return this._dataRows.length;
        },

        selectedCount: function(selected) {
            selected = (typeof selected == 'undefined') ? true : !!selected;
            var result = 0;

            $.each(this._dataRows, function(index, row) {
                if (row.__selected === selected) result++;
            });

            return result;
        },

        getSelectedRows: function(selected) {
            selected = (typeof selected == 'undefined') ? true : !!selected;
            var result = [];
            var dataSet = this;

            $.each(this._dataRows, function(index, row){
                if (row.__selected === selected) result.push(dataSet._cloneDataRow(row));
            });

            return result;
        },

        getChangedRows: function(changed) {
            changed = (typeof  changed == 'undefined') ? true : !!changed;

            var result = [];
            var dataSet = this;

            $.each(this._dataRows, function(index, row){
                if ((!!row.__changed) == changed) result.push(dataSet._cloneDataRow(row));
            });

            return result;
        },

        setRowField: function(rowId, field, value) {
            var row = this._findRow(rowId);
            if (row === null) return this;

            row[field] = value;
            row.__changed = true;
            return this;
        },

        getRowsAmount: function() {
            if (this._dataRows == null) return 0;
            return this._dataRows.length;
        },
        //-------------------------------------------------------------------------------------------
        // End of Public interface
        //-------------------------------------------------------------------------------------------

        _setData: function(data) {
            if (data instanceof Array)
                this._setDataArray(data);

            if (data instanceof Minder_Model_DataSet)
                this._setDataArray(data.getDataRows());
        },

        _updateData: function(data) {
            var _this = this;

            $.each(this._dataRows, function(rowIndex, rowData){
                $.each(data, function(tmpIndex, tmpData) {
                    if (rowData.__rowId == tmpData.__rowId) {
                        _this._dataRows[rowIndex] = tmpData;
                    }
                });
            });
        },

        _setDataArray: function(data) {
            var tmpData = [];
            var _this = this;

            $.each(data, function(index, row){
                tmpData.push(_this._cloneDataRow(row));
            });

            this._dataRows = tmpData;
            return this;
        },

        _cloneDataRow: function(source) {
            var result = {};

            for (i in source) {
                result[i] = source[i];
            }

            return result;
        },

        _selectRow: function(rowId, selected) {
            selected = (typeof selected == 'undefined') ? true : !!selected;
            var row = this._findRow(rowId);

            if (row == null)
                return this;

            row.__selected = selected;

            return this;
        },

        _selectAll: function(selected) {
            selected = (typeof selected == 'undefined') ? true : !!selected;
            $.each(this._dataRows, function(index, row) {
                row.__selected = selected;
            });

            return this;
        },

        _findRow: function(rowId) {
            var result = null;

            $.each(this._dataRows, function(index, row){
                if (row.__rowId == rowId){
                    result = row;
                    return false;
                }
            });

            return result;
        }
    },
    {
        //static members
        eventNamespace: 'Minder_Model_DataSet',
        dataChangedEvent: 'onDataChanged',
        rowSelectedEvent: 'rowSelectedEvent'
    }
);