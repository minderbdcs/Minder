var CheckStrategyEdiAll = Backbone.Model.extend({
    messageBus : null,
    prodIdIndex: null,
    altIdIndex: null,
    productNotFoundDialogIsActive: false,
    started: false,
    linesErrors: [],
    getRetailUnitUrl: '',
    acceptUrl: '',

    searchLog: [],

    index: null,

    initialize: function(attributes, options) {
        this.messageBus = attributes.messageBus;
        this.bindMessageBusEvents();
        this.linesErrors = [];
        this.searchLog = [];
        this.getRetailUnitUrl = attributes.getRetailUnitUrl;
        this.acceptUrl = options.acceptUrl;

        this.status = {
            isEdi: true,
            lastProdId: null,
            lastSscc: null,
            canBeChecked: attributes.canBeChecked,
            shouldCheckEachItem: true,
            allItemsChecked: false,
            totalSelectedItems: 0,
            uncheckedAmount: 0,
            uncheckedSsccItems: 0,
            uncheckedProducts: 0,
            uncheckedProdId: null,
            nextCheckAbleSscc: null,
            checkDetailsLoaded: false,
            checkLineDetails: {},
            hasPrintedSscc: false,
            packSscc: {}
        };

        Minder.Despatches.AwaitingChecking.SsccPackDetails.setShouldGenerateSSCC(true);
        Minder.Despatches.AwaitingChecking.SsccPackDetails.onSsccLabelAccept('edi-all', $.proxy(this.onSsccLabelAccept, this));

        if (attributes.selectedOrdersAmount > 0) {
            this.messageBus.notifyLoadLinesRequest();
        }
        this.listenTo(this, 'destroy', this.onDestroy);

        this.messageBus.notifyCheckingStrategyEdiAll();
    },

    bindMessageBusEvents: function() {
        this.messageBus.onOrdersBeforeSearch(this.onOrdersBeforeSearch, this);
        this.messageBus.onLinesDespatchStatus(this.onLinesDespatchStatus, this);
        this.messageBus.onCheckingStarted(this.onCheckingStarted, this);
        this.messageBus.onCheckingComplete(this.onCheckingComplete, this);
        this.messageBus.onCheckSsccLabelRequest(this.onCheckSsccLabelRequest, this);
        this.messageBus.onCheckProdIdRequest(this.onCheckProdIdRequest, this);
        this.messageBus.onScreenButtonAccept(this.onScreenButtonAccept, this);
        this.messageBus.onSsccDimensionsCanceled(this.onSsccDimensionsCanceled, this);
        this.messageBus.onSsccDimensionsAccepted(this.onSsccDimensionsAccepted, this);
        this.messageBus.onSsccRePacked(this.onSsccRePacked, this);

        this.messageBus.notifySubscribeToProdEanTypeRequest(this, this.onBarcodeTypeProdEan);
    },

    onSsccRePacked: function(checkLineDetails, packSscc) {
        this.status.packSscc = packSscc;
        this.status.checkLineDetails = checkLineDetails;
        this.status.nextCheckAbleSscc = this.getNextCheckAbleSscc();

        this.validatePackSsccData();

        Minder.Despatches.AwaitingChecking.SsccPackDetails.reset();
        Minder.Despatches.AwaitingChecking.SsccPackDetails.fillChecked(this.getAcceptedSsccDimensions(packSscc));

        this.status.uncheckedAmount = this.calculateUncheckedAmount(this.status.checkLineDetails);
        this.status.allItemsChecked = (this.status.totalSelectedItems > 0) && (this.status.uncheckedAmount < 1);
        this.status.started = Minder.Despatches.AwaitingChecking.SsccPackDetails.isChecking();
        this.status.dimensionsStarted = Minder.Despatches.AwaitingChecking.SsccPackDetails.isAddingDimensions();

        this.messageBus.notifyCheckingStarted(this.status);
        this.messageBus.notifyCheckingStatusChanged(this.status);

        if (this.status.allItemsChecked) {
            this.messageBus.notifyCheckingComplete(this.status);
        }
    },

    onSsccLabelAccept: function(evt) {
        this.messageBus.notifySsccDimensionsAccepted(evt.packSscc);
        this.messageBus.notifyEdiOneOrderStatisticsChanged(new OrderStatistics(evt.orderStatistics));
    },

    onSsccDimensionsAccepted: function(packSscc) {
        this.status.packSscc = packSscc;
        this.status.nextCheckAbleSscc = this.getNextCheckAbleSscc();
        this.status.lastSscc = null;

        this.validatePackSsccData();

        this.status.uncheckedAmount = this.calculateUncheckedAmount(this.status.checkLineDetails);
        this.status.allItemsChecked = (this.status.totalSelectedItems > 0) && (this.status.uncheckedAmount < 1);
        this.status.started = false;
        this.status.dimensionsStarted = false;

        this.messageBus.notifyCheckingStatusChanged(this.status);

        if (this.status.allItemsChecked) {
            this.messageBus.notifyCheckingComplete(this.status);
        }
    },

    onSsccDimensionsCanceled: function() {
        this.status.nextCheckAbleSscc = this.getNextCheckAbleSscc();
        this.status.lastSscc = null;
        this.status.started = false;
        this.status.dimensionsStarted = false;

        this.messageBus.notifyCheckingStatusChanged(this.status);
    },

    onScreenButtonAccept: function() {
        if (Minder.Dlg.RePackSscc.isVisible()) {
            this.messageBus.notifyScreenButtonServed();
            Minder.Dlg.RePackSscc.accept();
        }else if (Minder.Despatches.AwaitingChecking.SsccPackDetails.isGoing()) {
            this.messageBus.notifyScreenButtonServed();
            Minder.Despatches.AwaitingChecking.SsccPackDetails.accept(false);
        }
    },

    onOrdersBeforeSearch: function() {
        Minder.Despatches.AwaitingChecking.SsccPackDetails.reset();

        this.status.lastSscc            = null;
        this.status.lastProdId          = null;
        this.status.allItemsChecked     = false;
        this.status.totalSelectedItems  = 0;
        this.status.uncheckedAmount     = 0;
        this.status.uncheckedSsccItems  = 0;
        this.status.uncheckedProducts   = 0;
        this.status.uncheckedProdId     = null;
        this.status.nextCheckAbleSscc   = null;
        this.status.hasPrintedSscc      = false;

    },

    onBarcodeTypeProdEan: function(dataIdentifier) {
        if (Minder.Despatches.AwaitingChecking.SsccPackDetails.isGoing()) {
            if (this.status.lastSscc) {
                this.checkSsccRetailUnit(this.status.lastSscc, dataIdentifier.param_filtered_value);
            } else {
                showErrors(['Scan SSCC label first.']);
            }
        }
    },

    onCheckProdIdRequest: function(prodId) {
        if (Minder.Despatches.AwaitingChecking.SsccPackDetails.isGoing()) {
            if (this.status.lastSscc) {
                this.checkSsccProdId(this.status.lastSscc, prodId);
            } else {
                showErrors(['Scan SSCC label first.']);
            }
        }
    },

    checkSsccRetailUnit: function(ssccList, code) {
        $.get(this.getRetailUnitUrl, {'code': code}, $.proxy(this.checkSsccRetailUnitCallback, this), 'json');
    },

    checkSsccRetailUnitCallback: function(response) {
        var checkDetails, uncheckedProdIdAmount;

        showResponseMessages(response);

        if (response.retailUnit) {
            checkDetails = this.getCheckDetailBySsccList(this.status.lastSscc);
            uncheckedProdIdAmount = this.getTotalUncheckedProdId(checkDetails, response.retailUnit.PROD_ID);

            if (uncheckedProdIdAmount > 0) {
                if (uncheckedProdIdAmount < response.retailUnit.PROD_ISSUE_QTY) {
                    showErrors(['Retail Unit issue qty more then unchecked products left.']);
                } else {
                    this.checkSsccProdId(this.status.lastSscc, response.retailUnit.PROD_ID, response.retailUnit.PROD_ISSUE_QTY);
                }
            } else {
                if (this.hasProdId(checkDetails, response.retailUnit.PROD_ID)) {
                    showErrors(['All items checked.']);
                } else {
                    showErrors(['Product not found:']);
                }
            }
        }
    },

    getTotalUncheckedProdId: function(checkDetails, prodId) {
        var result = 0;
        checkDetails.filter(function(checkDetail){
            return checkDetail.PICKED_QTY > checkDetail.CHECKED_QTY && (!prodId || checkDetail.PROD_ID === prodId)
        }).forEach(function(checkDetail){
            result += checkDetail.PICKED_QTY - checkDetail.CHECKED_QTY;
        });

        return result;
    },

    hasProdId: function(checkDetails, prodId) {
        return checkDetails.filter(function(checkDetail){return checkDetail.PROD_ID === prodId || checkDetail.ALTERNATE_ID === prodId}).length > 0;
    },

    checkSsccProdId: function(ssccList, prodId, qty) {
        var self = this;
        function _doCheck(ssccList, prodId) {
            var checkDetails = self.getCheckDetailBySsccList(ssccList),
                lastUnchecked = self.getLastUncheckedDetail(checkDetails, prodId),
                uncompletedSscc;

            if (!lastUnchecked) {
                lastUnchecked = self.getLastUncheckedDetailAlt(checkDetails, prodId);
            }

            if (lastUnchecked) {
                lastUnchecked.CHECKED_QTY++;
                self.status.uncheckedProdId = prodId;
                uncompletedSscc = self.getUncompletedSscc(ssccList, lastUnchecked);
                uncompletedSscc.CHECKED_QTY = parseInt(uncompletedSscc.CHECKED_QTY) || 0;
                uncompletedSscc.CHECKED_QTY++;
                uncompletedSscc.prodId = prodId;

                if (lastUnchecked.CHECKED_QTY >= lastUnchecked.PICKED_QTY) {
                    uncompletedSscc.completed = true;
                }

                return true;
            } else {
                if (self.hasProdId(checkDetails, prodId)) {

                    showWarnings(['All items checked.']);
                } else {
                    var errors = ['Product not found:'],
                        tmpError;

                    if (ssccList.length < 1) {
                        errors.push('No SSCC label provided.');
                    } else {
                        tmpError = 'Searching Order Lines for SSCC #' + ssccList[0].PS_OUT_SSCC + '. ';
                        if (checkDetails.length < 0) {
                            errors.push(tmpError + 'No Order Lines found.');
                        } else {
                            errors.push(tmpError + checkDetails.length + ' Order Line(s) found.');
                            errors.push('Found Order Lines do not contain Product #' + prodId + '.');
                        }
                    }
                    showErrors(errors);
                }
                return false;
            }
        }

        qty = qty || 1;
        var success = true;

        while (success && (qty > 0)) {
            success = _doCheck(ssccList, prodId);
            qty--;
        }

        this.recalculateStatus(ssccList);
        this.validatePackSsccData();

        //updateSsccCheckStatistics(ssccList);
        //updateCheckLineStatus();
        //validatePackSsccData(isEdiOrder, checkLineDetails, packSscc);
    },

    onCheckSsccLabelRequest: function(ssccLabel) {
        var sscc;
        if (Minder.Despatches.AwaitingChecking.SsccPackDetails.isGoing()) {
            sscc = this.getSsccLabel(ssccLabel);

            if (sscc) {
                if (this.hasUnaccepted(sscc)) {
                    if (Minder.Despatches.AwaitingChecking.SsccPackDetails.isChecking()) {
                        if (Minder.Despatches.AwaitingChecking.SsccPackDetails.startDimensions(sscc, this.getSsccWeight(sscc))) {
                            //showPrompt(5);
                            //updateSsccCheckStatistics(sscc);
                            this.status.lastSscc = sscc;
                            this.recalculateStatus(sscc);
                        }
                    } else {
                        if (Minder.Despatches.AwaitingChecking.SsccPackDetails.startCheck(sscc)) {
                            //showPrompt(4);
                            //updateSsccCheckStatistics(sscc);
                            this.status.lastSscc = sscc;
                            this.recalculateStatus(sscc);
                        }
                    }
                } else {
                    showWarnings(['Already accepted.']);
                }
            } else {
                showErrors(['SSCC Label not found.']);
                showErrors(this.searchLog);
            }
        } else if (isEdiOrder) {

            showErrors(['All items are checked.']);
        }
    },

    getTotalUnchecked: function(checkDetails) {
        var result = 0;
        checkDetails.forEach(function(checkDetail){
            result += checkDetail.PICKED_QTY - checkDetail.CHECKED_QTY;
        });

        return result;
    },

    getLastUncheckedDetail: function(checkDetails, prodId) {
        return checkDetails.filter(function(checkDetail){
            return checkDetail.PICKED_QTY > checkDetail.CHECKED_QTY && (!prodId || checkDetail.PROD_ID === prodId)
        }).shift();
    },

    getLastUncheckedDetailAlt: function(checkDetails, altProdId) {
        return checkDetails.filter(function(checkDetail){
            return checkDetail.PICKED_QTY > checkDetail.CHECKED_QTY && checkDetail.ALTERNATE_ID === altProdId;
        }).shift();
    },

    recalculateStatus: function(sscc) {
        var checkDetails = this.getCheckDetailBySsccList(sscc),
            unchecked,
            totalUnchecked = this.getTotalUnchecked(checkDetails);

        this.status.uncheckedAmount = this.calculateUncheckedAmount(this.status.checkLineDetails);

        if (checkDetails.length > 0) {
            unchecked = this.getLastUncheckedDetail(checkDetails, this.status.lastProdId);

            if (!unchecked) {
                unchecked = this.getLastUncheckedDetailAlt(checkDetails, this.status.lastProdId);
            }

            if (!unchecked) {
                unchecked = this.getLastUncheckedDetail(checkDetails);
            }

            this.status.uncheckedSsccItems = totalUnchecked;

            if (unchecked) {
                this.status.uncheckedProducts = unchecked.PICKED_QTY - unchecked.CHECKED_QTY;
                this.status.uncheckedProdId = unchecked.PROD_ID;
            } else {
                this.status.uncheckedProducts = 0;
                this.status.uncheckedProdId = this.status.lastProdId;
            }

            if (totalUnchecked < 1) {
                Minder.Despatches.AwaitingChecking.SsccPackDetails.startDimensions(sscc, this.getSsccWeight(sscc));
            }

            this.status.started = Minder.Despatches.AwaitingChecking.SsccPackDetails.isChecking();
            this.status.dimensionsStarted = Minder.Despatches.AwaitingChecking.SsccPackDetails.isAddingDimensions();
            this.messageBus.notifyCheckingStatusChanged(this.status);
        } else {
            showErrors(['SSCC Label not found.']);
        }
    },

    onLinesDespatchStatus: function(linesStatus) {
        this.linesErrors = [].concat(linesStatus.errors || [], linesStatus.warnings || []);
        this.status.totalSelectedItems = linesStatus.totalSelectedItems;
        if (!this.status.checkDetailsLoaded) {
            this.status.checkLineDetails    = linesStatus.checkLineDetails;
            this.status.packSscc            = linesStatus.packSscc;
            this.status.checkDetailsLoaded  = true;
        }
        this.status.nextCheckAbleSscc = this.getNextCheckAbleSscc();
        this.status.hasPrintedSscc = linesStatus.hasPrintedSscc;

        this.validatePackSsccData();

        this.status.uncheckedAmount = this.calculateUncheckedAmount(this.status.checkLineDetails);

        Minder.Despatches.AwaitingChecking.SsccPackDetails.setAcceptedUoms(this.getAcceptedSsccUoms());
        Minder.Despatches.AwaitingChecking.SsccPackDetails.fillChecked(this.getAcceptedSsccDimensions());
        this.status.started = Minder.Despatches.AwaitingChecking.SsccPackDetails.isChecking();
        this.status.dimensionsStarted = Minder.Despatches.AwaitingChecking.SsccPackDetails.isAddingDimensions();

        this.status.allItemsChecked = (this.status.totalSelectedItems > 0) && (this.status.uncheckedAmount < 1);

        this.messageBus.notifyCheckingStarted(this.status);
        this.messageBus.notifyCheckingStatusChanged(this.status);

        if (this.status.allItemsChecked) {
            this.messageBus.notifyCheckingComplete(this.status);
        }

    },

    calculateUncheckedAmount: function(checkLineDetails) {
        var
            uncheckedAmount = 0;

        $.each(checkLineDetails, function(key, lineDetail) {
            var
                picked, checked;
            if (lineDetail.SELECTED) {
                picked      = parseInt(lineDetail.PICKED_QTY);
                picked      = isNaN(picked) ? 0 : picked;
                checked     = parseInt(lineDetail.CHECKED_QTY);
                checked     = isNaN(checked) ? 0 : checked;

                uncheckedAmount += Math.abs(picked - checked);
            }
        });

        return uncheckedAmount;
    },

    getCheckDetailBySsccList: function(ssccList) {
        var
            result = [], pickLabelNos = ssccList.map(function(sscc){return sscc.PS_PICK_LABEL_NO});

        $.each(this.status.checkLineDetails, function(index, lineDetail){
            if (lineDetail.SELECTED && pickLabelNos.indexOf(lineDetail.PICK_LABEL_NO) > -1) {
                result.push(lineDetail);
            }
        });

        return result;
    },

    getSsccWeight: function(ssccList) {
        var totalWeight = 0,
            checkDetails = this.getCheckDetailBySsccList(ssccList);

        checkDetails.map(function(checkDetail){
            var prodWeight = parseFloat(checkDetail.PACK_WEIGHT);
            ssccList
                .filter(function(sscc){return sscc.PS_PICK_LABEL_NO === checkDetail.PICK_LABEL_NO})
                .forEach(function(sscc){totalWeight += (parseInt(sscc.CHECKED_QTY) || 0) * prodWeight;});
        });

        return totalWeight;
    },

    getSsccLabel: function(label) {
        var ssccList = [],
            checkDetail = [],
            ssccCnList = [];

        this.searchLog = [];

        $.each(this.status.packSscc, function(index, sscc){
            if (sscc.PS_OUT_SSCC === label) {
                if (sscc.PS_SSCC_STATUS.toUpperCase() == 'CN') {
                    ssccCnList.push(sscc.PS_SSCC);
                } else {
                    ssccList.push(sscc);
                }
            }
        });

        if (ssccList.length > 0) {
            this.searchLog.push('Searching for SSCC #' + label + '. Label found.');
            checkDetail = this.getCheckDetailBySsccList(ssccList);

            if (checkDetail.length < 1) {
                this.searchLog.push('Searching Order Lines for SSCC #' + label + '. None found.');
            }
        } else {
            if (ssccCnList.length > 0) {
                searchLog.push('Searching for SSCC #' + label + '. None found. But found records with CN status: (' + ssccCnList.join(', ') + ').');
            } else {
                searchLog.push('Searching for SSCC #' + label + '. None found.');
            }
        }

        return checkDetail.length > 0 ? ssccList : null;
    },

    hasUnaccepted: function(ssccList) {
        return ssccList.filter(function(sscc){return (sscc.PS_SSCC_STATUS.toUpperCase() == 'GO') || (sscc.PS_SSCC_STATUS.toUpperCase() == 'AC')}).length > 0;
    },

    getAcceptedSsccUoms: function() {
        function _toSsccUoms(ssccList) {
            return {
                'DT': ssccList[0].PS_SSCC_DIM_UOM,
                'WT': ssccList[0].PS_SSCC_WEIGHT_UOM
            };
        }

        var result = {}, processed = [], self = this;

        $.each(this.status.packSscc, function(ssccLabel, sscc){
            var foundSscc;
            if (processed.indexOf(sscc.PS_OUT_SSCC) > -1) {
                return;
            }

            processed.push(sscc.PS_OUT_SSCC);
            foundSscc = self.getSsccLabel(sscc.PS_OUT_SSCC);
            if (foundSscc && foundSscc.length > 0 && !self.hasUnaccepted(foundSscc)) {
                result = _toSsccUoms(foundSscc);
            }
        });

        return result;
    },

    getAcceptedSsccDimensions: function() {
        function _toSsccDimensionRow(sscc, ssccList) {
            return {
                'SSCC' : sscc.PS_OUT_SSCC,
                'TYPE': ssccList[0].PS_PACK_TYPE,
                'L': parseFloat(ssccList[0].PS_SSCC_DIM_X) || 0,
                'W': parseFloat(ssccList[0].PS_SSCC_DIM_Y) || 0,
                'H': parseFloat(ssccList[0].PS_SSCC_DIM_Z) || 0,
                'WT' : parseFloat(ssccList[0].PS_SSCC_WEIGHT) || 0
            }
        }

        var result = [], foundSscc, processed = [], ssccMap = this.status.packSscc, self = this;
        $.each(ssccMap, function(ssccLabel, sscc){
            if (processed.indexOf(sscc.PS_OUT_SSCC) > -1) {
                return;
            }

            processed.push(sscc.PS_OUT_SSCC);
            foundSscc = self.getSsccLabel(sscc.PS_OUT_SSCC);
            if (foundSscc && foundSscc.length > 0 && !self.hasUnaccepted(foundSscc)) {
                result.push(_toSsccDimensionRow(ssccMap[ssccLabel], foundSscc));
            }
        });

        return result;
    },

    validatePackSsccData: function() {
        function _getSsccList(checkLineDetail, packSscc) {
            var result = [];
            $.each(packSscc, function(index, packSscc){
                if (packSscc.PS_PICK_LABEL_NO == checkLineDetail.PICK_LABEL_NO) {
                    result.push(packSscc);
                }
            });

            return result;
        }

        function _getSsccStatistics(packSsccList) {
            var result = {
                checkedAmount: 0,
                canceledSscc: [],
                completedSscc: [],
                waitingSscc: []
            };

            packSsccList.forEach(function(packSscc){
                if (packSscc.PS_SSCC_STATUS.toUpperCase() == 'CN') {
                    result.canceledSscc.push(packSscc);
                    return;
                }

                result.checkedAmount += parseInt(packSscc.CHECKED_QTY) || 0;

                if (packSscc.completed) {
                    result.completedSscc.push(packSscc);
                } else {
                    result.waitingSscc.push(packSscc);
                }
            });

            return result;
        }

        function _validateData(checkLineDetail, ssccStatistics) {
            var checkedQty = parseInt(checkLineDetail.CHECKED_QTY) || 0,
                orderedQty = parseInt(checkLineDetail.PICKED_QTY) || 0;

            if (!checkLineDetail.SELECTED) {
                return;
            }

            if (orderedQty <= checkedQty) {
                return;
            }

            if (ssccStatistics.waitingSscc.length < 1) {
                if (ssccStatistics.canceledSscc.length > 0) {
                    var cancelledSscc = ssccStatistics.canceledSscc.map(function(packSscc){return packSscc.PS_SSCC;}).join(', ');
                    showErrors(['Pick Item #' + checkLineDetail.PICK_LABEL_NO + ': has unchecked products and no uncompleted PACK_SSCC left. But there are cancelled PACK_SSCC records: (' + cancelledSscc + ')']);
                } else {
                    showErrors(['Pick Item #' + checkLineDetail.PICK_LABEL_NO + ': has unchecked products and no uncompleted PACK_SSCC left.']);
                }
            }
        }

        var self = this;

        $.each(this.status.checkLineDetails, function(index, checkLineDetail){
            _validateData(checkLineDetail, _getSsccStatistics(_getSsccList(checkLineDetail, self.status.packSscc)));
        });
    },

    getUncompletedSscc: function(ssccList, checkDetail) {
        return ssccList.filter(function(sscc){return (!sscc.completed) && sscc.PS_PICK_LABEL_NO === checkDetail.PICK_LABEL_NO}).shift();
    },

    getNextCheckAbleSscc: function() {
        var emptySscc = {'PS_OUT_SSCC': ''};
        var checkDetail,
            ssccList = [];

        $.each(this.status.packSscc, function(index, sscc){
            if (sscc.PS_SSCC_STATUS.toUpperCase() == 'CN') {
                return;
            }

            if (!sscc.completed) {
                ssccList.push(sscc);
            }
        });

        checkDetail = this.getCheckDetailBySsccList(ssccList).shift();

        return checkDetail ? (this.getUncompletedSscc(ssccList, checkDetail) || emptySscc) : emptySscc;
    },

    onCheckingStarted: function() {
        if (Minder.Despatches.AwaitingChecking.SsccPackDetails.shouldGenerateSSCC()) {
            Minder.Despatches.AwaitingChecking.SsccPackDetails.start();
        }
    },

    onCheckingComplete: function() {
        Minder.Despatches.AwaitingChecking.SsccPackDetails.stop();

        if (this.linesErrors.length > 0) {
            showErrors(this.linesErrors);

            if (!this.attributes.isSysAdmin) {
                return;
            }
        }

        this.messageBus.notifyShowConnote(this.acceptUrl);
    },

    onDestroy: function() {
        this.messageBus.notifyStopSubscriptionToBarcodeTypeRequest(this);
        Minder.Despatches.AwaitingChecking.SsccPackDetails.off('edi-all');
        Minder.Despatches.AwaitingChecking.SsccPackDetails.reset();
        this.stopListening();
    }

});