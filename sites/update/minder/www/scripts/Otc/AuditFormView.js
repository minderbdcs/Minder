var AuditFormView = Backbone.View.extend(_.extend({
    active: false,

    initialize: function(options) {
        var modelOptions = _.extend({}, _.clone(options), {parse: true});

        this.model = new ProcessState(options.auditState || {processId: 'AUDIT'}, modelOptions);
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
        this.onAuditLocationChanged(this.model, this.model.get('auditLocation'));
        this.onItemChanged(this.model, this.model.get('item'));
        this.onExpectedQtyChanged(this.model, this.model.get('expectedQty'));
        this.onCheckedIssnListChanged(this.model, this.model.get('checkedIssnList'));
        this.onCommittedChanged(this.model, this.model.get('committed'));
    },

    bindModelEvents: function() {
        this.listenTo(this.model, 'change:auditLocation', this.onAuditLocationChanged);
        this.listenTo(this.model, 'change:item', this.onItemChanged);
        this.listenTo(this.model, 'change:expectedQty', this.onExpectedQtyChanged);
        this.listenTo(this.model, 'change:checkedIssnList', this.onCheckedIssnListChanged);
        this.listenTo(this.model, 'change:committed', this.onCommittedChanged);
        this.listenTo(this.model, 'change:toolTransaction', this.onToolTransactionChanged);
        this.listenTo(this.model.history, 'add', this.renderHistoryRow);
    },

    bindUiEvents: function() {
        this.$('[name="location"]').bind('enter-key-pressed', $.proxy(this.onUiAuditLocationEnterKeyPressed, this));
        this.$('[name="tool"]').bind('enter-key-pressed', $.proxy(this.onUiToolEnterKeyPressed, this));
        //this.$('[data-barcode="HOME"]').click($.proxy(this.onRecordHomeClick, this));
        this.$(".audit-history-lines").tablesorter({headers: {0: {sorter:false}},sortList:[[1,0]], widgets: ['zebra']});
        this.$('[value="Import Audit"]').click($.proxy(this.onImportAuditClick, this));
        this.$('[value="END AUDIT"]').click($.proxy(this.onEndAuditClick, this));
        this.$('[value="Capture Image"]').click($.proxy(this.onCaptureImageButtonClick, this));

        addImportAuditFileCompletedListener('otc-audit-tab', $.proxy(this.onAuditImportCompleted, this));
    },

    bindMessageBusEvents: function() {
        this.messageBus.onSwitchToIssuesRequest(this.onSwitchToIssuesRequest, this);
        this.messageBus.onSwitchToReturnsRequest(this.onSwitchToReturnsRequest, this);
        this.messageBus.onSwitchToAuditRequest(this.onSwitchToAuditRequest, this);

        this.messageBus.onLocationBarcode(this.onLocationBarcode, this);
        this.messageBus.onToolBarcode(this.onToolBarcode, this);
        this.messageBus.onToolAltBarcode(this.onToolAltBarcode, this);
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

    onAuditImportCompleted: function(evt) {
        var importResult = evt.importResult || {};

        if (importResult.error && importResult.error.length > 0) {
            showErrors([importResult.error]);
        }

        if (importResult.processState) {
            this.model.set(this.model.parse(importResult.processState));
        }
    },

    onImportAuditClick: function() {
        if (isAddImportAuditDialogActive()) {
            closeImportAuditDialog();
        } else {
            this.messageBus.notifyCloseOpenedDialogsRequest();
            openAuditDialog();
        }
    },

    onEndAuditClick: function() {
        if (this.active) {
            this.model.endAudit();
        }
    },

    _renderRow: function(rowNo, date, ssnId, description, location) {
        this.$('.audit-history-lines').find('tbody').prepend('<tr class="' + (rowNo % 2 == 0 ? 'even' : 'odd') + '">' +
        '<td><input type="checkbox" name =""></td>' +
        '<td>' + date + '</td>' +
        '<td>' + (ssnId != undefined ? ssnId : '') + '</td>' +
        '<td>' + description + '</td>' +
        '<td>' + (location != undefined ? location : '') + '</td>' +
        '</tr>');
    },

    renderHistoryRow: function(row) {
        var json = row.toJSON();
        if (json.save_desc!=undefined ) {
            this._renderRow(this.renderedRowAmount++, json.save_datetime, json.save_ssn_id, json.save_desc, json.save_location);
        }
    },

    pressBarcodeButton: function(barcode) {
        if (this.active) {
            this.$('input[data-barcode="' + barcode + '"]').click();
        }
    },

    onToolAltBarcode: function(toolAltBarcode) {
        if (this.active) {
            this.messageBus.notifyLogRequest('Tool set: ' + toolAltBarcode + '. Tab: ' + this.model.getProcessId() + '. Via: B');
            this.model.setToolAltBarcode(toolAltBarcode, 'B');
        }
    },

    _setTool: function(toolId, via) {
        if (this.active) {
            this.messageBus.notifyLogRequest('Tool set: ' + toolId + '. Tab: ' + this.model.getProcessId() + '. Via: ' + via);
            this.model.setTool(toolId, via);
        }
    },

    onUiToolEnterKeyPressed: function(evt) {
        this._setTool($(evt.target).val().replace(/ /g,""), 'K');
    },

    onToolBarcode: function(toolId) {
        this._setTool(toolId, 'B');
    },

    _setLocation: function(locationId, via) {
        if (this.active) {
            this.messageBus.notifyLogRequest('Location set: ' + locationId + '. Tab: ' + this.model.getProcessId() + '. Via: ' + via);
            this.model.setAuditLocation(locationId, via);
        }
    },

    onUiAuditLocationEnterKeyPressed: function(evt) {
        this._setLocation($(evt.target).val().replace(/ /g,""), 'K');
    },

    onLocationBarcode: function(locationId) {
        this._setLocation(locationId, 'B');
    },

    onSwitchToAuditRequest: function() {
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

    onSwitchToReturnsRequest: function() {
        this.deactivate();
    },

    show: function() {
        this.$el.css('display','block');
    },

    deactivate: function() {
        if (this.active) {
            this.model.saveState();
            this.active = false;
            this.messageBus.notifyCloseOpenedDialogsRequest();
        }

        this.$el.css('display','none');
    },

    onAuditLocationChanged: function(state, auditLocation) {
        var $locationId = this.$('[name="location"]'),
            $locationDescription = this.$('[name="location_tip"]');

        $locationId.val(auditLocation.location);
        $locationDescription.val(auditLocation.description);

        if (auditLocation.opened) {
            $locationId.css('background-color', 'greenyellow');
            $locationDescription.css('background-color', 'greenyellow');
        } else {
            $locationId.css('background-color', '');
            $locationDescription.css('background-color', '');
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

        this.$('[name="tool"]').val(itemId);
        this.$('[name="tool_tip"]').val(newItem.description);

        this.fillExpiration(newItem);
        this.fillImages(newItem);
    },

    onCommittedChanged: function(state, newValue) {
        if (newValue) {
            this.$('[name="tool"]').css('background-color', 'greenyellow');
            this.$('[name="tool_tip"]').css('background-color', 'greenyellow');
        } else {
            this.$('[name="tool"]').css('background-color', '');
            this.$('[name="tool_tip"]').css('background-color', '');
        }
    },

    _getLocationFormat: function(whId) {
        return this.formatMap[whId.toUpperCase()] || this.formatMap['ALL'];
    },

    _doLocationFormat: function(location, format) {
        var result = format,
            replacePosition = result.indexOf('?'),
            sourcePosition = 0;

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
    },

    onExpectedQtyChanged: function(state, newValue) {
        this.$('[name="location_total"]').val(newValue);
    },

    onCheckedIssnListChanged: function(state, newValue) {
        this.$('[name="scanned_total"]').val(newValue.length);
    }
}, ToolTransactionAwareMixin));