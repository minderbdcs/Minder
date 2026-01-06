Minder_View_DataField = $.inherit(
    Minder_View_Container,
    {
        getValue: function() {
            var value = this.getModel().getFieldValue(this.getName());

            if ((typeof value == 'undefined') || (value == null))
                return '';

            return value;
        },

        setModel: function(model) {
            this.__base(model);
            $(this.getModel()).bind(Minder_Model.fieldsChangedEvent, this.onFieldsChanged);
            return this;
        },

        onFieldsChanged: function(evt) {
            if (evt.sender == this) return;
            if (!this._$renderedContent) return;
            this.render(this._$renderedContent);
        }
    },
    {

    }
);