var ProductNotFoundDialogView = Backbone.View.extend({

    messageBus: null,

    active: false,
    prodId: null,
    foundOrders: 0,

    initialize: function(options) {
        this.messageBus = options.messageBus;

        this.initDialog();
        this.bindMessageBusEvents();
    },

    initDialog: function() {
        var $prodIdNotFoundDialog = this.$el,
            buttonPane;

        $prodIdNotFoundDialog.dialog(
            {
                buttons: {
                    CONTINUE: $.proxy(this.onContinueButton, this),
                    'RESET CHECK': $.proxy(this.onResetCheckButton, this),
                    "FIND ORDER": $.proxy(this.onFindOrderButton, this)
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

        buttonPane = $prodIdNotFoundDialog.parents('.ui-dialog').find('.ui-dialog-buttonpane');
        buttonPane.find('::nth-child(2)').css('height', 'auto'); //workaround to remove unnecessary height property
    },

    bindMessageBusEvents: function() {
        this.messageBus.onCheckingProductNotFound(this.onCheckingProductNotFound, this);
        this.messageBus.onScreenButtonContinue(this.onScreenButtonContinue, this);
        this.messageBus.onScreenButtonResetCheck(this.onScreenButtonResetCheck, this);
        this.messageBus.onResetCheckLineStatusesRequest(this.onResetCheckLineStatusesRequest, this);
        this.messageBus.onOrdersSelectionChanged(this.onOrdersSelectionChanged, this);
        this.messageBus.onScreenButtonFindOrder(this.onScreenButtonFindOrder, this);
    },

    onResetCheckLineStatusesRequest: function() {
        if (this.active) {
            this.$el.dialog('close');
        }
    },

    onScreenButtonResetCheck: function() {
        if (this.active) {
            this.messageBus.notifyResetCheckLineStatusesRequest();
        }
    },

    onScreenButtonContinue: function() {
        if (this.active) {
            this.$el.dialog('close');
        }
    },

    onCheckingProductNotFound: function(prodId) {
        var buttonPane = this.$el.parents('.ui-dialog').find('.ui-dialog-buttonpane');

        this.$('.prod_id').text(prodId);
        this.prodId = prodId;

        if (this.foundOrders < 1) {
            buttonPane.find('::nth-child(3)').css('display', 'inherit');
        } else {
            buttonPane.find('::nth-child(3)').css('display', 'none');
        }

        if (!this.active) {
            this.$el.dialog('open');
        }
    },

    onDialogOpen: function() {
        this.messageBus.notifyProductNotFoundDialogOpen();
        $('#barcode').focus();
        this.active = true;
        this.$el.show();
    },

    onDialogClose: function() {
        this.messageBus.notifyProductNotFoundDialogClose();
        this.active = false;
    },

    onContinueButton: function(evt) {
        evt.preventDefault();
        this.$el.dialog('close');
    },

    onResetCheckButton: function(evt) {
        evt.preventDefault();

        if (this.active) {
            this.messageBus.notifyResetCheckLineStatusesRequest();
        }
    },

    onScreenButtonFindOrder: function() {
        if (this.active) {
            if (this.foundOrders < 1) {
                this.$el.dialog('close');
                this.messageBus.notifySearchOrderByProdId(this.prodId);
            }
        }
    },

    onFindOrderButton: function(evt) {
        evt.preventDefault();

        if (this.foundOrders < 1) {
            this.$el.dialog('close');
            this.messageBus.notifySearchOrderByProdId(this.prodId);
        }
    },

    onOrdersSelectionChanged: function(orders) {
        this.foundOrders = orders.getFoundOrdersAmount();
    }
});