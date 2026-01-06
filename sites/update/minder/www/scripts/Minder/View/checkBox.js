Minder_View_CheckBox = $.inherit(
    Minder_View_Container,
    {
        onModelFieldsChanged: function(evt) {
            if (evt.sender == this) return;
            if (!this._$renderedContent) return;
            this._setValue(this.getValue());
        },

        setModel: function(model) {
            this.__base(model);
            $(this._model)
                .bind(Minder_Model.fieldsChangedEvent, this.onModelFieldsChanged);
            return this;
        },

        _setValue: function(val) {
            if (val) {
                this._getInput().attr('checked', true);
            } else {
                this._getInput().removeAttr('checked');
            }
        },

        onClick: function(evt) {
            var $target = $(evt.target);
            this.getModel().setFieldValue(this.getName(), !!$target.attr('checked'), this);
        },

        _getInput: function() {
            return this._$renderedContent.parent().find('input[name="' + this.getName() + '"]');
        },

        render: function(placement) {
            this.__base(placement);

            this._getInput().click(this.onClick);
        }
    },
    {

    }
);