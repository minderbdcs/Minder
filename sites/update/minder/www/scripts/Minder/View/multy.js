Minder_View_Multy = $.inherit(
    Minder_View_Container,
    {
        _optionsDataSet: null,
        _valueField: 'value',
        _labelField: 'label',

        setOptionsDataset: function(dataSet) {
            this._optionsDataSet = dataSet;
        },

        setModel: function(model) {
            this.__base(model);
            $(this._model)
                .bind(Minder_Model.fieldsChangedEvent, this.onModelFieldsChanged);
            return this;
        },

        getValueField: function() {
            return this._valueField;
        },

        getLabelField: function() {
            return this._labelField;
        },

        getOptions: function() {
            var result = {};
            var valueField = this.getValueField();
            var labelField = this.getLabelField();
            $.each(this._optionsDataSet.getDataRows(), function(index, option) {
                result[option[valueField]] = option[labelField];
            });
            return result;
        },

        onModelFieldsChanged: function(evt) {
            if (evt.sender == this) return;
            if (!this._$renderedContent) return;

            this._$renderedContent.parent().find('select').val(this.getValue());
        },

        onChange: function(evt) {
            var $target = $(evt.target);
            this.getModel().setFieldValue(this.getName(), $target.val(), this);
        },

        render: function($placement) {
            var $renderedContent = this._$template.tmpl(this).insertBefore($placement);
                $placement.remove();
            $renderedContent.data('minderElement', this);
            $renderedContent.parent().find('select').change(this.onChange);
            this._$renderedContent = $renderedContent;
        }
    },
    {

    }
);