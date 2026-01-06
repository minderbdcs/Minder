var CheckStrategyEdiOne = (function() {
    var PRODUCT_NOT_FOUND_MSG = 'Product not found.';

    var PickItem = Backbone.Model.extend({
        idAttribute: 'PICK_LABEL_NO',

        initialize: function(){
            this.packSsccList = new PackSsccCollection();
            this.outSsccList  = new OutSsccCollection();
        },

        canBeChecked: function() {
            return this.canBeDespatched() && (this.getUncheckedQty() > 0);
        },

        canBeDespatched: function() {
            return this.isPicked()
                && this.getPickedQty() > 0
                && this.hasCorrectWarehouse();
        },

        hasCorrectWarehouse: function() {
            return this.get('ORDER_WH_ID').toUpperCase() == this.get('ITEM_WH_ID').toUpperCase();
        },

        isPicked: function() {
            return this.isFullyPicked()
                || (this.isPartialPickAllowed() && this.isPartiallyPicked());
        },

        isFullyPicked: function() {
            return ['PL', 'DS', 'AC', 'CK'].indexOf(this.get('PICK_LINE_STATUS').toUpperCase()) > -1;
        },

        isPartiallyPicked: function() {
            return this.get('PICK_LINE_STATUS').toUpperCase() == 'AL';
        },

        explainCannotBeChecked: function(prodId, checkAmount) {
            if (this.get('PROD_ID') != prodId && this.get('ALTERNATE_ID') != prodId) {
                return 'productNotMatched';
            }

            if (!this.canBeDespatched()) {
                return 'productNotMatched';
            }

            if (this.isChecked()) {
                return 'checked';
            }

            return 'notEnoughItems';
        },

        getUncheckedQty: function() {
            return this.getPickedQty() - this.getCheckedQty();
        },

        isChecked: function() {
            return this.getPickedQty() > 0 && this.getUncheckedQty() < 1;
        },

        onPackSsccAdded: function(packSscc) {
            this.set('checkedQty', this.getCheckedQty() + packSscc.getCheckedQty());
        },

        onPackSsccRemoved: function(packSscc) {
            this.set('checkedQty', this.getCheckedQty() - packSscc.getCheckedQty());
        },

        getPickedQty: function() {
            return parseInt(this.get('PICKED_QTY')) || 0;
        },

        getCheckedQty: function() {
            return parseInt(this.get('CHECKED_QTY')) || 0;
        },

        wasUncheckedQty: function() {
            return this.wasPickedQty() - this.wasCheckedQty();
        },

        wasPickedQty: function() {
            return parseInt(this.previous('PICKED_QTY')) || 0;
        },

        wasCheckedQty: function() {
            return parseInt(this.previous('CHECKED_QTY')) || 0;
        },

        getPackWeight: function() {
            return parseFloat(this.get('PACK_WEIGHT')) || 0;
        },

        isPartialPickAllowed: function() {
            return (this.get('PARTIAL_PICK_ALLOWED') || '').toUpperCase() == 'T';
        }
    });

    var PickItemCollection = Backbone.Collection.extend({
        model: PickItem
    });

    var PackSscc = Backbone.Model.extend({
        idAttribute: 'RECORD_ID',

        defaults: {
            RECORD_ID: '',
            itemCanBeChecked: false,
            availableQty: 0,
            partialPickAllowed: false,
            packWeight: 0,
            prodId: '',
            altId: ''
        },

        _fillFromPickItem: function(pickItem) {
            this.set({
                'itemCanBeChecked': pickItem.canBeChecked(),
                'availableQty': pickItem.getUncheckedQty(),
                'partialPickAllowed': pickItem.isPartialPickAllowed(),
                'packWeight': pickItem.getPackWeight(),
                'prodId': pickItem.get('PROD_ID'),
                'altId': pickItem.get('ALTERNATE_ID')
            });
        },

        setPickItem: function(pickItem) {
            if (this.pickItem) {
                this.stopListening(this.pickItem);
            }

            this.pickItem = pickItem;
            this.listenTo(this.pickItem, 'change', this.onPickItemChanged);

            this._fillFromPickItem(pickItem);
        },

        onPickItemChanged: function(pickItem) {
            this._fillFromPickItem(pickItem);
        },

        explainCannotCheckProduct: function(prodId, checkAmount) {
            if (this.isCancelled()) {
                return 'cancelled';
            }
            if (this.get('prodId') != prodId && this.get('altId') != prodId) {
                return 'productNotMatched';
            }
            if (this.isChecked()) {
                return 'checked';
            }
            if (this.isChecking() && this.getUncheckedQty() < 1) {
                return 'checked';
            }

            return this.getUncheckedQty() < checkAmount ? 'notEnoughItems' : 'can';
        },

        explainCannotBeChecked: function(checkAmount) {
            if (this.isCancelled()) {
                return 'cancelled';
            }
            if (this.isChecked()) {
                return 'checked';
            }
            if (this.isChecking() && this.getUncheckedQty() < 1) {
                return 'checked';
            }

            return this.getUncheckedQty() < checkAmount ? 'notEnoughItems' : 'can';
        },

        explainCannotCancelCheck: function() {
            if (this.isCancelled()) {
                return 'cancelled';
            }
            if (this.isChecked()) {
                return 'checked';
            }
            if (this.isChecking()) {
                return 'can';
            }

            return 'other';
        },

        checkProdId: function(prodId, checkAmount) {
            if (this.get('prodId') != prodId) {
                return null;
            }

            return (this.isChecking() && this.getUncheckedQty() > 0)
                ? {'RECORD_ID': this.get('RECORD_ID'), 'qty': Math.min(checkAmount, this.getUncheckedQty())}
                : null;
        },

        checkAltId: function(prodId, checkAmount) {
            if (this.get('altId') != prodId) {
                return null;
            }

            return (this.isChecking() && this.getUncheckedQty() > 0)
                ? {'RECORD_ID': this.get('RECORD_ID'), 'qty': Math.min(checkAmount, this.getUncheckedQty())}
                : null;
        },

        getStatus: function() {
            return (this.get('PS_SSCC_STATUS') || '').toUpperCase();
        },

        _isChecked: function(status) {
            return status == 'CL' || status == 'DC' || status == 'DX';
        },

        _isCancelled: function(status) {
            return status == 'CN';
        },

        _isChecking: function(status) {
            return status == 'AC' || status == 'GO';
        },

        wasChecked: function() {
            return this._isChecked((this.previous('PS_SSCC_STATUS') || '').toUpperCase());
        },

        wasCancelled: function() {
            return this._isCancelled((this.previous('PS_SSCC_STATUS') || '').toUpperCase());
        },

        wasChecking: function() {
            return this._isChecking((this.previous('PS_SSCC_STATUS') || '').toUpperCase());
        },

        isChecked: function() {
            return this._isChecked(this.getStatus());
        },

        isCancelled: function() {
            return this._isCancelled(this.getStatus());
        },

        isChecking: function() {
            return this._isChecking(this.getStatus());
        },

        canBeChecked: function() {
            return this.isChecking() && this.getUncheckedQty() > 0;
        },

        wasCheckedQty: function() {
            return parseInt(this.previous('PS_QTY_SHIPPED')) || 0;
        },

        getCheckedQty: function() {
            return parseInt(this.get('PS_QTY_SHIPPED')) || 0;
        },

        addCheckedQty: function(diff) {
            this.set('PS_QTY_SHIPPED', this.getCheckedQty() + diff);
        },

        getOrderedQty: function() {
            return parseInt(this.get('PS_QTY_ORDERED')) || 0;
        },

        _getUncheckedQty: function(partialPickAllowed, orderedQty, availableQty, checkedQty) {
            return Math.min(orderedQty, availableQty) - checkedQty;
        },

        getUncheckedQtyDiff: function() {
            return this.getUncheckedQty() - this._getUncheckedQty(
                    this.previous('partialPickAllowed'),
                    parseInt(this.previous('PS_QTY_ORDERED')) || 0,
                    this.previous('availableQty'),
                    parseInt(this.previous('PS_QTY_SHIPPED')) || 0
                );
        },

        getUncheckedQty: function() {
            return this._getUncheckedQty(this.get('partialPickAllowed'), this.getOrderedQty(), this.get('availableQty'), this.getCheckedQty());
        },

        _getCheckedWeight: function(applicable, checkedQty, packWeight) {
            return applicable ? checkedQty * packWeight : 0;
        },

        getCheckedWeight: function() {
            return this._getCheckedWeight(this.isChecking() || this.isChecked(), this.getCheckedQty(), this.get('packWeight'));
        },

        _getCheckedWeightWas: function() {
            return this._getCheckedWeight(this.wasChecking() || this.wasChecked(), this.wasCheckedQty(), this.previous('packWeight'));
        },

        getCheckedWeightDiff: function() {
            return this.getCheckedWeight() - this._getCheckedWeightWas();
        }
    });

    var PackSsccCollection = Backbone.Collection.extend({
        model: PackSscc
    });

    var ProductStatus = Backbone.Model.extend({
        idAttribute: 'PROD_ID',

        defaults: {
            'PROD_ID': '',
            'uncheckedQty': 0
        },

        addUncheckedQty: function(qty) {
            this.set('uncheckedQty', this.get('uncheckedQty') + qty);
        },

        getUncheckedQtyDiff: function() {
            return this.get('uncheckedQty') - this.previous('uncheckedQty');
        }

    });

    var ProductStatusCollection = Backbone.Collection.extend({
        model: ProductStatus,

        findUncheckedProduct: function() {
            return this.find(function(productStatus){return productStatus.get('uncheckedQty') > 0 });
        }
    });

    var OutSscc = Backbone.Model.extend({
        idAttribute: 'PS_OUT_SSCC',

        defaults: {
            nextCheckAbleProduct: {},
            totalWeight: 0,
            totalUnchecked: 0,
            checked: 0,
            cancelled: 0,
            checkAble: 0,
            other: 0
        },

        initialize: function() {
            this.pickItemList = new PickItemCollection();
            this.packSsccList = new PackSsccCollection();
            this.productStatusList = new ProductStatusCollection();

            this.listenTo(this.packSsccList, 'add', this.onPackSsccAdded);
            this.listenTo(this.packSsccList, 'remove', this.onPackSsccRemoved);
            this.listenTo(this.packSsccList, 'change', this.onPackSsccChanged);

            this.listenTo(this.productStatusList, 'change', this.onProductStatusListChanged);
        },

        getPackSsccList: function() {
            return this.packSsccList.toJSON();
        },

        getUncheckedQty: function() {
            return this.get('totalUnchecked');
        },

        addTotalUnchecked: function(diff) {
            this.set('totalUnchecked', this.get('totalUnchecked') + diff);
        },

        onProductStatusListChanged: function(productStatus) {
            this.set({
                nextCheckAbleProduct: _.clone((this.productStatusList.findUncheckedProduct() || new ProductStatus()).attributes),
                totalUnchecked: this.get('totalUnchecked') + productStatus.getUncheckedQtyDiff()
            });

        },

        _checkProdId: function(prodId, checkAmount) {
            var result = [],
                index = this.packSsccList.length,
                checkDetail;

            while ((checkAmount > 0) && (index-- > 0)) {
                checkDetail = this.packSsccList.at(index).checkProdId(prodId, checkAmount);

                if (checkDetail) {
                    checkAmount -= checkDetail.qty;
                    result.push(checkDetail);
                }
            }

            return (checkAmount > 0) ? [] : result;
        },

        _checkAltProdId: function(prodId, checkAmount) {
            var result = [],
                index = this.packSsccList.length,
                checkDetail;

            while ((checkAmount > 0) && (index-- > 0)) {
                checkDetail = this.packSsccList.at(index).checkAltId(prodId, checkAmount);

                if (checkDetail) {
                    checkAmount -= checkDetail.qty;
                    result.push(checkDetail);
                }
            }

            return (checkAmount > 0) ? [] : result;
        },

        checkProduct: function(prodId, checkAmount){
            var checkDetails = this._checkProdId(prodId, checkAmount);

            if (checkDetails.length < 1) {
                checkDetails = this._checkAltProdId(prodId, checkAmount);
            }

            return checkDetails;
        },

        explainCannotCheckProduct: function(prodId, checkAmount) {
            var explainResults = this.packSsccList.reduce(function(result, packSscc) {
                result[packSscc.explainCannotCheckProduct(prodId, checkAmount)]++;
                return result;
            }, {
                cancelled: 0,
                checked: 0,
                productNotMatched: 0,
                notEnoughItems: 0
            });

            if (explainResults.notEnoughItems > 0) {
                return ['Not enough picked items.'];
            }

            if (explainResults.productNotMatched) {
                return [PRODUCT_NOT_FOUND_MSG];
            }

            if (explainResults.checked > 0) {

                return ['All items checked.'];
            }

            if (explainResults.cancelled) {
                return ['SSCC is cancelled'];
            }

            return [];
        },

        explainCannotCheckSscc: function(recordId, checkAmount) {
            var packSscc = this.packSsccList.get(recordId);

            if (packSscc) {
                switch (packSscc.explainCannotBeChecked(checkAmount)) {
                    case 'notEnoughItems':
                        return ['SSCC RECORD_ID #' + recordId + ' not enough items to check.'];
                    case 'cancelled':
                        return ['SSCC RECORD_ID #' + recordId + ' is cancelled.'];
                    case 'checked':
                        return ['SSCC RECORD_ID #' + recordId + ' already checked.'];
                    default:
                        return [];
                }
            }

            return ['SSCC RECORD_ID #' + recordId + ' not found.']
        },

        explainCannotCancelSsccCheck: function() {
            var explainResults = this.packSsccList.reduce(function(result, packSscc) {
                result[packSscc.explainCannotCancelCheck()]++;
                return result;
            }, {
                cancelled: 0,
                checked: 0,
                can: 0
            });

            if (explainResults.checked > 0) {
                return ['Already checked.'];
            } else {
                if (explainResults.can < 1) {
                    if (explainResults.cancelled > 0) {
                        return ['SSCC is cancelled.'];
                    } else {
                        return ['SSCC is not checking.'];
                    }
                }
            }

            return [];
        },

        _mapStatusIs: function(packSscc) {
            switch (true) {
                case packSscc.isChecked():
                    return 'checked';
                case packSscc.isChecking():
                    return 'checkAble';
                case packSscc.isCancelled():
                    return 'cancelled';
                default :
                    return 'other';
            }
        },

        _mapStatusWas: function(packSscc) {
            switch (true) {
                case packSscc.wasChecked():
                    return 'checked';
                case packSscc.wasChecking():
                    return 'checkAble';
                case packSscc.wasCancelled():
                    return 'cancelled';
                default :
                    return 'other';
            }
        },

        onPackSsccAdded: function(packSscc) {
            var statusIs = this._mapStatusIs(packSscc),
                productStatus,
                data = {
                    totalWeight: this.getTotalWeight() + packSscc.getCheckedWeight()
                };

            data[statusIs] = this.get(statusIs) + 1;
            this.set(data);

            if (packSscc.canBeChecked()) {
                productStatus = this.productStatusList.add({'PROD_ID': packSscc.get('prodId')});
                productStatus.addUncheckedQty(packSscc.getUncheckedQty());
            }
        },

        onPackSsccRemoved: function(packSscc) {
            var statusIs = this._mapStatusIs(packSscc),
                productStatus,
                data = {
                    totalWeight: this.getTotalWeight() - packSscc.getCheckedWeight()
                };
            data[statusIs] = this.get(statusIs) - 1;
            this.set(data);

            if (packSscc.canBeChecked()) {
                productStatus = this.productStatusList.add({'PROD_ID': packSscc.get('prodId')});
                productStatus.addUncheckedQty(-packSscc.getUncheckedQty());
            }
        },

        onPackSsccChanged: function(packSscc) {
            var statusIs = this._mapStatusIs(packSscc),
                statusWas = this._mapStatusWas(packSscc),
                productWas = this.productStatusList.add({'PROD_ID': packSscc.previous('prodId')}),
                productIs = this.productStatusList.add({'PROD_ID': packSscc.get('prodId')}),
                data = {
                    totalWeight: this.getTotalWeight() + packSscc.getCheckedWeightDiff()
                };

            if (statusWas != statusIs) {
                data[statusWas] = this.get(statusWas) - 1;
                data[statusIs] = this.get(statusIs) + 1;
            }

            this.set(data);
            productIs.addUncheckedQty(packSscc.getUncheckedQtyDiff());

            if (productIs.get('PROD_ID') != productWas.get('PROD_ID')) {
                productWas.addUncheckedQty(-packSscc.getUncheckedQtyDiff());
            }
        },

        canBeChecked: function() {
            return this.attributes.checkAble > 0 && this.attributes.checked < 1;
        },

        explainCannotBeChecked: function() {
            var result = [];

            if (this.attributes.checked > 0) {
                result.push('SSCC #' + this.attributes.PS_OUT_SSCC + ' already checked.');
            } else if (this.attributes.checkAble < 1) {
                if (this.attributes.cancelled > 0) {
                    result.push('SSCC #' + this.attributes.PS_OUT_SSCC + ' is cancelled.');
                } else {
                    result.push('SSCC #' + this.attributes.PS_OUT_SSCC + ' has bad statuses: [' + this.packSsccList.pluck('PS_SSCC_STATUS').join(', ') + '].');
                }
            }

            return result;
        },

        getTotalWeight: function() {
            return this.get('totalWeight');
        }
    });

    var OutSsccCollection = Backbone.Collection.extend({
        model: OutSscc
    });

    var PackSsccCheckStatus = Backbone.Model.extend({
        idAttribute: 'RECORD_ID',

        defaults: {
            RECORD_ID: '',
            PICK_LABEL_NO: '',
            checkedQty: 0
        },

        addCheckedQty: function(qty) {
            this.set('checkedQty', this.get('checkedQty') + qty);
        },

        getCheckedQtyDiff: function() {
            return this.get('checkedQty') - this.previous('checkedQty');
        }
    });

    var PackSsccCheckStatusCollection = Backbone.Collection.extend({
       model: PackSsccCheckStatus
    });

    var CheckStatus = Backbone.Model.extend({
        defaults: {
            outSscc: '',
            checkedQty: 0,
            checkingStarted: false,
            dimensionsStarted: false,
            completed: false,
            packSsccCheckStatus: []
        },

        initialize: function() {
            this.packSsccCheckList = new PackSsccCheckStatusCollection();
            this.listenTo(this.packSsccCheckList, 'change', this.onPackSsccCheckListChanged);
        },

        onPackSsccCheckListChanged: function(packSsccCheckStatus) {
            var newCheckedQty = this.get('checkedQty') + packSsccCheckStatus.getCheckedQtyDiff();

            this.set({
                'packSsccCheckStatus' : this.packSsccCheckList.toJSON(),
                'checkedQty': newCheckedQty
            });
        },

        reset: function() {
            this.set(this.defaults);
            this.packSsccCheckList.reset();
            this.trigger('status-reset', this);
        },

        start: function(outSscc) {
            this.set({
                outSscc: outSscc,
                checkingStarted: true
            });
        },

        explainCannotStartDimensions: function(outSscc) {
            var errors = [];

            if (outSscc != this.get('outSscc')) {
                errors.push(['SSCC label does not match.']);
            }

            if (this.get('checkedQty') < 1) {
                errors.push(['No items checked.']);
            }

            return errors;
        },

        startDimensions: function(outSscc) {
            this.set({
                'dimensionsStarted': true
            });
        },

        stopDimensions: function() {
            this.set({
                'dimensionsStarted': false
            });
        },

        isStarted: function() {
            return this.get('checkingStarted');
        },

        addCheckDetail: function(recordId, checkAmount) {
            var itemCheckDetail = this.packSsccCheckList.add({'RECORD_ID': recordId});
            itemCheckDetail.addCheckedQty(checkAmount);
       },

        dimensionsStarted: function() {
            return this.get('dimensionsStarted');
        }
    });

    return Backbone.Model.extend({
        messageBus: null,
        storeStatusUrl: '',
        acceptUrl: '',
        getRetailUnitUrl: '',

            initialize: function (attributes, options) {
            this.messageBus = attributes.messageBus;
            this.storeStatusUrl = options.storeStatusUrl;
            this.getRetailUnitUrl = attributes.getRetailUnitUrl;
            this.acceptUrl = options.acceptUrl;

            Minder.Despatches.AwaitingChecking.SsccPackDetails.reset();
            Minder.Despatches.AwaitingChecking.SsccPackDetails.onSsccLabelCancelAccept('edi-one', $.proxy(this.onPackSsccDimensionsCancelAccept, this));
            Minder.Despatches.AwaitingChecking.SsccPackDetails.onSsccLabelBeforeAccept('edi-one', $.proxy(this.onSsccBeforeAccept, this));
            Minder.Despatches.AwaitingChecking.SsccPackDetails.setVolumeAndWeightPolicy(attributes.volumeIsRequired, attributes.weightIsRequired);

            this.bindMessageBusEvents();

            this.pickItemList = new PickItemCollection();
            this.packSsccList = new PackSsccCollection();
            this.outSsccList = new OutSsccCollection();
            this.checkStatus = new CheckStatus();
            this.orderStatistics = new OrderStatistics();

            this.listenTo(this.packSsccList, 'add', this.onPackSsccAdded);
            this.listenTo(this.packSsccList, 'change', this.onPackSsccChanged);
            this.listenTo(this.packSsccList, 'remove', this.onPackSsccRemoved);
            this.listenTo(this.checkStatus.packSsccCheckList, 'change', this.onPackSsccCheckStatusChanged);
            this.listenTo(this.checkStatus, 'change', this.onCheckStatusChanged);
            this.listenTo(this.outSsccList, 'change', this.onOutSsccChanged);
            this.listenTo(this.orderStatistics, 'change', this.onOrderStatisticsChanged);
            this.listenTo(this.checkStatus, 'status-reset', this.onCheckStatusReset);

            if (attributes.selectedOrdersAmount > 0) {
                this.messageBus.notifyLoadEdiLinesRequest();
            }
            this.listenTo(this, 'destroy', this.onDestroy);
            this.messageBus.notifyCheckingStrategyEdiOne();
        },

        onCheckStatusReset: function(checkStatus) {
            this.messageBus.notifyEdiOneCheckStatusReset(checkStatus.toJSON());
        },

        onCheckStatusChanged: function(checkStatus) {
            $.post(this.storeStatusUrl, {status: checkStatus.toJSON()});
        },

        onOutSsccChanged: function(outSscc) {
            var errors;

            if (outSscc.get('PS_OUT_SSCC') == this.checkStatus.get('outSscc')) {
                this.notifyOutSsccStatus(outSscc);

                if (outSscc.getUncheckedQty() < 1) {
                    errors = this.checkStatus.explainCannotStartDimensions(outSscc.get('PS_OUT_SSCC'));

                    if (errors.length > 0) {
                        showErrors(errors);
                    } else {
                        this.startDimensions(outSscc.get('PS_OUT_SSCC'));
                    }
                }
            }
        },

        onOrderStatisticsChanged: function(orderStatistics) {
            this.messageBus.notifyEdiOneOrderStatisticsChanged(orderStatistics);
        },

        bindMessageBusEvents: function () {
            this.messageBus.onCheckSsccLabelRequest(this.onCheckSsccLabelRequest, this);
            this.messageBus.onEdiDespatchDataChanged(this.onEdiDespatchDataChanged, this);
            this.messageBus.onCheckProdIdRequest(this.onCheckProdIdRequest, this);
            this.messageBus.onEdiOnePackDimensionsAccepted(this.onEdiOnePackDimensionsAccepted, this);
            this.messageBus.onCheckingComplete(this.onCheckingComplete, this);
            this.messageBus.onScreenButtonAccept(this.onScreenButtonAccept, this);
            this.messageBus.onUiCancelSsccCheckRequest(this.onUiCancelSsccCheckRequest, this);

            this.messageBus.notifySubscribeToProdEanTypeRequest(this, this.onBarcodeTypeProdEan);
        },

        onUiCancelSsccCheckRequest: function() {
            var errors, outSscc;

            if (this.checkStatus.isStarted()) {
                outSscc = this.outSsccList.get(this.checkStatus.get('outSscc'));
                errors = outSscc.explainCannotCancelSsccCheck();

                if (errors.length > 0) {
                    showErrors(errors);
                } else {
                    this.messageBus.notifyCancelSsccCheckRequest(this.checkStatus.get('outSscc'));
                }
            } else {
                showErrors(['SSCC is not checking.']);
            }
        },

        onCheckingComplete: function(status) {
            this.messageBus.notifyShowConnote(this.acceptUrl);
        },

        onPackSsccAdded: function(packSscc) {
            var pickItem = this.pickItemList.add({'PICK_LABEL_NO': packSscc.get('PS_PICK_LABEL_NO')}, {merge: true}),
                outSscc = this.outSsccList.add({'PS_OUT_SSCC': packSscc.get('PS_OUT_SSCC')}, {merge: true});

            packSscc.setPickItem(pickItem);

            //pickItem.packSsccList.add(packSscc);
            outSscc.packSsccList.add(packSscc);
        },

        onPackSsccChanged: function(packSscc) {
            var outSsccWas = packSscc.previous('PS_OUT_SSCC'),
                outSsccIs = packSscc.get('PS_OUT_SSCC'),
                outSscc;

            if (outSsccIs != outSsccWas) {
                outSscc = this.outSsccList.add({'PS_OUT_SSCC': outSsccWas}, {merge: true});
                outSscc.packSsccList.remove(packSscc);
                outSscc = this.outSsccList.add({'PS_OUT_SSCC': outSsccIs}, {merge: true});
                outSscc.packSsccList.add(packSscc);
            }

            this.messageBus.notifyPackSsccUpdated(packSscc);
        },

        onPackSsccRemoved: function(packSscc) {
            var pickItem = this.pickItemList.add({'PICK_LABEL_NO': packSscc.get('PS_PICK_LABEL_NO')}, {merge: true}),
                outSscc = this.outSsccList.add({'PS_OUT_SSCC': packSscc.get('PS_OUT_SSCC')}, {merge: true});

            //pickItem.packSsccList.remove(packSscc);
            outSscc.packSsccList.remove(packSscc);
        },

        onPackSsccCheckStatusChanged: function(checkStatus) {
            var packSscc = this.packSsccList.get(checkStatus.get('RECORD_ID'));
            this.orderStatistics.addCheckedQty(checkStatus.getCheckedQtyDiff());
            packSscc.addCheckedQty(checkStatus.getCheckedQtyDiff());
        },

        onSsccBeforeAccept: function(evt) {
            evt.preventDefault();
            var acceptRequest = evt.acceptRequest,
                validateResult = Minder.Despatches.AwaitingChecking.SsccPackDetails.validateAcceptRequest(acceptRequest);

            showResponseMessages(validateResult);

            if (validateResult.hasErrors()) {
                return;
            }

            acceptRequest.checkStatus = this.checkStatus.toJSON();

            this.messageBus.notifyEdiOnePackAcceptRequest(acceptRequest);
        },

        onEdiDespatchDataChanged: function (data) {
            var outSscc, self = this;

            this.stopListening(this.checkStatus, 'change');
            this.stopListening(this.outSsccList, 'change');
            this.stopListening(this.orderStatistics, 'change');

            this.orderStatistics.set(data.ediOrderStatistics);
            this.pickItemList.set(data.pickItems);
            this.packSsccList.set(data.packSscc);
            Minder.Despatches.AwaitingChecking.SsccPackDetails.reset();
            outSscc = this.outSsccList.get(data.checkingStatus.outSscc);

            this.checkStatus.reset();
            if (data.checkingStatus.checkingStarted && !data.checkingStatus.completed) {

                if (!outSscc.canBeChecked()) {
                    showErrors(outSscc.explainCannotBeChecked());
                }
                this.checkStatus.start(data.checkingStatus.outSscc);

                data.checkingStatus.packSsccCheckStatus.forEach(function(checkStatus){
                    var checkedQty = parseInt(checkStatus.checkedQty) || 0,
                        errors = outSscc.explainCannotCheckSscc(checkStatus.RECORD_ID, checkedQty);

                    if (errors.length) {
                        showErrors(errors);
                    }

                    self.checkStatus.addCheckDetail(checkStatus.RECORD_ID, checkedQty);
                });

                if (data.checkingStatus.dimensionsStarted) {
                    var errors = this.checkStatus.explainCannotStartDimensions(data.checkingStatus.outSscc);

                    if (errors.length > 0) {
                        showErrors(errors);
                    }
                    this.startDimensions(data.checkingStatus.outSscc);
                }
            }

            this.listenTo(this.checkStatus, 'change', this.onCheckStatusChanged);
            this.listenTo(this.outSsccList, 'change', this.onOutSsccChanged);
            this.listenTo(this.orderStatistics, 'change', this.onOrderStatisticsChanged);

            this.messageBus.notifyPackSsccListStatusUpdateRequest(this.packSsccList);
            this.messageBus.notifyEdiOneOrderStatisticsChanged(this.orderStatistics);
            if (outSscc) {
                this.notifyOutSsccStatus(outSscc);
            }

            if (data.checkingStatus.completed) {
                this.messageBus.notifyEdiOneDimensionsAccepted();
            } else {
                if (this.orderStatistics.canBeDespatched()) {
                    this.notifyCheckingComplete();
                }
            }

        },

        notifyOutSsccStatus: function(outSscc) {
            this.messageBus.notifyCheckingStatusChanged({
                isEdi: true,
                started: this.checkStatus.isStarted(),
                dimensionsStarted: this.checkStatus.dimensionsStarted(),
                lastSscc: [{'PS_OUT_SSCC': outSscc.attributes.PS_OUT_SSCC}],
                uncheckedSsccItems: outSscc.attributes.totalUnchecked,
                uncheckedProducts: outSscc.attributes.nextCheckAbleProduct.uncheckedQty,
                uncheckedProdId: outSscc.attributes.nextCheckAbleProduct.PROD_ID,
                nextCheckAbleSscc: {
                    PS_OUT_SSCC: this.orderStatistics.get('nextPrintedSscc')
                },
                hasPrintedSscc: !!this.orderStatistics.get('nextPrintedSscc')
            });
        },

        startDimensions: function(ssccLabel) {
            var outSscc = this.outSsccList.get(ssccLabel),
                packSsccList = outSscc.getPackSsccList();

            this.checkStatus.startDimensions(ssccLabel);
            Minder.Despatches.AwaitingChecking.SsccPackDetails.start();
            Minder.Despatches.AwaitingChecking.SsccPackDetails.startCheck(packSsccList);
            Minder.Despatches.AwaitingChecking.SsccPackDetails.startDimensions(packSsccList, outSscc.getTotalWeight());
            this.notifyOutSsccStatus(outSscc);
        },

        onPackSsccDimensionsCancelAccept: function() {
            var outSscc = this.outSsccList.get(this.checkStatus.get('outSscc'));

            Minder.Despatches.AwaitingChecking.SsccPackDetails.reset();
            this.checkStatus.stopDimensions();

            if (outSscc) {
                this.notifyOutSsccStatus(outSscc);
            }
        },

        onCheckSsccLabelRequest: function (ssccLabel) {
            var outSscc = this.outSsccList.get(ssccLabel),
                errors;

            if (outSscc) {
                if (this.checkStatus.isStarted()) {
                    errors = this.checkStatus.explainCannotStartDimensions(ssccLabel);

                    if (errors.length > 0) {
                        showErrors(errors);
                    } else {
                        this.startDimensions(ssccLabel);
                    }

                } else {
                    this.messageBus.startSsccCheckRequest(ssccLabel);
                }
            } else {
                this.messageBus.startSsccCheckRequest(ssccLabel);
            }
        },

        onBarcodeTypeProdEan: function(dataIdentifier) {
            $.get(this.getRetailUnitUrl, {'code': dataIdentifier.param_filtered_value}, $.proxy(this.checkSsccRetailUnitCallback, this), 'json');
        },

        checkSsccRetailUnitCallback: function(response) {
            var outSscc = this.outSsccList.get(this.checkStatus.get('outSscc')), checkQty;

            showResponseMessages(response);

            if (response.retailUnit) {
                checkQty = parseInt(response.retailUnit.PROD_ISSUE_QTY) || 0;

                if (checkQty > 0) {
                    if (this.checkStatus.isStarted()) {
                        if (this.checkStatus.dimensionsStarted()) {
                            showErrors(['You should ACCEPT previous SSCC label.']);
                        } else {
                            this._doCheck(outSscc, response.retailUnit.PROD_ID, checkQty);
                        }
                    } else {
                        showErrors(['Scan SSCC label first.']);
                    }
                }
            }
        },

        onCheckProdIdRequest: function(prodId) {
            var outSscc = this.outSsccList.get(this.checkStatus.get('outSscc'));

            if (this.checkStatus.isStarted()) {
                if (this.checkStatus.dimensionsStarted()) {
                    showErrors(['You should ACCEPT previous SSCC label.']);
                } else {
                    this._doCheck(outSscc, prodId, 1);
                }
            } else {
                showErrors(['Scan SSCC label first.']);
            }

        },

        _doCheck: function(outSscc, prodId, checkAmount) {
            var checkStatuses = outSscc.checkProduct(prodId, checkAmount),
                self = this,
                errors;

            if (checkStatuses.length < 1) {
                errors = outSscc.explainCannotCheckProduct(prodId, checkAmount);

                if (errors.length > 0) {
                    showErrors(errors);
                }
            } else {
                checkStatuses.forEach(function(checkStatus){
                    self.checkStatus.addCheckDetail(checkStatus.RECORD_ID, checkStatus.qty);
                });
            }
        },

        onEdiOnePackDimensionsAccepted: function() {
            this.checkStatus.reset();
            this.messageBus.notifyEdiOneOrderStatisticsChanged(this.orderStatistics);

            if (this.orderStatistics.canBeDespatched()) {
                this.notifyCheckingComplete();
            }
        },

        notifyCheckingComplete: function() {
            var status = {
                isEdi: true,
                lastProdId: null,
                lastSscc: null,
                canBeChecked: true,
                shouldCheckEachItem: true,
                allItemsChecked: true,
                totalSelectedItems: 0,
                uncheckedAmount: 0,
                uncheckedSsccItems: 0,
                uncheckedProducts: 0,
                uncheckedProdId: null,
                nextCheckAbleSscc: null,
                hasPrintedSscc: false,
                checkLineDetails: []
            };

            this.messageBus.notifyCheckingStatusChanged(status);
            this.messageBus.notifyCheckingComplete(status);
        },

        onScreenButtonAccept: function() {
            if (Minder.Dlg.RePackSscc.isVisible()) {
                this.messageBus.notifyScreenButtonServed();
                Minder.Dlg.RePackSscc.accept();
            }else if (this.checkStatus.dimensionsStarted()) {
                this.messageBus.notifyScreenButtonServed();
                Minder.Despatches.AwaitingChecking.SsccPackDetails.accept(false);
            }
        },

        onDestroy: function () {
            this.messageBus.notifyStopSubscriptionToBarcodeTypeRequest(this);
            Minder.Despatches.AwaitingChecking.SsccPackDetails.off('edi-one');
            Minder.Despatches.AwaitingChecking.SsccPackDetails.reset();
            this.stopListening();
        }

    });
})();