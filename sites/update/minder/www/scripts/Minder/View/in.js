Minder_View_In = $.inherit(
    Minder_View_Container,
    {
        getValue: function() {
            var value = this.getModel().getFieldValue(this.getName());

            if (!value)
                return '';

            return value;
        },

        setModel: function(model) {
            this.__base(model);
            $(this.getModel()).bind(Minder_Model.fieldsChangedEvent, this.onRowSelected);
            return this;
        },

        onInputChange: function(evt) {
            var $target = $(evt.target);
            this.getModel().setFieldValue(this.getName(), $target.val(), this);
        },

        _getInput: function() {
            return this._$renderedContent.parent().find('input[name="' + this.getName() + '"]');
        },

        onRowSelected: function(evt) {
            if (evt.sender == this) return;
            this._getInput().val(this.getValue());
        },

        render: function(placement) {
            this.__base(placement);

            this._getInput().bind('change', this.onInputChange);
        }
    },
    {

    }
);