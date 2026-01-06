var LinesView = Backbone.View.extend(_.extend({

    messageBus: null,
    reportUrl: '',
    loadUrl: '',
    printSsccLabelsUrl: '',

    selection: {
        url: '',
        namespace: '',
        action: '',
        controller: ''
    },

    initialize: function(options) {
        this.messageBus = options.messageBus;
        this.reportUrl = options.reportUrl;
        this.loadUrl = options.loadUrl;
        this.printSsccLabelsUrl = options.printSsccLabelsUrl;
        this.selection = _.extend({}, this.selection, options.selection);

        this.bindUiEvents();
        this.bindMessageBusEvents();
        this.initUi();
    },

    initUi: function() {
        this.$('#lines_tabs > ul').tabs();
        this.$('.order_lines').tablesorter({headers:{0:{sorter:false}}, widgets: ['zebra']});

        this.$('.row_selector, .select_all_rows').minderRowSelector(
            this.selection.url,
            {
                'scopeSelector'       : '#lines_tabs',
                'selectionNamespace'  : this.selection.namespace,
                'selectionAction'     : this.selection.action,
                'selectionController' : this.selection.controller
            },
            {
                'beforeLoad' : $.proxy(this.beforeLoad, this),
                'onError'    : $.proxy(this.onLoadError, this),
                'onSuccess'  : $.proxy(this.onLoadSuccess, this)
            }
        );

        this.updateSelectionState();
    },

    bindMessageBusEvents: function() {
        this.messageBus.onLoadLinesRequest(this.onLoadLinesRequest, this);
        this.messageBus.onOrdersBeforeSearch(this.onOrdersBeforeSearch, this);
        this.messageBus.onCancelDespatch(this.onCancelDespatch, this);
        this.messageBus.onCheckingStatusChanged(this.onCheckingStatusChanged, this);
        this.messageBus.onCheckingStrategyEdiOne(this.onCheckingStrategyEdiOne, this);
        this.messageBus.onOrdersSelectionChanged(this.onOrdersSelectionChanged, this);
    },

    bindUiEvents: function() {
        this.$el.delegate('[name="show_by"], [name="pageselector"]', 'change', $.proxy(this.onPaginationChange, this));
        this.$el.delegate('[name="report_format"]', 'click', $.proxy(this.onPrintLines, this));
        this.$el.delegate('#cancel_despatch', 'click', $.proxy(this.onCancelDespatchClick, this));
        this.$el.delegate('#print_sscc', 'click', $.proxy(this.printSsccLabels, this));
        this.$el.delegate('#change_carrier', 'click', $.proxy(this.messageBus.notifyChangeCarrier, this.messageBus));
        this.$el.delegate('#connote_btn', 'click', $.proxy(this.onConnoteBtnClick, this));
        this.$el.delegate('#check_all_prod_id', 'click', $.proxy(this.onCheckAllBtnClick, this));
    },

    onOrdersSelectionChanged: function(orders) {
        if (orders.getSelectedAmount() < 1) {
            this.$el.html('');
        }
    },

    onCheckingStrategyEdiOne: function() {
        this.$el.html('');
    },

    onCheckAllBtnClick: function() {
        this.messageBus.notifyScreenButtonCheckAll();
    },

    onConnoteBtnClick: function() {
        this.messageBus.notifyConnoteButtonClick();
        //this.messageBus.notifyShowConnote(false);
    },

    onPaginationChange: function() {
        this.loadLines();
    },

    onLoadLinesRequest: function(orders) {
        this.$el.html('');
        this.loadLines();
    },

    loadLines: function() {
        var data = {},
            $showBy = this.$('[name="show_by"]'),
            $pageSelector = this.$('[name="pageselector"]');

        this.messageBus.notifyLinesBeforeLoad();

        if ($showBy.length > 0) {
            data.show_by = $showBy.val();
        }

        if ($pageSelector.length > 0) {
            data.pageselector = $pageSelector.val();
        }

        this.$el.html('');
        this._forgetEmbedMessages();
        this.$el.load(
            this.loadUrl,
            data,
            $.proxy(this.loadLinesCallback, this)
        );
    },

    loadLinesCallback: function() {
        var status = this._getEmbedMessages();

        status.readyForDespatchAmount = parseInt(this.$('.selected_ready_for_desp_count').text()) || 0;
        status.totalSelectedItems = parseInt(this.$('.selected_count').text()) || 0;
        status.hasPrintedSscc = this.hasPrintedSscc();
        status.checkLineDetails = checkLineDetails; //todo: remove global variable
        status.packSscc = packSscc; //todo: remove global variable
        status.serialNumbers = serialNumbers; //todo: remove global variable

        this.showEmbedMessages();
        this.initUi();

        status.errors = status.errors || [];

        if (status.readyForDespatchAmount < 1) {
            status.errors.push(['No lines selected for despatch.']);
            showErrors(['No lines selected for despatch.']);
        }

        if (status.hasPrintedSscc) {
            this.$('#change_carrier').attr('disabled', 'disabled');
        } else {
            this.$('#change_carrier').removeAttr('disabled');
        }

        this.messageBus.notifyLinesDespatchStatus(status);
        this.messageBus.notifyEdiOneOrderStatisticsChanged(new OrderStatistics(this._getEdiOrderStatistics()));
        //this.messageBus.notifyShowConnote(true);
    },

    _getEdiOrderStatistics: function() {
        return JSON.parse(this.$('.ediOrderStatistics').text() || "{}");
    },

    onCheckingStatusChanged: function(status) {
        var $orderLines = this.$('.order_lines');

        if (!status.shouldCheckEachItem) {
            return;
        }

        this.$('.unchecked_items').text(status.uncheckedAmount);

        $.each(status.checkLineDetails, function(key, lineDetail) {
            if (lineDetail.SELECTED && parseInt(lineDetail.CHECKED_QTY) < parseInt(lineDetail.PICKED_QTY)) {
                $orderLines.find('tr.ROW-ID-'+key).addClass('unchecked');
            } else {
                $orderLines.find('tr.ROW-ID-'+key).removeClass('unchecked');
            }
        });
    },

    reset: function() {
    },

    beforeLoad: function() {
        return true;
    },

    onLoadError: function(response) {
        if (response.errors && response.errors.length > 0) {
            showErrors(response.errors);
        }

        this.messageBus.notifyLinesSelectionChanged(this);
        this.messageBus.notifyLinesDespatchStatus({errors: response.errors || []});
        //this.messageBus.notifyShowConnote();
    },

    onLoadSuccess: function(response) {
        var warnings = response.warnings || [];
        if (warnings.length > 0) {
            showErrors(warnings);
        }

        if (response.readyForDespSelected) {
            this.$('.selected_ready_for_desp_count').html(response.readyForDespSelected);
        } else {
            this.$('.selected_ready_for_desp_count').html('0');
            warnings.push('No lines selected for despatch.');
            showErrors(['No lines selected for despatch.']);
        }

        this.messageBus.notifyLinesSelectionChanged(this);
        this.messageBus.notifyLinesDespatchStatus({warnings: warnings});
        //this.messageBus.notifyShowConnote(true);
        //
        //updateCheckLineSelection(response.selectedRows);
    },

    onPrintLines: function(evt) {
        var linesForm = $('#lines_list');
        this.$('.REPORT_FORMAT').remove();
        linesForm.prepend('<input type="hidden" class="REPORT_FORMAT" name="report_format" value="' + $(evt.target).val() + '" />');
        var oldAction = linesForm.attr('action');
        linesForm.attr('action', this.reportUrl).submit();
        linesForm.attr('action', oldAction);
    },

    printSsccLabels: function() {
        $.post(this.printSsccLabelsUrl, {}, $.proxy(this.printSsccLabelsCallback, this), 'json');
    },

    printSsccLabelsCallback: function(response) {
        if (response.success) {
            this.$('#change_carrier').attr('disabled', 'disabled');

            this.messageBus.notifySsccLabelsPrinted();
            //
            //if (Minder.Despatches.AwaitingChecking.SsccPackDetails.isAddingDimensions()) {
            //    showPrompt(5);
            //} else if (isAllLinesChecked()) {
            //    showPrompt(6);
            //} else if (Minder.Despatches.AwaitingChecking.SsccPackDetails.isChecking()) {
            //    showPrompt(4);
            //} else {
            //    var uncheckedSscc = getNextCheckAbleSscc();
            //    showPrompt(3, [{'placeholder': '%nextSscc%', 'value': uncheckedSscc.PS_OUT_SSCC}]);
            //}
        }

        showResponseMessages(response);
        $("#barcode").focus();
    },

    onCancelDespatchClick: function() {
        this.messageBus.notifyCancelDespatch();
    },

    onCancelDespatch: function() {
        this.$el.html('');
    },

    onOrdersBeforeSearch: function() {
        this.$el.html('');
    },

    hasPrintedSscc: function() {
        return this.$('[name="hasPrintedSscc"]').val() === 'true';
    }
}, EmbedMessagesAwareMixin, SelectionMixin));
