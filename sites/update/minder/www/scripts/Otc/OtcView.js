var OtcView = Backbone.View.extend({

    initialize: function(options) {
        var issuesOptions,
            returnsOptions,
            auditOptions;

        this.messageBus = new OtcMessageBus();
        this.logServiceUrl = options.serviceUrlList.logServiceUrl || '/minder/service/otc_log.php';
        this.altPressed = false;

        issuesOptions = _.extend({}, _.clone(options), {el: '#issues_tab', messageBus: this.messageBus});
        returnsOptions = _.extend({}, _.clone(options), {el: '#returns_tab', messageBus: this.messageBus});
        auditOptions = _.extend({}, _.clone(options), {el: '#audit_tab', messageBus: this.messageBus});

        this.issuesFormView = new IssuesFormView(issuesOptions);
        this.returnsFormView = new ReturnsFormView(returnsOptions);
        this.auditFormView = new AuditFormView(auditOptions);

        this.bindUiEvents();
        this.bindMessageBusEvents();

    },

    bindUiEvents: function() {
        this.$('#barcode')
            .bind('parse-success', $.proxy(this.onBarcodeSuccess, this))
            .bind('parse-error', $.proxy(this.onBarcodeError, this))
            .bind('parse-starting', $.proxy(this.onBarcodeParseStarting, this));
        this.$('.issues-switch').bind('click', $.proxy(this.onIssuesSwitchClick, this));
        this.$('.returns-switch').bind('click', $.proxy(this.onReturnsSwitchClick, this));
        this.$('.audit-switch').bind('click', $.proxy(this.onAuditSwitchClick, this));
        this.$el.bind('keypress', $.proxy(this.onKeyPress, this));

        this.$el.bind('keydown', $.proxy(this.onKeyDown, this));
        this.$el.bind('keyup', $.proxy(this.onKeyUp, this));
        this.listenTo(Backbone, 'tool-images-saved', this.onToolImagesSaved);
        this.listenTo(Backbone, 'close-opened-dialogs-request', this.onCloseOpenedDialogsRequest);
    },

    bindMessageBusEvents: function() {
        this.messageBus.onLogRequest(this.log, this);
        this.messageBus.onQueryBorrowerRequest(this.onQueryBorrowerRequest, this);
        this.messageBus.onQueryLocationRequest(this.onQueryLocationRequest, this);
        this.messageBus.onQueryCostCenterRequest(this.onQueryCostCenterRequest, this);
        this.messageBus.onQueryToolRequest(this.onQueryToolRequest, this);
        this.messageBus.onQueryLegacyToolCodeRequest(this.onQueryLegacyToolCodeRequest, this);
        this.messageBus.onCaptureToolImagesRequest(this.onCaptureToolImagesRequest, this);
        this.messageBus.onCloseOpenedDialogsRequest(this.onCloseOpenedDialogsRequest, this);
    },

    onCloseOpenedDialogsRequest: function() {
        Backbone.trigger('close-capture-image-request');
        closeToolDialog();
        closeProductDialog();
        closeBorrowerDialog();
        closeCostCentreDialog();
        closeHistoryDialog();
        closeDisposeToolDialog();
        closeLoans();
        closeImportAuditDialog();

    },

    onCaptureToolImagesRequest: function(tool) {
        Backbone.trigger('capture-image-request', tool);
    },

    onToolImagesSaved: function() {
        this.messageBus.notifyReloadToolRequest();
    },

    onKeyPress: function(evt) {
        var $target = $(evt.target),
            value = $target.val().replace(/ /g,"");

        if (evt.keyCode == 13 && value !== '') {
            $(evt.target).trigger('enter-key-pressed');
        }
    },

    onKeyDown: function(evt) {
        if (evt.keyCode == 18) {
            this.altPressed = true;
            return;
        }

        if (!this.altPressed) {
            return;
        }

        switch (evt.keyCode) {
            case 82:
                this.messageBus.notifySwitchToReturnsRequest();
                return false;
            case 73:
                this.messageBus.notifySwitchToIssuesRequest();
                return false;
            case 83:
                console.log('save');
                //todo: save state
                return false;
            case 67:
                console.log('cancel');
                //todo: clear state
                return false;
        }
    },

    onKeyUp: function(evt) {
        if (evt.keyCode == 18) {
            this.altPressed = false;
        }
    },

    onIssuesSwitchClick: function() {
        this.messageBus.notifySwitchToIssuesRequest();
    },

    onReturnsSwitchClick: function() {
        this.messageBus.notifySwitchToReturnsRequest();
    },

    onAuditSwitchClick: function() {
        this.messageBus.notifySwitchToAuditRequest();
    },

    defaultPressBarcodeButton: function(barcode) {
        if (isConfirmToolTransferDialogActive()) {
            pressConfirmToolTransferDialogButton(barcode);
        } else if (isConfirmExpirationDialogActive()) {
            pressConfirmDialogButton(barcode);
        } else {
            this.issuesFormView.pressBarcodeButton(barcode);
            this.returnsFormView.pressBarcodeButton(barcode);
            this.auditFormView.pressBarcodeButton(barcode);
        }
    },

    pressBarcodeButton: function(barcode) {
        this.log('Press Barcode Button: ' + barcode);

        if (barcode == 'CANCEL' || barcode == 'CLOSE') {
            if (isListLoansActive()) {
                closeLoans();
            } else if (isConfirmToolTransferDialogActive()) {
                closeToolTransferDialog();
            } else if (isConfirmExpirationDialogActive()) {
                closeExpirationDialog();
            } else if (isHistoryDialogActive()) {
                closeHistoryDialog();
            } else if (isDisposeToolDialogActive()) {
                closeDisposeToolDialog();
            } else if (isAddBorrowerDialogActive()) {
                closeBorrowerDialog();
            }else if (isAddLocationDialogActive()) {
                closeLocationDialog();
            } 
	    else if (isAddProductDialogActive()) {
                closeProductDialog();
            } else if (isAddToolDialogActive()) {
                closeToolDialog();
            } else {
                this.defaultPressBarcodeButton(barcode);
            }
        }
	else if (barcode == 'SAVE') {
            if (isAddLocationDialogActive()) {
                saveLocation();
            } else if (isAddBorrowerDialogActive()) {
                saveBorrower();
            } else {
                this.defaultPressBarcodeButton(barcode);
            }
        }

	 else {
            this.defaultPressBarcodeButton(barcode);
        }
    },

    onBarcodeSuccess: function(evt) {
        $('#globalSearchMessage').text(evt.parseResult.paramDesc.param_name + ': ' + evt.parseResult.paramDesc.param_filtered_value);
        $(evt.target).val('').focus();

        switch (evt.parseResult.paramDesc.param_name) {
            case 'SCREEN_BUTTON':
                this.pressBarcodeButton(evt.parseResult.paramDesc.param_filtered_value);
                break;
            case 'COST_CENTER_CODE':
            case 'COST_CENTRE_CODE':
                this.messageBus.notifyCostCenterBarcode(evt.parseResult.paramDesc.param_filtered_value);
                break;
            case 'BADGE_CODE':
                this.messageBus.notifyBorrowerBarcode(evt.parseResult.paramDesc.param_filtered_value);
                break;
            case 'LOCATION':
                var location = evt.parseResult.paramDesc.param_filtered_value;
                if (location.slice(0, 2) == 'XB') {
                    this.messageBus.notifyBorrowerBarcode(location.slice(2));
                } else {
                    this.messageBus.notifyLocationBarcode(location);
                }

                break;
            case 'BARCODE':
            case 'SSN_CODE':
            case 'NON_UNIQUE_SSN_CODE':
            case 'ALTBARCODE':
                this.messageBus.notifyToolBarcode(evt.parseResult.paramDesc.param_filtered_value);
                break;
            case 'ALT_BARCODE':
                this.messageBus.notifyToolAltBarcode(evt.parseResult.paramDesc.param_filtered_value);
                break;
            case 'PROD_13':
            case 'PRODUCT_CODE':
            case 'PROD_INTERNAL':
            case 'PROD_UPC12':
            case 'ALT_PROD':
                this.messageBus.notifyConsumableBarcode(evt.parseResult.paramDesc.param_filtered_value);
                break;
            case 'QTY_CODE':
                this.messageBus.notifyIssueQtyBarcode(evt.parseResult.paramDesc.param_filtered_value);
                break;
            case 'DEBUG':
                break; //this will be served by Query Log
            case 'DESCRIPTION':
                this.messageBus.notifyDescriptionBarcode(evt.parseResult.paramDesc.param_filtered_value);
                break;
            default:
                switch (evt.parseResult.paramDesc.param_type) {
                    case 'PROD_ID':
                        this.messageBus.notifyConsumableBarcode(evt.parseResult.paramDesc.param_filtered_value);
                        break;
                    default:
                        showErrors(['Unsupported Data Identifier "' + evt.parseResult.paramDesc.param_name + '"']);
                }
        }
    },

    onBarcodeError: function(evt) {
        $(evt.target).val('').focus();
        showErrors(['Unsupported Data Identifier "' + evt.parseResult.errorMsg + '"']);
        $('#globalSearchMessage').text('');
    },

    onBarcodeParseStarting: function(evt) {
        otcLog('Label Scanned: ' + evt.scannedValue);
    },

    onQueryBorrowerRequest: function(borrowerId) {
        Otc.Dlg.Query.fastQueryBorrower(borrowerId);
    },

    onQueryLocationRequest: function(location) {
        Otc.Dlg.Query.fastQueryLocation(location);
    },

    onQueryCostCenterRequest: function(costCenter) {
        Otc.Dlg.Query.fastQueryCostCenter(costCenter);
    },

    onQueryToolRequest: function(toolId) {
        Otc.Dlg.Query.fastQueryTool(toolId);
    },

    onQueryLegacyToolCodeRequest: function(legacyCode) {
        Otc.Dlg.Query.fastQueryAltBarcode(legacyCode);
    },

    log: function(message) {
        $.post(
            this.logServiceUrl,
            {'message': message, 'timestamp' : (new Date()).toISOString()}
        );
    }
});
