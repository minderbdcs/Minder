Minder_View_Button = $.inherit(
    Minder_View_Container,
    {
        _callbacks: null,

        __constructor: function(name, model, $template, settings) {
            this.__base(name, model, $template, settings);
            this._callbacks = {};
        },

        click: function(callback) {
            this._callbacks['click'] = callback;
        },

        getTitle: function() {
            return this.getSetting('SSB_TITLE');
        },

        getButtonElement: function() {
            return this._$renderedContent.parent().find('[name="' + this.getName() + '"]');
        },

        render: function($placement) {
            this.__base($placement);
            this.getButtonElement().data('minderElement', this);
            this.getButtonElement().click(this._callbacks['click']);
        }
    },
    {
        
    }
);