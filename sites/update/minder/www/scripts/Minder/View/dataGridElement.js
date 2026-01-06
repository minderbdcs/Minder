Minder_View_DataGridElement = $.inherit(
    Minder_View_Container,
    {
        _rowId: null,
        _colorFieldName: null,

        getValue: function() {
            var fieldValue = this.getFieldValue(this.getName());
            return (!fieldValue) ? '' : fieldValue;
        },

        getFieldValue: function(fieldName) {
            var cellValue = this._model.getCellValue(this._rowId, this.getName());
            return (!cellValue) ? '' : cellValue;
        },

        setRowId: function(rowId) {
            this._rowId = rowId;
            return this;
        },

        setColorFieldName: function(name) {
            this._colorFieldName = name;
            return this;
        },

        _isColoredField: function() {
            var fieldColor = this._getColor();
            return (fieldColor !== null);
        },

        _getColor: function() {
            return this._model.getCellValue(this._rowId, this._colorFieldName);
        },

        render: function($placement) {
            this.__base($placement);

            if (this._isColoredField())
                this._$renderedContent.parent().css('background-color', this._getColor());
        },

        initHandlers: function($dataGridContent) {
            
        }
    },
    {
        
    }
);