var ConnoteView = Backbone.View.extend({
    messageBus: null,

    linesErrors: [],

    isSysAdmin: false,

    loadUrl: '',

    acceptUrl: '',

    loadData: {},

    checkingStatus: {},

    skipLabelPrinting: false,

    isEdiOrder: false,

    ssccAwbConsignmentNo: '',

    dimensionUoms: {},

    initialize: function(options) {
        this.messageBus = options.messageBus;
        this.loadData = options.loadData;
        this.loadUrl = options.loadUrl;
        this.acceptUrl = options.acceptUrl;
        this.isSysAdmin = options.isSysAdmin;
        this.dimensionUoms = options.dimensionUoms;

        this.ssccTotals = this._getSsccTotalDefaults();

        this.bindUiEvents();
        this.bindMessageBusEvents();
        this.checkingStatus = {};
    },

    _getSsccTotalDefaults: function() {
        return {
            VOL: 0,
            WT: 0,
            PACKS: {
                PALLETS: 0,
                SATCHELS: 0,
                CARTONS: 0,
                TOTAL: 0
            }
        };
    },

    bindUiEvents: function() {
        this.$el.bind('cancel', $.proxy(this.onCancelDespatch, this));
        this.$el.delegate('input[name=accept_btn]', 'click', $.proxy(this.onAcceptConnote, this));
        this.$el.delegate('input[name=cancel_btn]', 'click', $.proxy(this.onCancelDespatch, this));
        this.$el.delegate('#service_type_record_id', 'change', $.proxy(this.onServiceTypeChange, this));
        this.$el.delegate('#ship_via', 'change', $.proxy(this.onShipViaChange, this));
    },

    bindMessageBusEvents: function() {
        this.messageBus.onOrdersBeforeSearch(this.onOrdersBeforeSearch, this);
        this.messageBus.onCancelDespatch(this.onCancelDespatch, this);
        this.messageBus.onConnoteCancelRequest(this.onCancelDespatch, this);
        this.messageBus.onConnoteAcceptRequest(this.onAcceptConnote, this);
        this.messageBus.onOrdersSelectionChanged(this.onOrdersSelectionChanged, this);
        this.messageBus.onShowConnote(this.onShowConnote, this);
        this.messageBus.onScreenButtonConnote(this.onScreenButtonConnote, this); //todo: this should be served by checking strategy
        this.messageBus.onLinesBeforeLoad(this.onLinesBeforeLoad, this);
        this.messageBus.onLinesDespatchStatus(this.onLinesDespatchStatus, this);

        this.messageBus.onCheckingStatusChanged(this.onCheckingStatusChanged, this);

        this.messageBus.onCarrierConfirmed(this.onCarrierConfirmed, this);
        this.messageBus.onConfirmDespatchConfirmed(this.onAcceptConnote, this);

        this.messageBus.onQuickAcceptCarrierServiceRequest(this.onQuickAcceptCarrierServiceRequest, this);
        this.messageBus.onSetConsignmentRequest(this.onSetConsignmentRequest, this);
        this.messageBus.onSetConsignmentCarrierRequest(this.onSetConsignmentCarrierRequest, this);

        this.messageBus.onEdiOneOrderStatisticsChanged(this.onEdiOneOrderStatisticsChanged, this);
    },

    onEdiOneOrderStatisticsChanged: function(orderStatistics) {
        this.ssccTotals = orderStatistics.getPackSsccTotals();
        this.ssccAwbConsignmentNo = orderStatistics.getSsccConsignmentNo();
    },

    onSetConsignmentRequest: function(consignment) {
        this.$('#consignment').val(consignment);
    },

    onSetConsignmentCarrierRequest: function(carrierId) {
        var serviceType,
            dimensionalFactor = this.getCarrierDimensionalFactor(carrierId);

        //skipLabelPrinting = true; //todo: add skip label printing

        serviceType = this.getDefaultServiceTypeByCarrierId(carrierId).shift();

        if (serviceType) {
            this.messageBus.notifyQuickAcceptCarrierServiceRequest(serviceType);
        } else {
            Minder.Despatches.AwaitingChecking.PackDetails.setDimensionalFactor(dimensionalFactor);
        }
    },

    getPakIdDimensions: function() {
        var result = [];

        Minder.Despatches.AwaitingChecking.PackDetails.getAllRows().forEach(function(rowData){
            rowData.TOTAL_WT = rowData.CALCULATED.TOTAL_WT;
            rowData.VOL = rowData.CALCULATED.VOL;
            rowData.TOTAL_VOL = rowData.CALCULATED.TOTAL_VOL;
            rowData.CUBIC_WEIGHT = rowData.CALCULATED.CUBIC_WEIGHT;
            rowData.CHARGE_WEIGHT = rowData.CALCULATED.CHARGE_WEIGHT;

            rowData.DIMENSION_UOM = rowData.UOM.DT;
            rowData.PACK_WEIGHT_UOM = rowData.UOM.WT;
            rowData.VOLUME_UOM = rowData.UOM.VT;
            rowData.TOTAL_PACK_WEIGHT_UOM = rowData.UOM.TOTAL_WT;
            rowData.TOTAL_VOLUME_UOM = rowData.UOM.TOTAL_VT;

            result.push(rowData);
        });
        return result;
    },

    grabConnoteFormData: function() {
        var data = {};
        data.checkingStatus = this.checkingStatus;
        data.dimentions     = this.getPakIdDimensions();

        data.carrier        = this.$('#ship_via').val();
        data.carrier        = (data.carrier === null) || (typeof data.carrier === 'undefined') ? '' : data.carrier;

        data.carrierService = this.$('#service_type_record_id').val();
        data.carrierService = (data.carrierService === null) || (typeof data.carrierService === 'undefined') ? '' : data.carrierService;

        data.consignment    = this.$('#consignment').val();
        data.consignment    = (data.consignment === null) || (typeof data.consignment === 'undefined') ? '' : data.consignment;

        data.skipLabelPrinting = this.skipLabelPrinting || false;

        data.isEdiOrder         = this.isEdiOrder;
        data.ssccTotals         = this.ssccTotals;
        data.packTotalWeight    = Minder.Despatches.AwaitingChecking.PackDetails.getTotalWeight();
        return data;
    },

    getCarrierDimensionalFactor: function(carrierId) {
        return this.$('#ship_via').find('[carrier_id="' + carrierId + '"]').attr('dimensional_factor') || 0;
    },

    onCheckingStatusChanged: function(status) {
        this.checkingStatus = status;
    },

    onLinesBeforeLoad: function() {
        this.$el.html('');
    },

    onCancelDespatch: function() {
        this.$el.html('');
    },

    onOrdersSelectionChanged: function(orders) {
        this.ssccTotals = this._getSsccTotalDefaults();
        this.isEdiOrder = orders.isEdi();
        this.$el.html('');
    },

    onOrdersBeforeSearch: function() {
        this.$el.html('');
    },

    onLinesDespatchStatus: function(status) {
        this.linesErrors = [].concat(status.errors || [], status.warnings || []);
    },

    onScreenButtonConnote: function() {
        this.onShowConnote(''); //todo: set default accept url
    },

    onShowConnote: function(acceptUrl) {
        var data = this.loadData;
        this.acceptUrl = acceptUrl;
        this.$el.load(this.loadUrl, data, $.proxy(this.onShowConnoteCallback, this));
    },

    onShowConnoteCallback: function() {
        var orderShipVia = this.$('[name="order-ship-via"]').val(),
            dimensionalFactor = this.getCarrierDimensionalFactor(orderShipVia),
            $packDimensionsContainer = $('#pack_dimentions');
        Minder.Despatches.AwaitingChecking.PackDetails.init($packDimensionsContainer, this.dimensionUoms, this.ssccTotals);
        Minder.Despatches.AwaitingChecking.PackDetails.setDimensionalFactor(dimensionalFactor);

        this.messageBus.notifyOrderShipViaChanged(orderShipVia);
        this.filterCarrierServiceList();
        this.updateConsignmentNo();
    },

    doAccept: function() {
        $.post(
            this.acceptUrl,
            this.grabConnoteFormData(),
            $.proxy(this.doAcceptCallback, this),
            'json'
        );
    },

    doAcceptCallback: function(response) {
        $('#barcode').focus();
        showResponseMessages(response);

        if (response.errors && response.errors.length > 0) {
            return;
        }

        if (response.success) {
            this.messageBus.notifyConnoteAccepted();

            //Minder.Despatches.AwaitingChecking.SsccPackDetails.reset();
            //
            //if (hasSearchParams()) {
            //    executeSearch(getSearchParams());
            //} else {
            //    if (typeof loadOrders == 'function')
            //        loadOrders();
            //}
        }
    },

    onCarrierConfirmed: function() {
        this.doAccept();
    },

    validateAcceptConnoteData: function(data, result) {
        var hasZeroWeight = false, hasZeroVolume = false;
        result = result || new Mdr.ProcessResult();


        if (!this.checkingStatus.allItemsChecked) {

            alert('all lines not checked');
            result.addErrors(['Not all lines is checked.']);
        }

        if (data.isEdiOrder) {
            if (data.packTotalWeight < data.ssccTotals.WT) {
                result.addErrors(['Total weight should be ' + data.ssccTotals.WT + ' or greater']);
            }
        }

        if (data.dimentions.length < 1) {
            result.addErrors(['Please fill Pack Dimensions.']);
        } else {
            data.dimentions.forEach(function(dimension){
                if (dimension.TOTAL_WT.toFixed(4) < 0.0001) {
                    hasZeroWeight = true;
                }

                if (dimension.TOTAL_VOL.toFixed(4) < 0.0001) {
                    hasZeroVolume = true;
                }
            });

            if (weightRequired && hasZeroWeight) {
                result.addErrors(['Please fill weight information.']);
            }

            if (volumeRequired && hasZeroVolume) {
                result.addErrors(['Please fill volume information.']);
            }
        }
        return result;
    },

    onQuickAcceptCarrierServiceRequest: function(carrierServiceId) {
        this.setCarrierService(carrierServiceId, $.proxy(this.setCarrierCallback, this));
    },

    setCarrierCallback: function(carrierServiceId) {
        var dimensionalFactor = this.getCarrierDimensionalFactor(this.$('ship_via').val());
        Minder.Despatches.AwaitingChecking.PackDetails.setDimensionalFactor(dimensionalFactor);

        this.updateRadioButtonsState();
        this._accept(carrierServiceId);
    },

    onAcceptConnote: function() {
        this._accept('ACCEPT-BUTTON');
    },

    _accept: function(token) {
        var validateResult = this.validateAcceptConnoteData(this.grabConnoteFormData());

        showResponseMessages(validateResult);

        if (validateResult.hasErrors()) {
            return;
        }

        this.messageBus.notifyCarrierConfirmRequest(this.$('#ship_via').find('option:selected').val(), token);
    },

    onServiceTypeChange: function() {
        this.updateRadioButtonsState();
    },

    onShipViaChange: function(evt) {
        var dimensionalFactor = this.getCarrierDimensionalFactor($(evt.target).val());

        Minder.Despatches.AwaitingChecking.PackDetails.setDimensionalFactor(dimensionalFactor);
        this.filterCarrierServiceList();
        this.updateConsignmentNo();
    },

    setCarrierService: function(carrierServiceId, doneCallback) {
        var
            $serviceType = this.$('#service_type_record_id'),
            matchCarrier,
            totalWeight = packDimensionsGetTotalWeight(),
            matchRecordId = function(option) {return option.RECORD_ID == carrierServiceId},
            emptyDepot = function(option) {return !option.DEPOT_ID},
            underWeight = function(option) {return parseFloat(option.MIN_WEIGHT) > totalWeight},
            overWeight = function(option) {return parseFloat(option.MAX_WEIGHT) < totalWeight},
            validWeight = Mdr.notOr(underWeight, overWeight),
            carrierService;

        carrierService = $serviceType.minderFilteredDDGetFiltered(Mdr.and(matchRecordId, Mdr.or(emptyDepot, validWeight)));

        if (carrierService.length < 1) {
            return;
        }

        carrierService = carrierService.shift();
        this.$('#ship_via').val(carrierService.CARRIER_ID);
        matchCarrier = function(option) {return option.CARRIER_ID == carrierService.CARRIER_ID};
        $serviceType.minderFilteredDDSetFilter(Mdr.and(matchCarrier, Mdr.or(emptyDepot, validWeight))).done(function(){
            $serviceType.val(carrierServiceId);

            doneCallback(carrierServiceId);
        });
    },

    updateConsignmentNo: function() {
        var defaultConnoteIsso = this.$('#ship_via').find('option:selected').attr('default_connote_isso');

        if (defaultConnoteIsso == 'T') {
            this.$('#consignment').val(this.$('#first_selected_order').val());
        } else {
            this.$('#consignment').val(this.ssccAwbConsignmentNo);
        }
    },

    updateRadioButtonsState: function() {
        var $_serviceType = this.$('#service_type_record_id');
        if ($_serviceType.length < 1)
            return;

        var $_selectedOption = $_serviceType.find('option:selected');

        this.$('input:radio[name="service_signature_reqd"]').removeAttr('checked');
        this.$('input:radio[name="service_signature_reqd"][value="' + $_selectedOption.attr('service_signature_reqd') + '"]').attr('checked', 'checked');

        this.$('input:radio[name="service_multipart_consignment"]').removeAttr('checked');
        this.$('input:radio[name="service_multipart_consignment"][value="' + $_selectedOption.attr('service_multipart_consignment') + '"]').attr('checked', 'checked');

        this.$('input:radio[name="service_partial_delivery"]').removeAttr('checked');
        this.$('input:radio[name="service_partial_delivery"][value="' + $_selectedOption.attr('service_partial_delivery') + '"]').attr('checked', 'checked');

        this.$('input:radio[name="service_cash_to_collect"]').removeAttr('checked');
        this.$('input:radio[name="service_cash_to_collect"][value="' + $_selectedOption.attr('service_cash_to_collect') + '"]').attr('checked', 'checked');

        this.$('input:radio[name="service_transit_cover_reqd"]').removeAttr('checked');
        this.$('input:radio[name="service_transit_cover_reqd"][value="' + $_selectedOption.attr('service_transit_cover_reqd') + '"]').attr('checked', 'checked');

        this.$('input:text[name="service_transit_cover_amount"]').val('').val($_selectedOption.attr('service_transit_cover_amount'));
    },

    filterCarrierServiceList: function() {
        var
            $serviceTypeSelect = this.$('#service_type_record_id'),
            $selectedCarrier = this.$('#ship_via').find('option:selected'),
            totalWeight = packDimensionsGetTotalWeight(),
            selectedCarrier = function(option) {return option.CARRIER_ID == $selectedCarrier.val()},
            emptyDepot = function(option) {return !option.DEPOT_ID},
            underWeight = function(option) {return parseFloat(option.MIN_WEIGHT) > totalWeight},
            overWeight = function(option) {return parseFloat(option.MAX_WEIGHT) < totalWeight},
            validWeight = Mdr.notOr(underWeight, overWeight),
            filterHelper = Mdr.and(selectedCarrier, Mdr.or(emptyDepot, validWeight)),
            self = this;

        $serviceTypeSelect.minderFilteredDDSetFilter(filterHelper).done(function(){
            var
                defaultServiceTypeId = $selectedCarrier.attr('default_service_type'),
                tmpArr,
                validWeightServiceTypeId,
                emptyDepotServiceTypeId;
            if (defaultServiceTypeId) {
                $('#service_type_record_id').val(defaultServiceTypeId);
            } else {
                tmpArr = $serviceTypeSelect.minderFilteredDDGetFiltered(Mdr.and(selectedCarrier, Mdr.not(emptyDepot) , validWeight));
                validWeightServiceTypeId = tmpArr.length > 0 ? tmpArr.shift().RECORD_ID : null;

                if (validWeightServiceTypeId) {
                    $('#service_type_record_id').val(validWeightServiceTypeId);
                } else {
                    tmpArr = $serviceTypeSelect.minderFilteredDDGetFiltered(Mdr.and(selectedCarrier, emptyDepot));
                    emptyDepotServiceTypeId = tmpArr.length > 0 ? tmpArr.shift().RECORD_ID : null;

                    if (validWeightServiceTypeId) {
                        $('#service_type_record_id').val(emptyDepotServiceTypeId);
                    }
                }
            }

            self.updateRadioButtonsState();
        });
    },

    getDefaultServiceTypeByCarrierId: function(carrierId) {
        var
            matchCarrierId = function () {return $(this).attr('connote_param_id') === carrierId;},
            matchedCarrierOptions = $('#ship_via').find('option').filter(matchCarrierId),
            carriers = matchedCarrierOptions.map(Mdr.jqAttr('carrier_id')).get(),
            defaultServiceTypes = matchedCarrierOptions.map(Mdr.jqAttr('default_service_type')).get().filter(function(item){return !!item;}),
            matchCarriers = function(option) {return carriers.indexOf(option.CARRIER_ID) >= 0},
            matchDefaultServiceTypes = function(option) {return defaultServiceTypes.indexOf(option.RECORD_ID) >= 0},
            totalWeight = packDimensionsGetTotalWeight(),
            emptyDepot = function(option) {return !option.DEPOT_ID},
            underWeight = function(option) {return parseFloat(option.MIN_WEIGHT) > totalWeight},
            overWeight = function(option) {return parseFloat(option.MAX_WEIGHT) < totalWeight},
            notEmptyDepotAndValidWeight = Mdr.and(Mdr.not(emptyDepot), Mdr.notOr(underWeight, overWeight)),
            matchedCarrierServiceTypes = $('#service_type_record_id').minderFilteredDDGetFiltered(matchCarriers),
            matchedDefaultServiceTypes = matchedCarrierServiceTypes.filter(matchDefaultServiceTypes),
            result = matchedDefaultServiceTypes.filter(notEmptyDepotAndValidWeight);

        if (result.length < 1) {
            result = matchedCarrierServiceTypes.filter(notEmptyDepotAndValidWeight);
        }

        if (result.length < 1) {
            result = matchedDefaultServiceTypes.filter(emptyDepot);
        }

        if (result.length < 1) {
            result = matchedCarrierServiceTypes.filter(emptyDepot);
        }


        return result.map(function(option){ return option.RECORD_ID;});
    }
});
//todo: add quick accept button handler