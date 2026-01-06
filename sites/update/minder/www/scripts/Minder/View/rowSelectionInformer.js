MinderView_RowSelectionInformer = $.inherit(
    Minder_View_DataField,
    {
        setModel: function(model) {
            this.__base(model);
            $(this.getModel()).bind(Minder_Model_DataSet.rowSelectedEvent, this.onRowSelected).bind(Minder_Model_DataSet.dataChangedEvent, this.onDataChanged);
            return this;
        },

        onRowSelected: function(evt) {
            if (evt.sender == this) return;
            this.render(this._$renderedContent);
        },

        onDataChanged: function(evt) {
            if (evt.sender == this) return;
            this.render(this._$renderedContent);
        }
    },
    {

    }
);