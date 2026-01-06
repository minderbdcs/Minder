/**
 * @class Minder.Dlg.RePackSscc
 */
minderNamespace('Minder.Dlg.RePackSscc', function () {
    const REPACKED_EVENT = 'sscc-repacked';

    var
        _$container,
        _ssccMap = {},
        _checkDetails = {},
        waitingSscc = true,
        waitingProduct = false,
        waitingAccept = false,
        visible = false,
        ssccToRepack = [],
        _prodId,
        _serviceUrl;

    this.init = function($container, serviceUrl, prompts, uoms) {
        var
            self = this, buttonPane;
        _$container = $container;
        prompts = prompts || [];
        _$container.find('.repack-prompt').minderScreenPrompts(prompts);

        _serviceUrl = serviceUrl;

        _$container.dialog(
            {
                buttons: {
                    ACCEPT: function (evt) {
                        evt.preventDefault();
                        self.accept();
                    },
                    'RESET': function (evt) {
                        evt.preventDefault();
                        self.reset();
                    },
                    CANCEL: function (evt) {
                        evt.preventDefault();
                        self.close();
                    }
                },
                buttonStyle: 'green-button',
                width: 800,
                height: 400,
                insertHtml: '&nbsp;',
                autoOpen: false,
                resizable: false,
                modal: false
            }
        ).unbind('dialogopen').bind('dialogopen', function (event, ui) {
            $(this).show();
            $('#barcode').focus();
            visible = true;
            $('.order-check-prompt').hide();
        }).unbind('dialogclose').bind('dialogclose', function (event, ui) {
            $('#barcode').focus();
            visible = false;
            $('.order-check-prompt').show();
        });

        buttonPane = _$container.parents('.ui-dialog').find('.ui-dialog-buttonpane');
        buttonPane.find('::nth-child(2)').css('height', 'auto'); //workaround to remove unnecessary height property
        _$container.undelegate('[name="qty"]', 'change').delegate('[name="qty"]', 'change', $.proxy(this.changed, this));
        _$container.undelegate('[name="wt"]', 'change').delegate('[name="wt"]', 'change', $.proxy(this.changed, this));

        var $oldDimsContainer = this.getOldDimsContainer();
        $oldDimsContainer.minderPackDimensions(uoms);
        $oldDimsContainer.minderPackDimensions('disableDimensionInputs');
        var $newDimsContainer = this.getNewDimsContainer();
        $newDimsContainer.minderPackDimensions(uoms);
        $newDimsContainer.minderPackDimensions('addRowChangedListener', 'repack-dlg', $.proxy(this.changed, this));

    };

    this.getOldDimsContainer = function() {
        return _$container.find('.repack-old-dims');
    };

    this.getNewDimsContainer = function() {
        return _$container.find('.repack-new-dims');
    };

    this.setPrompt = function(code) {
        _$container.find('.repack-prompt').minderScreenPrompts('showPrompt', code);
    };

    this.changed = function(evt) {
        evt.preventDefault();
        var enteredTotals = this.getEnteredTotals(this.getEnteredRows());
        this.getDialogContainer().find('.product-total').text(enteredTotals.products);
    };

    this.setSsccMap = function(ssccMap) {
        _ssccMap = ssccMap;
    };

    this.getSsccMap = function() {
        return _ssccMap;
    };

    this.setCheckDetails = function(checkDetails) {
        _checkDetails = checkDetails;
    };

    this.canBeRepacked = function(ssccList) {
        return ssccList.filter(function(sscc) {
            return ['DC', 'CL'].indexOf(sscc.PS_SSCC_STATUS) < 0;
        }).length < 1;
    };

    this.getTotalProducts = function(ssccLabels, prodId) {
        var pickLabels = this.findCheckDetailsByProduct(prodId).map(function(checkDetail){return checkDetail.PICK_LABEL_NO;}),
            result = 0;

        this.findSscc(ssccLabels).forEach(function(sscc){
            if (pickLabels.indexOf(sscc.PS_PICK_LABEL_NO) > -1) {
                result += parseInt(sscc.CHECKED_QTY) || 0;
            }
        });

        return result;
    };

    this.getCheckDetails = function() {
        return _checkDetails;
    };

    this.findCheckDetailsByProduct = function(prodId) {
        var result = [];
        $.each(this.getCheckDetails(), function(index, checkDetail){
            if (checkDetail.PROD_ID == prodId || checkDetail.ALTERNATE_ID == prodId) {
                result.push(checkDetail);
            }
        });

        return result;
    };

    this.findSscc = function(outSsccList) {
        var result = [];
        $.each(this.getSsccMap(), function(ssccLabel, sscc) {
            if (outSsccList.indexOf(sscc.PS_OUT_SSCC) > -1) {
                result.push(sscc);
            }
        });

        return result;
    };

    this.getDialogContainer = function() {
        return _$container;
    };

    this.reset = function() {
        var $dialog = this.getDialogContainer();

        waitingSscc = true;
        waitingProduct = false;
        waitingAccept = false;
        ssccToRepack = [];
        prodId = null;
        $dialog.find('.pack_list').empty();
        $dialog.find('.total-sscc').text(0);
        $dialog.find('.total-sscc-weight').text(0);
        $dialog.find('.product-total').text(0);
        $dialog.find('.prod-id').text('');
        $dialog.find('.product-overall').text(0);
        this.getOldDimsContainer().minderPackDimensions('removeAll');
        this.getNewDimsContainer().minderPackDimensions('removeAll');

        this.setPrompt(1);
        $('#barcode').focus();
    };

    this.isVisible = function() {
        return visible;
    };

    this.addSsccLabel = function(ssccLabel) {
        var ssccList;

        if (!waitingSscc) {
            return;
        }

        if (ssccToRepack.indexOf(ssccLabel) > -1) {
            showErrors(['Already in list.']);
            return;
        }

        ssccList = this.findSscc([ssccLabel]);
        if (ssccList.length < 1) {
            showErrors(['Not found.']);
            return;
        }

        if (!this.canBeRepacked(ssccList)) {
            showErrors(['Cannot be RePacked.']);
            return;
        }

        ssccToRepack.push(ssccLabel);
        this.getDialogContainer().find('.total-sscc').text(ssccToRepack.length);
        this.getDialogContainer().find('.total-sscc-weight').text(this.getSsccWeight(ssccToRepack));
    };

    this.getSsccWeight = function(outSsccList) {
        var result = 0, self = this;

        outSsccList.forEach(function(outSscc){
            var sscc = self.findSscc([outSscc]).shift();
            result += sscc ? (parseFloat(sscc.PS_SSCC_WEIGHT) || 0) : 0;
        });

        return result;
    };

    this.getSsccUoms = function(outSsccList) {
        var sscc = this.findSscc(outSsccList).shift();

        return sscc ? {
            'DT': sscc.PS_SSCC_DIM_UOM,
            'WT': sscc.PS_SSCC_WEIGHT_UOM
        } : {};
    };

    this.fillPackInfo = function(ssccLabels, prodId) {
        var self = this,
            $oldDimsContainer = this.getOldDimsContainer(),
            $newDimsContainer = this.getNewDimsContainer(),
            ssccUoms = this.getSsccUoms(ssccLabels);

        $oldDimsContainer.minderPackDimensions('setUoms', ssccUoms);
        $newDimsContainer.minderPackDimensions('setUoms', ssccUoms);

        ssccLabels.forEach(function(ssccLabel){
            var ssccList = self.findSscc([ssccLabel]);

            $oldDimsContainer.minderPackDimensions('addRow', true, {
                'SSCC': ssccLabel,
                'TYPE': ssccList.length > 0 ? ssccList[0].PS_PACK_TYPE : '',
                'PROD_ID': prodId,
                'PROD_QTY': self.getTotalProducts([ssccLabel], prodId),
                'WT': self.getSsccWeight([ssccLabel])
            });
            $newDimsContainer.minderPackDimensions('addRow', true, {
                'SSCC': ssccLabel,
                'TYPE': ssccList.length > 0 ? ssccList[0].PS_PACK_TYPE : '',
                'PROD_ID': prodId
            });
        });
        $oldDimsContainer.minderPackDimensions('commit', true);
    };

    this.getPackData = function() {
        var $packList = this.getDialogContainer().find('.pack_list');

    };

    this.setProduct = function(prodId) {
        if (!waitingProduct) {
            return;
        }

        var totalProducts = this.getTotalProducts(ssccToRepack, prodId), enteredTotals;

        if (totalProducts < 1) {
            showErrors(['Product not found.']);
            return;
        }

        this.fillPackInfo(ssccToRepack, prodId);

        this.getDialogContainer().find('.prod-id').text(prodId);
        this.getDialogContainer().find('.product-overall').text(totalProducts);
        _prodId = prodId;

        waitingAccept = true;
        waitingProduct = false;

        enteredTotals = this.getEnteredTotals(this.getEnteredRows());
        this.getDialogContainer().find('.product-total').text(enteredTotals.products);
        this.setPrompt(3);
    };

    this.acceptSscc = function() {
        if (ssccToRepack.length < 2) {
            showErrors(['Nothing to repack. Scan at least two SSCC labels.']);
            return;
        }

        waitingSscc = false;
        waitingProduct = true;
        this.setPrompt(2);
    };

    this.validateEnteredData = function(rowsData) {
        var result = true,
            enteredTotals = this.getEnteredTotals(rowsData),
            ssccProducts = this.getTotalProducts(ssccToRepack, _prodId),
            emptyQty = [],
            emptyWeight = [];

        if (enteredTotals.products < ssccProducts) {
            showErrors(['Total entered products is less then SSCC products']);
            result = false;
        } else if (enteredTotals.products > ssccProducts) {
            showErrors(['Total entered products is greater then SSCC products']);
            result = false;
        }

        rowsData.forEach(function(rowData){
            if (!rowData.qty) {
                emptyQty.push(rowData.sscc);
            }
            if (!rowData.weight) {
                emptyWeight.push(rowData.sscc);
            }
        });

        if (emptyQty.length > 0) {
            showErrors(['SSCC: ' + emptyQty.join(', ') + ' have empty Qty.']);
            result = false;
        }

        if (emptyWeight.length > 0) {
            showErrors(['SSCC: ' + emptyWeight.join(', ') + ' have empty Weight.']);
            result = false;
        }

        return result;
    };

    this.doAccept = function() {
        var enteredRows = this.getEnteredRows(), self = this;

        if (!this.validateEnteredData(enteredRows)) {
            return;
        }

        $.post(
            _serviceUrl,
            {'rows': enteredRows, 'prodId': _prodId},
            function(response){
                var event = $.Event(REPACKED_EVENT);

                showResponseMessages(response);

                if (response.errors && response.errors.length > 0) {
                    return;
                }

                event.ssccMap = response.ssccMap;
                event.checkDetails = response.checkDetails;
                _$container.trigger(event);

                self.reset();
                self.setSsccMap(event.ssccMap);
                self.setCheckDetails(event.checkDetails);
            },
            'json'
        );
    };

    this.onRepack = function(namespace, callback) {
        var fullName = REPACKED_EVENT + '.' + namespace;
        _$container.unbind(fullName).bind(fullName, callback);
    };

    this.accept = function() {
        if (!this.isVisible()) {
            return;
        }

        if (waitingSscc) {
            this.acceptSscc();
        } else if (waitingAccept) {
            this.doAccept();
        }

        $('#barcode').focus();
    };

    this.close = function() {
        _$container.dialog('close');
    };

    this.getEnteredRows = function() {
        var result = [];
        this.getNewDimsContainer().minderPackDimensions('getAllRows').forEach(function(rowData){
            result.push({
                "sscc": rowData.SSCC,
                "qty": rowData.RAW.PROD_QTY,
                "weight": rowData.RAW.WT
            });
        });

        return result;
    };

    this.getEnteredTotals = function(enteredRows) {
        var result = {
            products: 0,
            weight: 0
        };
        enteredRows.forEach(function(row) {
            result.products     += parseInt(row.qty) ||0;
            result.weight       += parseFloat(row.weight) || 0;
        });

        return result;
    };

    this.show = function(ssccMap, checkDetails) {
        if (!visible) {
            this.reset();
        }
        this.setSsccMap(ssccMap);
        this.setCheckDetails(checkDetails);

        this.getDialogContainer().dialog('open');
    };
});
