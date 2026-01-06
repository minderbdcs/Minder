var ConfirmDespatchDialogView = Backbone.View.extend({

    messageBus: null,
    active: false,
    confirmed: false,

    initialize: function(options) {
        this.messageBus = options.messageBus;

        this.initDialog();
        this.bindMessageBusEvents();
    },

    initDialog: function() {
        var $confirmDialog = this.$el,
            buttonPane;
        $confirmDialog.dialog(
            {
                buttons: {
                    ACCEPT: $.proxy(this.onAcceptButtonClick, this),
                    'HTML': true,
                    CANCEL: $.proxy(this.onCancelButtonClick, this)
                },
                buttonStyle: 'green-button',
                insertHtml:  '&nbsp;',
                autoOpen  : false,
                width     : 400,
                height    : 160,
                resizable : true,
                modal     : false
            }
        ).unbind('dialogopen').bind('dialogopen', $.proxy(this.onDialogOpen, this))
        .unbind('dialogclose').bind('dialogclose', $.proxy(this.onDialogClose, this));

        buttonPane = $confirmDialog.parents('.ui-dialog').find('.ui-dialog-buttonpane');
        buttonPane.find('::nth-child(2)').css('height', 'auto'); //workaround to remove unnecessary height property
        buttonPane.find('::nth-child(3)').find('button').removeClass('green-button').addClass('yellow-button');
    },

    bindMessageBusEvents: function() {
        this.messageBus.onConfirmDespatchRequest(this.onConfirmDespatchRequest, this);
        this.messageBus.onScreenButtonCancel(this.onScreenButtonCancel, this);
        this.messageBus.onScreenButtonAccept(this.onScreenButtonAccept, this);
        this.messageBus.onScreenButtonChecked(this.onScreenButtonChecked, this);

    },

    onScreenButtonCancel: function() {
        if (this.active) {
            this.messageBus.notifyScreenButtonServed();
            this.closeDialog(false);
        }
    },

    onScreenButtonAccept: function() {
        if (this.active) {
            this.messageBus.notifyScreenButtonServed();
            this.closeDialog(true);
        }
    },

    onScreenButtonChecked: function() {
        if (this.active) {
            this.messageBus.notifyScreenButtonServed();
            this.closeDialog(true);
        }
    },

    onConfirmDespatchRequest: function() {
        this.$el.dialog('open');
    },

    onAcceptButtonClick: function(evt) {
        evt.preventDefault();
        this.closeDialog(true);
    },

    onCancelButtonClick: function(evt) {
        evt.preventDefault();
        this.closeDialog(false);
    },

    onDialogOpen: function() {
        //$('.order-check-prompt').hide(); //todo: notify prompt view
        $('#barcode').focus();
        this.active = true;
        this.confirmed = false;
        this.$el.show();
    },

    onDialogClose: function() {
        //$('.order-check-prompt').show();  //todo: notify prompt view
        this.active = false;

        if (this.confirmed) {
            this.messageBus.notifyConfirmDespatchConfirmed();
        } else {
            this.messageBus.notifyConfirmDespatchCancelled();
        }
    },

    closeDialog: function(confirmed) {
        this.confirmed = !!confirmed;
        this.$el.dialog('close');
    }
}); //todo: add screen_button prompt listener