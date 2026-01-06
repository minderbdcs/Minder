Minder_View_DataGridSelectRow = $.inherit(
    Minder_View_Container,
    {
        _rowId: null,

        __constructor: function(name, model, $template, settings) {
            this.__base(name, model, $template, settings);
            this.forgetRenderedContent();
        },

        setModel: function(model) {
            $(model).bind(Minder_Model_DataSet.rowSelectedEvent + '.' + this.__self.eventNamespace, this.onSelectionChanged);
            $(model).bind(Minder_Model.fieldsChangedEvent + '.' + this.__self.eventNamespace, this.onModelFieldsChanged);
            return this.__base(model);
        },

        onModelFieldsChanged: function(evt) {
            if (evt.sender == this) return;
            if (!this._$renderedContent) return;

            this._updateState(this._getSelectionMode());
        },

        _getSelectionMode: function() {
            var
                model = this.getModel();

            return model ? model.getSelectionMode() : 'all';
        },

        _updateState: function(selectionMode) {
            var rowSelector = this;

            $.each(this._$renderedContent, function(index, $renderedContent) {
                var rowId = $renderedContent.data('row-id');

                if (rowId == 'all') {
                    if (selectionMode == 'one') {
                        $renderedContent.attr('disabled', true);
                    } else {
                        $renderedContent.removeAttr('disabled');
                    }
                }
            });
        },

        forgetRenderedContent: function() {
            this._$renderedContent = {};
        },

        setRowId: function(rowId) {
            this._rowId = rowId;
            return this;
        },

        getRowId: function() {
            return this._rowId;
        },

        rowSelected: function() {
            return this._model.rowSelected(this._rowId);
        },

        updateRowSelection: function() {
            var rowSelector = this;

            $.each(this._$renderedContent, function(index, $renderedContent) {
                var rowId = $renderedContent.data('row-id');

                if (rowSelector._model.rowSelected(rowId)) {
                    $renderedContent.parent().find('input:checkbox').attr('checked', 'checked');
                } else {
                    $renderedContent.parent().find('input:checkbox').removeAttr('checked');
                }
            });
        },

        onSelectionChanged: function(evt) {
            this.updateRowSelection();
        },

        render: function($placement) {
            var $renderedContent = this._$template.tmpl(this).insertBefore($placement);
            $placement.remove();
            $renderedContent.data('minderElement', this);
            $renderedContent.data('row-id', this.getRowId());
            $renderedContent.click(this._onRowSelectorClick);

            this._$renderedContent[this.getRowId()] = $renderedContent;

            if (this.getRowId() == 'all') {
                if (this._getSelectionMode() == 'one') {
                    $renderedContent.attr('disabled', true);
                } else {
                    $renderedContent.removeAttr('disabled');
                }
            }
        },

        _onRowSelectorClick: function(evt) {
            var $target = $(evt.target);
            var rowId  = $target.data('row-id');

            this._model.selectRow(rowId, this, $target.attr('checked'));
        },

        initHandlers: function($dataGridContent) {

        }
    },
    {
        //static members
        eventNamespace: 'Minder_View_DataGridSelectRow'
    }
);