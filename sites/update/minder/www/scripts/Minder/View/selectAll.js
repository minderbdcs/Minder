Minder_View_SelectAll = $.inherit(
    Minder_View_CheckBox,
    {
        onModelFieldsChanged: function(evt) {
            if (evt.sender == this) return;
            if (!this._$renderedContent) return;
            this._setValue(this.getValue());
            this._updateState(this._getSelectionMode());
        },

        _getSelectionMode: function() {
            return this.getModel().getSelectionMode();
        },

        _updateState: function(selectionMode) {
            if (selectionMode == 'one') {
                this._getInput().attr('disabled', true);
            } else {
                this._getInput().removeAttr('disabled');
            }
        }
    },
    {

    }
);