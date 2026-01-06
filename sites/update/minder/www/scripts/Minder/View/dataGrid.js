Minder_View_DataGrid = $.inherit(
    Minder_View_Container,
    {
        _fields: null,
        _$renderedContent: null,

        __constructor: function(name, model, $template, settings){
            this.__base(name, model, $template, settings);
            this._fields = {};
        },

        setModel: function(model) {
            this.__base(model);
            $(this._model)
                .bind(Minder_Model_DataSet.dataChangedEvent, this.onDataChanged)
                .bind(Minder_Model_SysScreen.dataUpdateStartedEvent, this.onDataUpdateStarted);
            return this;
        },

        addField: function(fieldView, headerView) {
            this._fields[fieldView.getName()] = {
                'field': fieldView,
                'header': headerView
            };
        },

        getFields: function() {
            var result = [];

            for (i in this._fields)
                result.push(this._fields[i]);

            result.sort(function(a, b){
                return a.field.getOrder() - b.field.getOrder();
            });

            return result;
        },

        getRows: function() {
            return this._model.getDataRows();
        },

        render: function($placement) {
            var $renderedContent = this._$template.tmpl(this).insertBefore($placement);
            $placement.remove();
            $renderedContent.data('minderElement', this);

            $.each(this._fields, function(name, fieldStruct){
                var $headerPlacement = $renderedContent.find('.minder-grid-view-header.FIELD_ID_' + name);
                if ($headerPlacement.length > 0)
                    fieldStruct.header.render($headerPlacement);
            });

            var tmpRows = this.getRows();
            var tmpFields = this._fields;

            $.each(tmpRows, function(rowId, row){
                $.each(tmpFields, function(name, fieldStruct){
                    var $fieldPlacement = $renderedContent.find('.minder-grid-view-field.FIELD_ID_' + name + '[data-row-id="' + row.__rowId + '"]');
                    if ($fieldPlacement.length > 0) {
                        fieldStruct.field.setRowId(row.__rowId).render($fieldPlacement);
                    }
                });
            });

            $renderedContent.find('tbody tr:even').addClass('even');
            $renderedContent.find('tbody tr:odd').addClass('odd');

            this._$renderedContent = $renderedContent;

            $.each(tmpFields, function(name, fieldStruct){
                fieldStruct.field.initHandlers($renderedContent);
            });
        },

        onDataChanged: function(evt) {
            if (evt.sender == this) return;

            if (this._$renderedContent === null) return;

            this.render(this._$renderedContent);
//            this._$renderedContent.unblock();
        },

        onDataUpdateStarted: function(evt) {
//            if (evt.sender == this) return;
//
//            if (this._$renderedContent === null) return;
//
//            this._$renderedContent.block('Loading Data...');
        }
    },
    {
        //static members
        eventNamespace: 'Minder_View_DataGrid'
    }
);