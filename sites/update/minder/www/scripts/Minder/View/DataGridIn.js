Minder_View_DataGridIn = $.inherit(
    Minder_View_DataGridElement,
    {
        onInputChange: function(evt) {
            var $target = $(evt.target);

            var rowId = $target.attr('data-row-id') ? $target.attr('data-row-id') : $target.parents().filter('[data-row-id]').attr('data-row-id');
            this.getModel().setRowField(rowId, this.getName(), $target.val(), null);
        },

        initHandlers: function($dataGridContent) {
            $dataGridContent.delegate('input[name="' + this.getName() + '"]', 'change', this.onInputChange);
        }

    },
    {

    }
);