var SelectionMixin = {
    getSelectedAmount: function() {
        return parseInt(this.$('.selected_count').html()) || 0;
    },

    updateSelectionState: function() {
        var totalRowsOnPage    = 0;
        var rowsSelectedOnPage = 0;
        var totalRows          = parseInt(this.$('.total_count').html()) || 0;
        var totalRowsSelected  = this.getSelectedAmount();

        this.$('.row_selector').each(function() {
            totalRowsOnPage++;
            if ($(this).attr('checked') === true) {
                rowsSelectedOnPage++;
            }
        });

        this.$('.select_all_rows').each(function (){
            if (totalRowsOnPage == rowsSelectedOnPage) {
                $(this).attr('checked', true);
            } else {
                $(this).removeAttr('checked');
            }
        });

        this.$('.select_complete').each(function (){
            if (totalRows == totalRowsSelected) {
                $(this).attr('checked', true);
            } else {
                $(this).removeAttr('checked');
            }
        });

    }
};