Minder_View_Radio = $.inherit(
    Minder_View_Multy,
    {
        onModelFieldsChanged: function(evt) {
            if (evt.sender == this) return;
            if (!this._$renderedContent) return;
            this._setValue(this.getValue());
        },

        _setValue: function(val) {
            this._getInputs().removeAttr('checked').filter('[value="' + val + '"]').attr('checked', true);
        },

        onClick: function(evt) {
            var $target = $(evt.target);
            this.getModel().setFieldValue(this.getName(), $target.val(), this);
        },

        _getInputs: function() {
            return this._$renderedContent.parent().find('input[type="radio"]');
        },

        render: function($placement) {
            var $renderedContent = this._$template.tmpl(this).insertBefore($placement);
            $placement.remove();
            $renderedContent.data('minderElement', this);
            this._$renderedContent = $renderedContent;
            this._getInputs().click(this.onClick);
            this._setValue(this.getValue());
        }
    },
    {

    }
);