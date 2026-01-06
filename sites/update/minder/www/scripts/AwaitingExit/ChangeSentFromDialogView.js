Mdr.Pages.AwaitingExit.ChangeSentFromDialogView = (function(Backbone, $, _){
    return Backbone.View.extend({
        initialize: function(options){
            this._messageBus = options.messageBus;
            this._initDialog();

            this._selectedRowsAmount = 0;
            this._selectedCarriers = [];
            this._selectedCarrierService = [];
            this._carrierService = options.carrierService || [];
            this._changeUrl = options.changeUrl || '';

            this._messageBus.onChangeSentFromRequest(this.onChangeSentFromRequest, this);
            this._messageBus.onDespatchedDataReady(this.onDespatchedDataReady, this);
            this._messageBus.onDespatchedRowsSelectionChanged(this.onDespatchedRowsSelectionChanged, this);
        },

        _initDialog: function() {
            var buttonPane;

            this.$el.dialog({
                buttons: {
                    ACCEPT: $.proxy(this._onAcceptButtonClick, this),
                    'HTML': true,
                    CANCEL: $.proxy(this._onCancelButtonClick, this)
                },
                buttonStyle: 'green-button',
                insertHtml:  '&nbsp;',
                autoOpen  : false,
                width     : 400,
                height    : 160,
                resizable : true,
                modal     : false
            }).bind('dialogopen', $.proxy(this._onDialogOpen, this))
                .bind('dialogclose', $.proxy(this._onDialogClose, this));

            buttonPane = this.$el.parents('.ui-dialog').find('.ui-dialog-buttonpane');
            buttonPane.find('::nth-child(2)').css('height', 'auto'); //workaround to remove unnecessary height property
            buttonPane.find('::nth-child(3)').find('button').removeClass('green-button').addClass('yellow-button');
        },

        onDespatchedRowsSelectionChanged: function(sysScreenSelectionState) {
            this._selectedCarriers = sysScreenSelectionState.pickedCarriers || [];
            this._selectedCarrierService = sysScreenSelectionState.pickedCarrierService || [];
            this._selectedRowsAmount = sysScreenSelectionState.selectedRowsTotal || 0;
            this._renderOptions();
        },

        onDespatchedDataReady: function(sysScreenData) {
            this._selectedCarriers = sysScreenData.pickedCarriers || [];
            this._selectedCarrierService = sysScreenData.pickedCarrierService || [];
            this._selectedRowsAmount = sysScreenData.paginator.selectedRows || 0;
            this._renderOptions();
        },

        onChangeSentFromRequest: function() {
            var validateResult = this._validateSelection();

            if (validateResult.hasErrors()) {
                showResponseMessages(validateResult);
            } else {
                this.$el.dialog('open');
            }
        },

        _isCarrierServiceApplicable: function(carrierService) {

            if (this._selectedCarriers.indexOf(carrierService.CARRIER_ID) < 0) {
                return false;
            }

            return !((this._selectedCarrierService.length == 1) && (this._selectedCarrierService.indexOf(carrierService.RECORD_ID) > -1));
        },

        _filterCarrierService: function() {
            return this._carrierService.filter($.proxy(this._isCarrierServiceApplicable, this));
        },

        _renderOptions: function() {

            var options = this._filterCarrierService().map(function(carrierService){
                return '<option value="' + carrierService.RECORD_ID + '">' + carrierService.DESCRIPTION + '</option>';
            }).join();

            this.$('select').empty();
            this.$('select').append($(options));
        },

        _validateSelection: function(result) {
            result = result || new Mdr.ProcessResult();

            if (this._selectedRowsAmount < 1) {
                result.addErrors(['No rows selected. Please select one.']);
            }

            if (this._selectedCarriers.length > 1) {
                result.addErrors(['Cannot change Sent From details for more then one carrier.']);
            }

            return result;
        },

        _validateFormData: function(result) {
            result = result || new Mdr.ProcessResult();

            if (!this.$('[name="carrier-service"]').val()) {
                result.addErrors(['No Carrier Service selected.']);
            }

            return result;
        },

        _onAcceptButtonClick: function() {
            var validateResult = this._validateSelection();

            validateResult = this._validateFormData(validateResult);

            if (validateResult.hasErrors()) {
                showResponseMessages(validateResult);
            } else {
                $.post(
                    this._changeUrl,
                    {carrierServiceId: this.$('[name="carrier-service"]').val()},
                    $.proxy(this._changeSentFromCallback, this),
                    'json'
                );
                this.$el.dialog('close');
            }
        },

        _changeSentFromCallback: function(response) {
            showResponseMessages(response);

            if (response.errors && response.errors.length > 0) {
                return;
            }

            this._messageBus.notifyReloadDataRequest();
        },

        _onCancelButtonClick: function() {
            this.$el.dialog('close');
        },

        _onDialogOpen: function(evt) {
            this._renderOptions();
            $(evt.target).show();
            $('#barcode').focus();
        },

        _onDialogClose: function() {
        }
    });
})(Backbone, jQuery, _);