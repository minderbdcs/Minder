Minder_View_PageSelector = $.inherit(
    Minder_View_Multy,
    {
        _renderedPagesAmount: 0,
        
        getOptions: function() {
            if (typeof this._optionsDataSet != 'undefined')
                return this.__base();

            var result = {};
            var totalPages = Math.min(this.getModel().getTotalPages(), 100);
            this._renderedPagesAmount = totalPages;

            for (var i = 1; i <= totalPages; i++) {
                result[i] = i;
            }

            return result;
        },

        onModelFieldsChanged: function(evt) {
            if (this.getModel().getTotalPages() == this._renderedPagesAmount) {
                if (!this._$renderedContent) return;

                this._$renderedContent.parent().find('select').val(this.getValue());
                return;
            }

            this.render(this._$renderedContent);
        }

    },
    {

    }
);