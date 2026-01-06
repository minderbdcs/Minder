Minder_View_PaginatorInformer = $.inherit(
    Minder_View_DataField,
    {


        getValue: function() {
            return this.getTotalRowsAmount();
        },

        getTotalRowsAmount: function() {
            return this.getModel().getTotalRows();
        },

        getFirstRowNumber: function() {
            return this.getModel().getRowsOffset() + 1;
        },

        getLastRowNumber: function() {
            return this.getModel().getRowsOffset() + this.getModel().getRowsAmount();
        },

        setModel: function(model) {
            this.__base(model);
            $(this.getModel()).bind(Minder_Model_DataSet.dataChangedEvent, this.onDataSetChanged);
            return this;
        },

        onDataSetChanged: function(evt) {
            if (evt.sender == this) return;
            this.render(this._$renderedContent);
        }
    },
    {
        
    }
);