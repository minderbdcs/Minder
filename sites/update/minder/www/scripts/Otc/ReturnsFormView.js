var ReturnsFormView = Backbone.View.extend(_.extend({
    active: false,

    initialize: function(options) {
        var modelOptions = _.extend({}, _.clone(options), {parse: true});

        this.model = new ProcessState(options.returnsState || {processId: 'RETURNS'}, modelOptions);
        this.messageBus = options.messageBus || new OtcMessageBus();
        this.formatMap = options.locationFormatMap || {};
        this.currentWhId = options.currentWhId || '';
        this.renderedRowAmount = 0;

        this.bindModelEvents();
        this.bindUiEvents();
        this.bindMessageBusEvents();

        this.render();
    },

    render: function() {
        this.onReturnFromChanged(this.model, this.model.get('returnFrom'));
        this.onReturnToChanged(this.model, this.model.get('returnTo'));
        this.onIssueQtyChanged(this.model, this.model.get('issueQty'));
        this.onIssueQtyDescriptionChanged(this.model, this.model.get('issueQtyDescription'));
        this.onCommittedChanged(this.model, this.model.get('committed'));
        this.onItemChanged(this.model, this.model.get('item'));
	this.onCanChangeToolReturnLocationChanged(this.model, this.model.get('canChangeToolReturnLocation'));
    },

    bindModelEvents: function() {
        this.listenTo(this.model, 'change:returnFrom', this.onReturnFromChanged);
        this.listenTo(this.model, 'change:returnTo', this.onReturnToChanged);
        this.listenTo(this.model, 'change:issueQty', this.onIssueQtyChanged);
        this.listenTo(this.model, 'change:issueQtyDescription', this.onIssueQtyDescriptionChanged);
        this.listenTo(this.model, 'change:committed', this.onCommittedChanged);
        this.listenTo(this.model, 'change:item', this.onItemChanged);
	this.listenTo(this.model, 'change:canChangeToolReturnLocation', this.onCanChangeToolReturnLocationChanged);
        this.listenTo(this.model, 'change:toolTransaction', this.onToolTransactionChanged);
        this.listenTo(this.model.history, 'add', this.renderHistoryRow);
    },

    bindUiEvents: function() {
        this.$('#field1_returns').bind('enter-key-pressed', $.proxy(this.onUiReturnToEnterKeyPressed, this));
        this.$('#field2_returns').bind('enter-key-pressed', $.proxy(this.onUiReturnFromEnterKeyPressed, this));
        this.$('#field3_returns').bind('enter-key-pressed', $.proxy(this.onUiToolEnterKeyPressed, this));
        this.$('#field4_returns').bind('enter-key-pressed', $.proxy(this.onUiIssueQtyEnterKeyPressed, this));
        this.$('[data-barcode="HOME"]').click($.proxy(this.onRecordHomeClick, this));
        this.$('.list-issues-returns').click($.proxy(this.onListIssuesReturnsClick, this));
        this.$('.list-returns').click($.proxy(this.onListReturnsClick, this));

        this.$('[value="SAVE"]').click($.proxy(this.onSaveButtonClick, this));
        this.$('[value="CANCEL"]').click($.proxy(this.onCancelButtonClick, this));
        this.$('[value="Capture Image"]').click($.proxy(this.onCaptureImageButtonClick, this));

        Otc.Dlg.Query.addRowClickListener($.proxy(this.onQueryDialogRowClick, this), 'returns-view');
    },

    bindMessageBusEvents: function() {
        this.messageBus.onSwitchToIssuesRequest(this.onSwitchToIssuesRequest, this);
        this.messageBus.onSwitchToReturnsRequest(this.onSwitchToReturnsRequest, this);
        this.messageBus.onSwitchToAuditRequest(this.onSwitchToAuditRequest, this);

        this.messageBus.onLocationBarcode(this.onLocationBarcode, this);
        this.messageBus.onBorrowerBarcode(this.onBorrowerBarcode, this);
        this.messageBus.onToolBarcode(this.onToolBarcode, this);
        this.messageBus.onToolAltBarcode(this.onToolAltBarcode, this);

        this.messageBus.onIssueQtyBarcode(this.onIssueQtyBarcode, this);
        this.messageBus.onDescriptionBarcode(this.onDescriptionBarcode, this);
        this.messageBus.onReloadToolRequest(this.onReloadToolRequest, this);
    },

    onReloadToolRequest: function() {
        if (this.active) {
            this.model.reloadTool();
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

    _renderRow: function(rowNo, date, prod_code, ssn_id, saveQty, saveDesc, costCenter) {
        this.$('#lines_returns').prepend('<tr class="' + (rowNo % 2 == 0 ? 'even' : 'odd') + '">' +
        '<td><input type="checkbox" name =""></td>' +
        '<td>'+ date +'</td>' +
        '<td>'+ ((prod_code == undefined) ? '' : prod_code) +'</td>' +
        '<td>'+ ((ssn_id == undefined) ? '' : ssn_id) +'</td>' +
        '<td>'+ saveQty +'</td>' +
        '<td>'+ saveDesc +'</td>' +
        '<td>'+ ((costCenter == undefined) ? '' : costCenter) +'</td>' +
        '</tr>');

    },

    renderHistoryRow: function(row) {
        var json = row.toJSON(),
            date = json.save_datetime;

        if (json.save_qty!=undefined && json.save_desc!=undefined ) {

            if (json.loaned_total != undefined) {					// By #413
                this.$('#field5_returns')[0].value = json.loaned_total; 	//
            }														//

            this._renderRow(this.renderedRowAmount++, date, json.save_prod_code, json.save_ssn_id, json.save_qty, json.save_desc, json.save_location);
        }

        if (json.save2_qty!=undefined && json.save2_desc!=undefined ) {

            if (json.loaned_total != undefined) {					// By #413
                this.$('#field5_returns')[0].value = json.loaned_total; 	//
            }														//

            this._renderRow(this.renderedRowAmount++, date, json.save2_prod_code, json.save2_ssn_id, json.save2_qty, json.save2_desc, json.save2_cc);
        }

        this.$('#returns_count').text(this.renderedRowAmount);
    },

    onListReturnsClick: function() {
        if (isReturnsActive()) {
            closeLoans();
        } else {
            this.messageBus.notifyCloseOpenedDialogsRequest();
            listReturns();
        }
    },

    onListIssuesReturnsClick: function() {
        if (isIssuesReturnsActive()) {
            closeLoans();
        } else {
            this.messageBus.notifyCloseOpenedDialogsRequest();
            listIssuesReturns();
        }
    },

    onRecordHomeClick: function(evt) {
        if (this.active) {
            this.model.recordHome();
            $('#barcode').focus();
        }
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
        this._setIssueQty(issueQty, 'B')
    },

    onToolAltBarcode: function(toolAltBarcode) {
        if (this.active) {

            //todo: add alt barcode fast query
            this.messageBus.notifyLogRequest('Tool set: ' + toolAltBarcode + '. Tab: ' + this.model.getProcessId() + '. Via: B');
            this.model.setToolAltBarcode(toolAltBarcode, 'B');
        }
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

    onUiToolEnterKeyPressed: function(evt) {
        this._setTool($(evt.target).val().replace(/ /g,""), 'K');
    },

    onToolBarcode: function(toolId) {
        this._setTool(toolId, 'B');
    },

    _setBorrower: function(borrowerId, via) {
        if (this.active) {
            if (this.model.get('returnFrom').location == ('XB' + borrowerId)) {
                this.messageBus.notifyQueryBorrowerRequest(borrowerId);
            } else {
                this.messageBus.notifyLogRequest('Borrower set: ' + borrowerId + '. Tab: ' + this.model.getProcessId() + '. Via: ' + via);
                this.model.setReturnFrom(borrowerId, via);
            }
        }
    },

    onUiReturnFromEnterKeyPressed: function(evt) {
        this._setBorrower($(evt.target).val().replace(/ /g,""), 'K');
    },

    onBorrowerBarcode: function(borrowerId) {
        this._setBorrower(borrowerId, 'B');
    },

    _setLocation: function(locationId, via) {
        if (this.active) {
            if (this.model.get('returnTo').location == locationId) {
                this.messageBus.notifyQueryLocationRequest(locationId);
            } else {
                this.messageBus.notifyLogRequest('Location set: ' + locationId + '. Tab: ' + this.model.getProcessId() + '. Via: ' + via);
                this.model.setReturnTo(locationId, via);
            }
        }
    },

    onUiReturnToEnterKeyPressed: function(evt) {
        this._setLocation($(evt.target).val().replace(/ /g,""), 'K');
    },

    onLocationBarcode: function(locationId) {
        this._setLocation(locationId, 'B');
    },

    onSwitchToReturnsRequest: function() {
        if (this.active) {
            this.model.saveState();
        } else {
            this.model.resetState();
            this.active = true;
        }

        this.show();
    },

    onSwitchToIssuesRequest: function() {
        this.deactivate();
    },

    onSwitchToAuditRequest: function() {
        this.deactivate();
    },

    show: function() {
        $('.restore-warnings.returns').removeClass('hidden');
        this.$el.css('display','block');
    },

    deactivate: function() {
        if (this.active) {
            this.model.saveState();
            this.active = false;
            this.messageBus.notifyCloseOpenedDialogsRequest();
        }

        $('.restore-warnings.returns').addClass('hidden');
        this.$el.css('display','none');
    },

    onReturnFromChanged: function(state, newReturnFrom) {
        this.$('#field2_returns').val(newReturnFrom.displayedId);
        this.$('#field2_tip_returns').val(newReturnFrom.description);
    },

    onReturnToChanged: function(state, newReturnTo) {
        var locationId = this.formatLocation(newReturnTo.displayedId.substr(0, 2), newReturnTo.displayedId.substr(2));

        this.$('#field1_returns').val(locationId);
        this.$('#field1_tip_returns').val(newReturnTo.description);
    },

    onIssueQtyChanged: function(state, newIssueQty) {
        this.$('#field4_returns').val(newIssueQty);
    },

    onIssueQtyDescriptionChanged: function(state, newDescription) {
        this.$('#field4_tip_returns').val(newDescription);
    },

    onCommittedChanged: function(state, newCommittedValue) {
        if (newCommittedValue) {
            this.$('#field3_returns').css('background-color', 'greenyellow').attr('data-item-saved', 'data-item-saved');
            this.$('#field3_tip_returns').css('background-color', 'greenyellow').attr('data-item-saved', 'data-item-saved');
        } else {
            this.$('#field3_returns').css('background-color', '').removeAttr('data-item-saved');
            this.$('#field3_tip_returns').css('background-color', '').removeAttr('data-item-saved');
        }
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
        var $container = this.$('#returns_image_container');

        $container.empty();

        if (item.images) {
            $container.append('<img src="' + item.images.image1 + '" alt=" " />');
            $container.append('<img src="' + item.images.image2 + '" alt=" " />');
            $container.append('<img src="' + item.images.image3 + '" alt=" " />');
        }
    },

    onItemChanged: function(state, newItem) {
        var itemId = newItem.id;

	/*if (newItem.homeLocation) {
            itemId += ' [' + this.formatLocation(this._getCurrentWhId(), newItem.homeLocation) + ']';
        }*/

        this.$('#field3_returns').val(itemId);
        this.$('#field3_tip_returns').val(newItem.description);

	if (newItem.homeLocation || newItem.homeWhId) {
		var home_locn = newItem.homeWhId +'-'+ newItem.homeLocation;
		var locn_name = 'WH: '+newItem.homeWhId+' - '+ newItem.locnName;
        }

        this.$('#field6_returns').val(home_locn);
        this.$('#field6_tip_returns').val(locn_name);

        this.fillExpiration(newItem);
        this.fillImages(newItem);
    },


    onCanChangeToolReturnLocationChanged: function(state, newValue) {
        if (newValue) {
            this.$('[data-barcode="HOME"]').removeAttr('disabled');
        } else {
            this.$('[data-barcode="HOME"]').attr('disabled', 'disabled');
        }
    },

    _getCurrentWhId: function() {
        return this.currentWhId;
    },

    _getLocationFormat: function(whId) {
        return this.formatMap[whId.toUpperCase()] || this.formatMap['ALL'];
    },

    _doLocationFormat: function(location, format) {
        var result = format,
            replacePosition = result.indexOf('?'),
            sourcePosition = 0;

        if (!location) {
            return location;
        }

        while (replacePosition > -1 && sourcePosition < location.length) {
            result = result.substring(0, replacePosition)
            + location.substring(sourcePosition, sourcePosition + 1)
            + result.substring(replacePosition + 1);

            sourcePosition++;
            replacePosition = result.indexOf('?');
        }

        return result;
    },

    formatLocation: function(whId, locnId) {
        var format 		= this._getLocationFormat(whId),
            location 	= whId + locnId;

        return format ? this._doLocationFormat(location, format) : location;
    }
}, ToolTransactionAwareMixin));
