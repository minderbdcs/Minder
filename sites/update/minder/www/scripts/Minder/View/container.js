Minder_View_Container = $.inherit(
    {
        _settings: null,
        _name: null,
        _model: null,
        _$template: null,
        _subViews: null,
        _$renderedContent: null,

        __constructor: function(name, model, $template, settings) {
            this.setModel(model).setTemplate($template).setName(name).setSettings(settings);
            this._subViews = {};
        },

        getSetting: function(name) {
            if (typeof this._settings == 'undefined' || this._settings == null) return null;
            if (typeof this._settings[name] == 'undefined') return null;

            return this._settings[name];
        },

        setSettings: function(settings) {
            this._settings = settings;
            return this;
        },

        setModel: function(model) {
            this._model = model;
            return this;
        },

        setTemplate: function($template) {
            this._$template = $template;
            return this;
        },

        addSubView: function(subView) {
            this._subViews[subView.getName()] = subView;
            return this;
        },

        setName: function(name) {
            this._name = name;
            return this;
        },

        getName: function() {
            return this._name;
        },

        getModel : function() {
            return this._model;
        },

        getValue : function() {
            return this.getModel().getFieldValue(this.getName());
        },

        getWidth: function() {
            return (this._settings.SSV_FIELD_WIDTH == null) ? 0 : this._settings.SSV_FIELD_WIDTH;
        },

        getWidthDimension: function() {
            var inputMethodExt = this._settings.SSV_INPUT_METHOD_EXT || {};
            return  inputMethodExt.columnDimensionType || 'px';
        },

        getWidthString: function() {
            var width = this.getWidth();
            return (width) ? this.getWidth() + this.getWidthDimension() : '';
        },

        getSubViews: function() {
            return this._subViews;
        },

        getOrder: function() {
            var orderByFieldName = this.getSetting('ORDER_BY_FIELD_NAME');
            if (orderByFieldName === null) return 0;

            return this.getSetting(orderByFieldName);
        },

        getElementStyle: function() {
            return this.getSetting('style');
        },

        getSortedSubViews: function() {
            var orderArray = [];
            for (i in this._subViews) {
                orderArray.push(this._subViews[i]);
            }

            orderArray.sort(function(a, b){
                return a.getOrder() - b.getOrder();
            });

            return orderArray;
        },

        render: function($placement) {
            var $renderedContent = this._$template.tmpl(this).insertBefore($placement);
            $placement.remove();
            $renderedContent.data('minderElement', this);

            $.each(this._subViews, function(name, subView){
                var $subViewPlacement = $renderedContent.parent().find('.minder-element.' + name);
                if ($subViewPlacement.length > 0)
                    subView.render($subViewPlacement);
            });

            $renderedContent.data('minderElement', this);
            this._$renderedContent = $renderedContent;
        },

        __dump: function() {
            if(typeof console !== 'undefined' && typeof console.debug == 'function') console.debug(arguments);
        }
    },
    {
    }
);