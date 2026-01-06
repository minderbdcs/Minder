Minder_View_SelectionModeSwitcher = $.inherit(
    Minder_View_CheckBox,
    {
        _setValue: function(selectionMode) {
            var
                $input = this._getInput();

            if (selectionMode == 'one') {
                $input.attr('checked', true);
            } else {
                $input.removeAttr('checked');
            }
        },

        onClick: function(evt) {
            var selectionMode = this.getModel().getFieldValue(this.getName()),
                $input = this._getInput();

            if (selectionMode == 'one') {
                this.getModel().setFieldValue(this.getName(), 'all', this);
                $input.removeAttr('checked');
            } else {
                this.getModel().setFieldValue(this.getName(), 'one', this);
                $input.attr('checked', true);
            }
        }
    },
    {

    }
);