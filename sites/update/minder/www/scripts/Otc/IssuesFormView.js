var IssuesFormView = Backbone.View.extend(_.extend({
    active: true,

    initialize: function(options) {
        var modelOptions = _.extend({}, _.clone(options), {parse: true});

        this.toConfirm = [];
        this.model = new ProcessState(options.issuesState || {processId: 'ISSUES'}, modelOptions);
        this.messageBus = options.messageBus || new OtcMessageBus();
        this.renderedRowAmount = 0;

        this.bindUiEvents();
        this.bindModelEvents();
        this.bindMessageBusEvents();

        this.render();

        this.show();
    },

    render: function() {
        this.onChargeToChanged(this.model, this.model.get('chargeTo'));
        this.onIssueToChanged(this.model, this.model.get('issueTo'));
        this.onItemChanged(this.model, this.model.get('item'));
        this.onIssueQtyChanged(this.model, this.model.get('issueQty'));
        this.onIssueQtyDescriptionChanged(this.model, this.model.get('issueQtyDescription'));
        this.onCommittedChanged(this.model, this.model.get('committed'));
    },

    bindModelEvents: function() {
        this.listenTo(this.model, 'change:chargeTo', this.onChargeToChanged);
        this.listenTo(this.model, 'change:issueTo', this.onIssueToChanged);
        this.listenTo(this.model, 'change:item', this.onItemChanged);
        this.listenTo(this.model, 'change:issueQty', this.onIssueQtyChanged);
        this.listenTo(this.model, 'change:issueQtyDescription', this.onIssueQtyDescriptionChanged);
        this.listenTo(this.model, 'change:committed', this.onCommittedChanged);
        this.listenTo(this.model, 'change:toolTransaction', this.onToolTransactionChanged);
        this.listenTo(this.model.history, 'add', this.renderHistoryRow);
    },

    bindUiEvents: function() {
        this.$('#field1_issues').bind('enter-key-pressed', $.proxy(this.onUiChargeToEnterKeyPressed, this));
        this.$('#field2_issues').bind('enter-key-pressed', $.proxy(this.onUiIssueToEnterKeyPressed, this));
        this.$('#field3_issues').bind('enter-key-pressed', $.proxy(this.onUiItemEnterKeyPressed, this));
        this.$('#field4_issues').bind('enter-key-pressed', $.proxy(this.onUiIssueQtyEnterKeyPressed, this));

        this.$('[value="SAVE"]').click($.proxy(this.onSaveButtonClick, this));
        this.$('[value="CANCEL"]').click($.proxy(this.onCancelButtonClick, this));
        this.$('[value="Capture Image"]').click($.proxy(this.onCaptureImageButtonClick, this));
        this.$('[value="Add Product"]').click($.proxy(this.onAddProductButtonClick, this));
        this.$('[value="Add Tool"]').click($.proxy(this.onAddToolButtonClick, this));
        this.$('[value="Add Borrower"]').click($.proxy(this.onAddBorrowerButtonClick, this));
        this.$('.add-cost-center-button').click($.proxy(this.onAddCostCenterButtonClick, this));
        this.$('[value="Tool History"]').click($.proxy(this.onToolHistoryButtonClick, this));
        this.$('[value="Dispose Tool"]').click($.proxy(this.onDisposeToolButtonClick, this));
        this.$('[value="Print Tool"]').click($.proxy(this.onPrintToolButtonClick, this));
        this.$('[value="Print Borrower"]').click($.proxy(this.onPrintBorrowerButtonClick, this));
        this.$('[value="Print Product"]').click($.proxy(this.onPrintProductButtonClick, this));
        this.$('[value="Add Location"]').click($.proxy(this.onAddNewLocationButtonClick, this));

        $('#confirm_expiration_dialog').unbind('confirm.otc').bind('confirm.main', $.proxy(this.onExpirationConfirmed, this));

        addToolDialogOnCreate($.proxy(this.onToolCreated, this), 'otc-issues-tab');
        addProductDialogOnCreate($.proxy(this.onProductCreated, this), 'otc-issues-tab');
        addCostCentreAddedListener('otc-issues-tab', $.proxy(this.onCostCenterCreated, this));
        setToolTransferConfirmListener('otc-main', $.proxy(this.onTransferConfirmed, this));
        Otc.Dlg.Query.addRowClickListener($.proxy(this.onQueryDialogRowClick, this), 'issues-view');
    },

    bindMessageBusEvents: function() {
        this.messageBus.onSwitchToIssuesRequest(this.onSwitchToIssuesRequest, this);
        this.messageBus.onSwitchToReturnsRequest(this.onSwitchToReturnsRequest, this);
        this.messageBus.onSwitchToAuditRequest(this.onSwitchToAuditRequest, this);
        this.messageBus.onCostCenterBarcode(this.onCostCenterBarcode, this);
        this.messageBus.onBorrowerBarcode(this.onBorrowerBarcode, this);
        this.messageBus.onLocationBarcode(this.onLocationBarcode, this);
        this.messageBus.onToolBarcode(this.onToolBarcode, this);
        this.messageBus.onToolAltBarcode(this.onToolAltBarcode, this);
        this.messageBus.onConsumableBarcode(this.onConsumableBarcode, this);
        this.messageBus.onIssueQtyBarcode(this.onIssueQtyBarcode, this);
        this.messageBus.onDescriptionBarcode(this.onDescriptionBarcode, this);
        this.messageBus.onReloadToolRequest(this.onReloadToolRequest, this);
    },

    onReloadToolRequest: function() {
        if (this.active) {
            this.model.reloadTool();
        }
    },

    onPrintProductButtonClick: function() {
        this.messageBus.notifyCloseOpenedDialogsRequest();
        printProduct();
    },

    onPrintBorrowerButtonClick: function() {
        this.messageBus.notifyCloseOpenedDialogsRequest();
        printBorrower();
    },

    onPrintToolButtonClick: function() {
        this.messageBus.notifyCloseOpenedDialogsRequest();
        printTool();
    },

    onDisposeToolButtonClick: function() {
        if (isDisposeToolDialogActive()) {
            closeDisposeToolDialog();
        } else {
            this.messageBus.notifyCloseOpenedDialogsRequest();
            disposeTool();
        }
    },

    onToolHistoryButtonClick: function() {
        if (isHistoryDialogActive()) {
            closeHistoryDialog();
        } else {
            this.messageBus.notifyCloseOpenedDialogsRequest();
            showHistory();
        }
    },

    onAddCostCenterButtonClick: function() {
        if (isAddCostCentreDialogActive()) {
            closeCostCentreDialog();
        } else {
            this.messageBus.notifyCloseOpenedDialogsRequest();
            //alert('open ccd');
            openCostCentreDialog();
        }
    },

//edit

    onAddNewLocationButtonClick: function() {


    if($('#limit_warehouse').val()=='all'){

        $('.restore-errors').remove();
        var error_message='<ul class="restore-errors issues">'+
                    '<li id="restore-errors-li">Please select one Warehouse</li>'+
                '</ul>';

        $("#top-message").append(error_message);
        return false;

    }   

    else{

        $('.restore-errors').remove();

    }


        if (isAddLocationDialogActive()) {
            closeLocationDialog();
        } else {
            //alert('else open cost center');
            this.messageBus.notifyCloseOpenedDialogsRequest();
            openLocationDialog();
        }
    },

//edit    

    onAddBorrowerButtonClick: function() {
        if (isAddBorrowerDialogActive()) {
            closeBorrowerDialog();
        } else {
            this.messageBus.notifyCloseOpenedDialogsRequest();
            openBorrowerDialog();
        }
    },

    onAddToolButtonClick: function() {
        if (isAddToolDialogActive()) {
            closeToolDialog();
        } else {
            this.messageBus.notifyCloseOpenedDialogsRequest();
            openToolDialog();
        }
    },

    onAddProductButtonClick: function(evt) {
        if (isAddProductDialogActive()) {
            closeProductDialog();
        } else {
            this.messageBus.notifyCloseOpenedDialogsRequest();
            openProductDialog();
        }
    },

    onCaptureImageButtonClick: function(evt) {
        var item = this.model.get('item');
        evt.preventDefault();

        if (!item.existed || item.itemType != 'TOOL') {
            showErrors(['Scan tool first.']);
        } else {
            this.messageBus.notifyCaptureToolImagesRequest(item);
        }
    },

    _renderRow: function(rowNo, date, productCode, ssnId, qty, description, costCentre, location) {
        this.$('#lines_issues').prepend('<tr class="' + (rowNo % 2 == 0 ? 'even' : 'odd') + '">' +
        '<td><input type="checkbox" name =""></td>' +
        '<td>' + date + '</td>' +
        '<td>' + (productCode != undefined ? productCode : '') + '</td>' +
        '<td>' + (ssnId != undefined ? ssnId : '') + '</td>' +
        '<td>' + qty + '</td>' +
        '<td>' + description + '</td>' +
        '<td>' + (costCentre != undefined ? costCentre : '') + '</td>' +
        '<td>' + (location != undefined ? location : '') + '</td>' +
        '</tr>');
    },

    renderHistoryRow: function(row) {
        var json = row.toJSON();
        if (json.save_qty!=undefined && json.save_desc!=undefined ) {

            if (json.loaned_total != undefined) {					// By #413
                this.$('#field5_issues')[0].value = json.loaned_total; 	//
            }														//

            this._renderRow(this.renderedRowAmount++, json.save_datetime, json.save_prod_code, json.save_ssn_id, json.save_qty, json.save_desc, json.save_cc, json.save_location);
        }

        if (json.save2_qty!=undefined && json.save2_desc!=undefined ) {

            if (json.loaned_total != undefined) {					// By #413
                this.$('#field5_issues')[0].value = json.loaned_total; 	//
            }														//

            this._renderRow(this.renderedRowAmount++, json.save_datetime, json.save2_prod_code, json.save2_ssn_id, json.save2_qty, json.save2_desc, json.save2_cc, json.save2_location);
        }

        this.$('#issues_count').text(this.renderedRowAmount);
    },

    onQueryDialogRowClick: function(evt) {
        if (evt.queryData.param_type == 'BORROWER_PARTIAL') {
            this._setBorrower(evt.rowData.RECORD_ID, 'K');
            Otc.Dlg.Query.close();
        }
    },

    pressBarcodeButton: function(barcode) {
        if (this.active) {
            this.$('input[data-barcode="' + barcode + '"]').click();
        }
    },

    onToolCreated: function(evt, data) {
        this._setTool(data.ssnId, 'S');
        $('#barcode')[0].focus();
    },

    onProductCreated: function(evt, data){
        this._setConsumable(data.prodId, 'S');
        $('#barcode')[0].focus();
    },

    onCostCenterCreated: function(evt){
        this._setCostCenter(evt.costCentre.CODE, 'S');
        $('#barcode')[0].focus();
    },

    onSaveButtonClick: function(evt) {
        if (this.active) {
            this.model.saveState();
        }
    },

    onCancelButtonClick: function(evt) {
        if (this.active) {
            this.model.resetState();
        }
    },

    _setIssueQty: function(issueQty, via) {
        if (this.active) {
            this.messageBus.notifyLogRequest('Issue Qty set: ' + issueQty + '. Tab: ' + this.model.getProcessId() + '. Via: ' + via);
            this.model.setIssueQty(issueQty, via);
        }
    },

    onUiIssueQtyEnterKeyPressed: function(evt) {
        this._setIssueQty($(evt.target).val().replace(/ /g,""), 'K');
    },

    onIssueQtyBarcode: function(issueQty) {
        this._setIssueQty(issueQty, 'B');
    },

    onToolAltBarcode: function(toolAltBarcode) {
        var item;

        if (this.active) {
            item = this.model.get('item');

            if (item.legacyId && item.id == toolAltBarcode) {
                this.messageBus.notifyQueryLegacyToolCodeRequest(toolAltBarcode);
            } else {
                this.messageBus.notifyLogRequest('Tool set: ' + toolAltBarcode + '. Tab: ' + this.model.getProcessId() + '. Via: B');
                this.model.setToolAltBarcode(toolAltBarcode, 'B');
            }

        }
    },

    _setConsumable: function(consumableId, via) {
        if (this.active) {
            this.messageBus.notifyLogRequest('Consumable set: ' + consumableId + '. Tab: ' + this.model.getProcessId() + '. Via: ' + via);
            this.model.setConsumable(consumableId, via);
        }
    },

    onConsumableBarcode: function(consumableId) {
        this._setConsumable(consumableId, 'B');
    },

    _setTool: function(toolId, via) {
        if (this.active) {
            if (this.model.get('item').id == toolId) {
                this.messageBus.notifyQueryToolRequest(toolId);
            } else {
                this.messageBus.notifyLogRequest('Tool set: ' + toolId + '. Tab: ' + this.model.getProcessId() + '. Via: ' + via);
                this.model.setTool(toolId, via);
            }
        }
    },

    onUiItemEnterKeyPressed: function(evt) {
        var itemId = $(evt.target).val().replace(/ /g, "");

        if (itemId.length == 8) {
            this._setTool(itemId, 'K');
        } else {
            this._setConsumable(itemId, 'K');
        }
    },

    onToolBarcode: function(toolId) {
        this._setTool(toolId, 'B');
    },

    _setLocation: function(locationId, via) {
        if (this.active) {
            if (this.model.get('issueTo').location == locationId) {
                this.messageBus.notifyQueryLocationRequest(locationId);
            } else {
                this.messageBus.notifyLogRequest('Location set: ' + locationId + '. Tab: ' + this.model.getProcessId() + '. Via: ' + via);
                this.model.setIssueToLocation(locationId, via);
            }
        }
    },

    onLocationBarcode: function(locationId) {
        this._setLocation(locationId, 'B');
    },

    _setBorrower: function(borrowerId, via) {
        if (this.active) {
            if (this.model.get('issueTo').location == ('XB' + borrowerId)) {
                this.messageBus.notifyQueryBorrowerRequest(borrowerId);
            } else {
                this.messageBus.notifyLogRequest('Borrower set: ' + borrowerId + '. Tab: ' + this.model.getProcessId() + '. Via: ' + via);
                this.model.setIssueToBorrower(borrowerId, via);
            }
        }
    },

    onUiIssueToEnterKeyPressed: function(evt) {
        var issueTo = $(evt.target).val().replace(/ /g, "");

        if (issueTo.slice(0,2) == 'XB') {
            this._setBorrower(issueTo.slice(2), 'K');
        } else {
            this._setLocation(issueTo, 'K');
        }

    },

    onBorrowerBarcode: function(borrowerId) {
        this._setBorrower(borrowerId, 'B');
    },

    _setCostCenter: function(costCenterId, via) {
        if (this.active) {
            if (this.model.get('chargeTo').id == costCenterId) {
                this.messageBus.notifyQueryCostCenterRequest(costCenterId);
            } else {
                this.messageBus.notifyLogRequest('Cost Center set: ' + costCenterId + '. Tab: ' + this.model.getProcessId() + '. Via: ' + via);
                this.model.setCostCenter(costCenterId, via);
            }
        }
    },

    onUiChargeToEnterKeyPressed: function(evt) {
        this._setCostCenter($(evt.target).val().replace(/ /g,""), 'K');
    },

    onCostCenterBarcode: function(costCenterId) {
        this._setCostCenter(costCenterId, 'B');
    },


    onSwitchToReturnsRequest: function() {
        this.deactivate();
    },

    onSwitchToAuditRequest: function() {
        this.deactivate();
    },

    onSwitchToIssuesRequest: function() {
        if (this.active) {
            this.model.saveState();
        } else {
            this.model.resetState();
            this.active = true;
        }

        this.show();
    },

    show: function() {
        $('.restore-warnings.issues').removeClass('hidden');
        this.$el.css('display','block');
    },

    deactivate: function() {
        if (this.active) {
            this.messageBus.notifyCloseOpenedDialogsRequest();
            this.model.saveState();
            this.active = false;
        }

        $('.restore-warnings.issues').addClass('hidden');
        this.$el.css('display','none');
    },

    onChargeToChanged: function(state, newChargeTo) {
        this.$('#field1_issues').val(newChargeTo.id);
        this.$('#field1_tip_issues').val(newChargeTo.description);
    },

    onIssueToChanged: function(state, newIssueTo) {
        var $field5TipIssues = this.$('#field5_tip_issues');

        this.$('#field2_issues').val(newIssueTo.displayedId);
        this.$('#field2_tip_issues').val(newIssueTo.description);
        this.$('#field5_issues').val(newIssueTo.loanedTotal);
        $field5TipIssues.val(newIssueTo.loanedTotalDescription);

        if (newIssueTo.overdue) {
            $field5TipIssues.addClass('overdue');
        } else {
            $field5TipIssues.removeClass('overdue');
        }
    },

    onExpirationConfirmed: function() {
        var currentExpiration;

        if (this.toConfirm.length > 0) {
            currentExpiration = this.toConfirm.shift();
            openExpirationDialog(currentExpiration.type, currentExpiration.date);

            return;
        }

        this.$('#field3_issues').css('background-color', '');
        this.model.confirmExpiration();
    },

    confirmExpiration: function(item) {
        var $item = this.$('#field3_issues');

        this.toConfirm = [];

        if (item.safetyTestPeriod.expired) {
            if (item.safetyTestPeriod.refuseIfExpired) {
                $item.css('background-color', 'red');
                return;
            }

            this.toConfirm.push({'type': 'Safety test date', 'date': item.safetyTestPeriod.expireDate});
        }

        if (item.calibratePeriod.expired) {
            if (item.calibratePeriod.refuseIfExpired) {
                $item.css('background-color', 'red');
                return;
            }

            this.toConfirm.push({'type': 'Calibration test date', 'date': item.calibratePeriod.expireDate});
        }

        if (item.inspectionPeriod.expired) {
            if (item.inspectionPeriod.refuseIfExpired) {
                $item.css('background-color', 'red');
                return;
            }

            this.toConfirm.push({'type': 'Inspection date', 'date': item.inspectionPeriod.expireDate});
        }

        var currentExpiration;
        if (this.toConfirm.length > 0) {
            $item.css('background-color', 'red');

            currentExpiration = this.toConfirm.shift();
            openExpirationDialog(currentExpiration.type, currentExpiration.date);
        }
    },

    onTransferConfirmed: function() {
        this.model.confirmTransfer();
    },

    confirmItemTransfer: function(item) {
        openToolTransferDialog(item.loanedTo, item.onLoanAt);
    },

    _doFill: function($container, checkPeriod) {
        if (checkPeriod.applicable) {
            $container.removeClass('hidden');
            $container.find('.expired-date').text(checkPeriod.expireDate);

            if (checkPeriod.expired) {
                $container.addClass('expired');
            } else {
                $container.removeClass('expired');
            }
        } else {
            $container.addClass('hidden');
            $container.find('.expired-date').text('');
        }
    },

    fillExpiration: function(item) {
        this._doFill(this.$('.safety-indicator'), item.safetyTestPeriod);
        this._doFill(this.$('.calibration-indicator'), item.calibratePeriod);
        this._doFill(this.$('.inspection-indicator'), item.inspectionPeriod);

    },

    fillImages: function(item) {
        var $container = this.$('#issues_image_container');

        $container.empty();

        if (item.images) {
            $container.append('<img src="' + item.images.image1 + '" alt=" " />');
            $container.append('<img src="' + item.images.image2 + '" alt=" " />');
            $container.append('<img src="' + item.images.image3 + '" alt=" " />');
        }
    },

    onItemChanged: function(state, item) {
        this.$('#field3_issues').val(item.id);
        this.$('#field3_tip_issues').val(item.description);

        this.fillExpiration(item);
        this.fillImages(item);
        this.$('#field3_issues').css('background-color', '');

        if (!item.expirationConfirmed) {
            this.confirmExpiration(item);
        } else if (item.onLoan && !item.transferConfirmed) {
            this.confirmItemTransfer(item);
        }
    },

    onIssueQtyChanged: function(state, newIssueQty) {
        this.$('#field4_issues').val(newIssueQty);
    },

    onIssueQtyDescriptionChanged: function(state, newIssueQtyDescription) {
        this.$('#field4_tip_issues').val(newIssueQtyDescription);
    },

    onCommittedChanged: function(state, newCommittedValue) {
        if (newCommittedValue) {
            this.$('#field3_issues').css('background-color', 'greenyellow').attr('data-item-saved', 'data-item-saved');
            this.$('#field3_tip_issues').css('background-color', 'greenyellow').attr('data-item-saved', 'data-item-saved');
        } else {
            this.$('#field3_issues').css('background-color', '').removeAttr('data-item-saved');
            this.$('#field3_tip_issues').css('background-color', '').removeAttr('data-item-saved');
        }
    }
}, ToolTransactionAwareMixin));
