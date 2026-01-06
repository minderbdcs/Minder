var ConfirmCarrierDialogView = Backbone.View.extend({
    messageBus: null,

    carriersList: [],
    orderCarrierId: '',

    token: null,
    active: false,
    confirmed: false,

    initialize: function(options) {
        this.messageBus = options.messageBus;
        this.carriersList = options.carriersList || [];

        this.initDialog();
        this.bindMessageBusEvents();
    },

    initDialog: function() {
        var $confirmDialog = this.$el,
            buttonPane;
        $confirmDialog.dialog(
            {
                buttons: {
                    CONTINUE: $.proxy(this.onContinueButton, this),
                    'HTML': true,
                    CANCEL: $.proxy(this.onCancelButton, this)
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

    onContinueButton: function(evt) {
        evt.preventDefault();
        this.closeDialog(true);
    },

    onCancelButton: function(evt) {
        evt.preventDefault();
        this.closeDialog(false);
    },

    onDialogOpen: function() {
        $('#barcode').focus();
        this.$el.show();

        this.active = true;
        this.confirmed = false;
    },

    onDialogClose: function() {
        if (this.confirmed) {
            this.messageBus.notifyCarrierConfirmed();
        } else {
            this.messageBus.notifyCarrierRejected();
        }

        this.active = false;
        this.confirmed = false;
        this.token = null;
    },

    onScreenButtonContinue: function() {
        if (this.active) {
            this.messageBus.notifyScreenButtonServed();
            this.closeDialog(true);
        }
    },

    onScreenButtonCancel: function() {
        if (this.active) {
            this.messageBus.notifyScreenButtonServed();
            this.closeDialog(false);
        }
    },

    bindMessageBusEvents: function() {
        this.messageBus.onCarrierConfirmRequest(this.onCarrierConfirmRequest, this);
        this.messageBus.onOrderShipViaChanged(this.onOrderShipViaChanged, this);
        this.messageBus.onScreenButtonCancel(this.onScreenButtonCancel, this);
        this.messageBus.onScreenButtonContinue(this.onScreenButtonContinue, this);
    },

    validateCarrier: function(carrierId) {
        var
            result = {
                needToConfirm: false,
                notInList: false,
                notSameAsOrder: false
            };

        result.notInList        = (this.carriersList.length > 0) && (this.carriersList.indexOf(carrierId) < 0);
        result.notSameAsOrder   = carrierId.length && this.orderCarrierId.length && carrierId != this.orderCarrierId;
        result.needToConfirm    = !this.confirmed && (result.notInList || result.notSameAsOrder);

        return result;
    },

    onOrderShipViaChanged: function(orderShipVia) {
        this.orderCarrierId = orderShipVia;
    },

    onCarrierConfirmRequest: function(carrierId, token) {
        var validateResult;

        if (this.active) {
            if (this.token === token) {
                this.closeDialog(true);
            }
        } else {
            validateResult = this.validateCarrier(carrierId);

            if (validateResult.needToConfirm) {
                this.showDialog(validateResult, carrierId, token);
            } else {
                this.messageBus.notifyCarrierConfirmed();
            }
        }
    },

    showDialog: function(validateResult, carrierId, token) {
        this.token = token;

        var
            dialog = this.$el;

        dialog.find('.order-carrier-id').text(this.orderCarrierId);
        dialog.find('.selected-carrier-id').text(carrierId);
        dialog.find('.device-carriers-list').text("'" + this.carriersList.join("' or '") + "'");

        if (validateResult.notInList) {
            dialog.find('.carrier-not-in-list').show();
        } else {
            dialog.find('.carrier-not-in-list').hide();
        }

        if (validateResult.notSameAsOrder) {
            dialog.find('.carrier-not-same-as-order').show();
        } else {
            dialog.find('.carrier-not-same-as-order').hide();
        }

        dialog.dialog('open');
    },

    closeDialog: function(confirmed) {
        this.confirmed = !!confirmed;
        this.$el.dialog('close');
    }

}); //todo: add screen_button prompt listener