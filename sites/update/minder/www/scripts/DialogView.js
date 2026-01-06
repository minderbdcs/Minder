var DialogView = Backbone.View.extend({
    constructor: function(options) {
        Backbone.View.apply(this, arguments);
        this.initUi(options);
    },

    initUi: function(options) {
        this.trigger('on-before-init-ui', options);
        this._doInitUi(options);
        this.trigger('on-after-init-ui', options);
    },

    _doInitUi: function(options) {
        var
            dialogOptions = _.extend({
                buttons: {},
                buttonStyle: 'green-button',
                insertHtml:  '&nbsp;',
                autoOpen  : false,
                width     : 400,
                height    : 160,
                resizable : false,
                modal     : false
            }, options),
            buttonPane;

        this.$el.dialog(dialogOptions)
            .unbind('dialogopen').bind('dialogopen', $.proxy(this._onUiDialogOpen, this))
            .unbind('dialogclose').bind('dialogclose', $.proxy(this._onUiDialogClose, this));

        buttonPane = this.$el.parents('.ui-dialog').find('.ui-dialog-buttonpane');
        buttonPane.find('::nth-child(2)').css('height', 'auto'); //workaround to remove unnecessary height property
    },

    _onUiDialogOpen: function(uiEvent) {
        this.trigger('dialog:before-open', uiEvent);
        this.$el.show();
        this.trigger('dialog:open', uiEvent);
    },

    _onUiDialogClose: function(uiEvent) {
        this.trigger('dialog:close', uiEvent);
    },

    openDialog: function() {
        this.$el.dialog('open');
    },

    closeDialog: function() {
        this.$el.dialog('close');
    }

});