var ConfirmDataIdentifierDialogView = Backbone.View.extend({

    messageBus: null,
    active: false,
    confirmed: false,

    initialize: function(options) {
        this.messageBus = options.messageBus;

        this.initDialog();
        this.bindUIEvents();
        this.bindMessageBusEvents();
    },

    initDialog: function() {
        var $confirmDialog = this.$el,
            buttonPane;
        $confirmDialog.dialog(
            {
                buttons: {
                    CANCEL: $.proxy(this.onCancelButtonClick, this)
                },
                buttonStyle: 'green-button',
                insertHtml:  '&nbsp;',
                autoOpen  : false,
                width     : 650,
                height    : 450,
                resizable : false,
                modal     : false
            }
        ).unbind('dialogopen').bind('dialogopen', $.proxy(this.onDialogOpen, this))
            .unbind('dialogclose').bind('dialogclose', $.proxy(this.onDialogClose, this));

        buttonPane = $confirmDialog.parents('.ui-dialog').find('.ui-dialog-buttonpane');
        buttonPane.find('::nth-child(2)').css('height', 'auto'); //workaround to remove unnecessary height property
        buttonPane.find('::nth-child(3)').find('button').removeClass('green-button').addClass('yellow-button');
    },

    bindUIEvents: function() {
        this.$el.delegate('input', 'click', $.proxy(this.onUiDataIdentifierSelect, this));
    },

    onUiDataIdentifierSelect: function(evt) {
        this.messageBus.notifyDataIdentifierConfirmed(JSON.parse( decodeURIComponent($(evt.target).val())));
        this.closeDialog();
    },

    bindMessageBusEvents: function() {
        this.messageBus.onConfirmDataIdentifierRequest(this.onConfirmDataIdentifierRequest, this);
        this.messageBus.onRevokeConfirmDataIdentifierRequest(this.onRevokeConfirmDataIdentifierRequest, this);
        this.messageBus.onScreenButtonCancel(this.onScreenButtonCancel, this);
    },

    onConfirmDataIdentifierRequest: function(dataIdentifierList) {
        this._clearIdentifiers();
        this._renderIdentifiers(dataIdentifierList);

        if (!this.active) {
            this.$el.dialog('open');
        }
    },

    onRevokeConfirmDataIdentifierRequest: function() {
        if (this.active) {
            this.closeDialog();
        }
    },

    _clearIdentifiers: function() {
        this.$('.data-identifier-list').empty();
    },

    _renderIdentifiers: function(dataIdentifierList) {
        var $container = this.$('.data-identifier-list'),
            classes = ['odd', 'even'];

        dataIdentifierList.forEach(function(dataIdentifier){
            var value = encodeURIComponent(JSON.stringify(dataIdentifier)),
                rowClass = classes.shift();
            classes.push(rowClass);
            $container.append('<tr class="' + rowClass + '">' +
                '<td>' +
                    '<label>' +
                        '<input type="radio" name="data-identifier-dialog-option" value="' + value + '">' +
                        dataIdentifier.param_name + ': ' + dataIdentifier.param_filtered_value +
                    '</label>' +
                '</td>' +
            '</tr>');
        });
    },

    onScreenButtonCancel: function() {
        if (this.active) {
            this.messageBus.notifyScreenButtonServed();
            this.closeDialog();
        }
    },

    onCancelButtonClick: function(evt) {
        evt.preventDefault();
        this.closeDialog();
    },

    onDialogOpen: function() {
        $('#barcode').focus();
        this.active = true;
        this.$el.show();
    },

    onDialogClose: function() {
        this.active = false;
    },

    closeDialog: function() {
        this.$el.dialog('close');
    }
});