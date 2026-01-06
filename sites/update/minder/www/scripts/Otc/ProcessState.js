var CostCenter = function(costCenter) {
    this.id = costCenter.id || '';
    this. description = costCenter.description || '';
    this.via = costCenter.via || 'S';
    this.existed = !!costCenter.existed;
};

var Location = function(location) {
    this.location = location.location || '';
    this.description = location.description || '';
    this.displayedId = location.displayedId || location.location || '';
    this.via = location.via || 'S';
    this.existed = !!location.existed;
    this.opened = !!location.opened;

    this.isSet = !!location.isSet;
    this.loanedTotal = location.loanedTotal || '0';
    this.loanedTotalDescription = location.loanedTotalDescription || '';
    this.overdue = !!location.overdue;
};

var CheckPeriod = function(checkPeriod) {
    this.applicable          = !!checkPeriod.applicable;
    this.expireDate          = checkPeriod.expireDate || '';
    this.expired             = !!checkPeriod.expired;
    this.refuseIfExpired     = !!checkPeriod.refuseIfExpired;

};

var Item = function(item) {
    this.id = item.id || '';
    this.description = item.description || '';
    this.via = item.via || 'S';

    this.homeWhId = item.homeWhId || '';
    this.locnName = item.locnName || '';

    this.existed = !!item.existed;
    this.images = item.images;
    this.scannedItemType = item.scannedItemType || '';
    this.itemType = item.itemType || '';
    this.defaultIssueQty = item.defaultIssueQty || 1;
    this.defaultIssueUom = item.defaultIssueUom || 'EA';
    this.homeLocation = item.homeLocation || '';
    this.onLoan = !!item.onLoan;
    this.onLoanAt = item.onLoanAt || '';
    this.loanedTo = item.loanedTo || '';
    this.transferConfirmed = !!item.transferConfirmed;
    this.whId = item.whId || '';
    this.locnId = item.locnId || '';
    this.safetyTestPeriod = new CheckPeriod(item.safetyTestPeriod || {});
    this.calibratePeriod = new CheckPeriod(item.calibratePeriod || {});
    this.inspectionPeriod = new CheckPeriod(item.inspectionPeriod || {});
    this.expirationConfirmed = !!item.expirationConfirmed;
    this.legacyId = !!item.legacyId;
};

var ToolTransaction = function(transaction) {
    this.descriptionLabel = transaction.descriptionLabel || '';
    this.prefix = transaction.prefix || '';
    this.reference = transaction.reference || '';
    this.type = transaction.type || '';
    this.message = transaction.message || '';
    this.executed = !!transaction.executed;
    this.error = !!transaction.error;
};

var HistoryCollectionRow = Backbone.Model.extend({

});

var HistoryCollection = Backbone.Collection.extend({
    model: HistoryCollectionRow
});

var ProcessState = Backbone.Model.extend({

    serviceUrlList :{
        setCostCenter: ''
    },

    defaults: {
        chargeTo: new CostCenter({}),
        returnTo: new Location({}),
        returnFrom: new Location({}),
        auditLocation: new Location({}),

        issueTo: new Location({}),
        item: new Item({}),
        qty: {},
        loaned: {},

        committed: false,

        issueQty    : '',
        issueQtyVia : '',
        issueQtyDescription : '',

        transactionType : '',
        transactionMessage : '',
        canChangeToolReturnLocation : false,

        toolTransaction: new ToolTransaction({}),

        expectedQty: 0,

        checkedIssnList: [],

        processId: ''
    },

    initialize: function(attributes, options) {
        this.history = this.history || new HistoryCollection();
        this.serviceUrlList = options.serviceUrlList || this.serviceUrlList;
        this.defaultCostCenter = options.defaultCostCenter || '';
        this.defaultReturnLocation = options.defaultReturnLocation || '';
    },

    getProcessId: function() {
        return this.get('processId');
    },

    setCostCenter: function(costCenterId, via) {
        this.set('chargeTo', new CostCenter({'id': costCenterId, 'description': '', 'via': via}));
        this.set('committed', false);

        $.post(
            this.serviceUrlList.setCostCenter,
            {'costCenterId' : costCenterId, 'via': via, 'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    setIssueToBorrower: function(borrowerId, via) {
        this.set({
            'issueTo': new Location({'location': borrowerId, 'description': '', 'via': via}),
            'committed': false
        });

        $.post(
            this.serviceUrlList.setBorrower,
            {'borrowerId' : borrowerId, 'via': via, 'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    setReturnFrom: function(borrowerId, via) {
        this.set({
            'returnFrom': new Location({'location': borrowerId, 'description': '', 'via': via}),
            'item': new Item({}),
            'committed': false
        });

        $.post(
            this.serviceUrlList.setBorrower,
            {'borrowerId' : borrowerId, 'via': via, 'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    setReturnTo: function(locationId, via) {
        this.set({
            'returnTo': new Location({'location': locationId, 'description': '', 'via': via}),
            'item': new Item({}),
            'returnFrom': new Location({}),
            'committed': false
        });

        $.post(
            this.serviceUrlList.setLocation,
            {'location' : locationId, 'via': via, 'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    setIssueToLocation: function(locationId, via) {
        this.set({
            'issueTo': new Location({'location': locationId, 'description': '', 'via': via}),
            'committed': false
        });

        $.post(
            this.serviceUrlList.setLocation,
            {'location' : locationId, 'via': via, 'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    setAuditLocation: function(locationId, via) {
        this.set({
            'auditLocation': new Location({'location': locationId, 'description': '', 'via': via}),
            'expectedQty': 0,
            'checkedIssnList': [], 'committed': false
        });

        $.post(
            this.serviceUrlList.setLocation,
            {'location' : locationId, 'via': via, 'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    setTool: function(toolId, via) {
        this.set('item', new Item({'id': toolId, 'description': '', 'via': via}));
        this.set('committed', false);
        this.set('toolTransaction', new ToolTransaction({}));

        $.post(
            this.serviceUrlList.setTool,
            {'itemId' : toolId, 'via': via, 'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    setToolAltBarcode: function(toolAltBarcode, via) {
        this.set('item', new Item({'id': toolAltBarcode, 'description': '', 'via': via, legacyId: true}));
        this.set('committed', false);
        this.set('toolTransaction', new ToolTransaction({}));

        $.post(
            this.serviceUrlList.setToolAltBarcode,
            {'itemId' : toolAltBarcode, 'via': via, 'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    setConsumable: function(consumableId, via) {
        this.set('item', new Item({'id': consumableId, 'description': '', 'via': via}));
        this.set('committed', false);
        this.set('toolTransaction', new ToolTransaction({}));

        $.post(
            this.serviceUrlList.setConsumable,
            {'itemId' : consumableId, 'via': via, 'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    setIssueQty: function(issueQty, via) {
        this.set({'issueQty': issueQty, 'issueQtyVia': via, 'issueQtyDescription': ''});
        this.set('committed', false);

        $.post(
            this.serviceUrlList.setIssueQty,
            {'qty' : issueQty, 'via': via, 'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    executeToolTransaction: function(descriptionLabel) {
        this.set('toolTransaction', new ToolTransaction({}));

        $.post(
            this.serviceUrlList.executeToolTransaction,
            {'descriptionLabel' : descriptionLabel, 'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    confirmExpiration: function() {
        var item = new Item(this.get('item'));
        item.expirationConfirmed = true;

        this.set('item', item);
        this.set('committed', false);
        $.post(
            this.serviceUrlList.confirmExpiration,
            {'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    confirmTransfer: function() {
        var item = new Item(this.get('item'));
        item.transferConfirmed = true;
        this.set('committed', false);

        this.set('item', item);
        $.post(
            this.serviceUrlList.confirmTransfer,
            {'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    endAudit: function() {
        this.set({
            item: new Item({}),
            committed: false
        });
        $.post(
            this.serviceUrlList.endAudit,
            {'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    reloadTool: function() {
        $.post(
            this.serviceUrlList.reloadTool,
            {'tab': this.getProcessId()},
            $.proxy(this.onStateChanged, this),
            'json'
        );
    },

    saveState: function() {
        this.set('committed', false);
        $.post("/minder/otc/save", {'tab': this.getProcessId()}, $.proxy(this.onStateChanged, this), 'json');
    },

    resetState: function() {
        var attributes = _.clone(this.defaults);
        attributes.processId = this.getProcessId();
        attributes.chargeTo = new CostCenter({id: this.defaultCostCenter});
        attributes.returnTo = new Location({location: this.defaultReturnLocation});

        this.set(attributes);

        $.post("/minder/otc/cancel", {'tab': this.get('processId')}, $.proxy(this.onStateChanged, this), 'json');
    },

    recordHome: function() {
        this.set('committed', false);
        $.post("/minder/otc/record-home", {'tab': this.get('processId')}, $.proxy(this.onStateChanged, this), 'json');
    },

    onStateChanged: function(response) {
        this.set(this.parse(response));
    },

    parse: function(attributes) {
        var result              = attributes;
        result.chargeTo         = new CostCenter(attributes.chargeTo || {});
        result.returnTo         = new Location(attributes.returnTo || {});
        result.returnFrom       = new Location(attributes.returnFrom || {});
        result.auditLocation    = new Location(attributes.auditLocation || {});
        result.issueTo          = new Location(attributes.issueTo || {});
        result.item             = new Item(attributes.item || {});
        result.toolTransaction  = new ToolTransaction(attributes.toolTransaction || {});
        result.qty              = attributes.qty || {};
        result.loaned           = attributes.loaned || {};

        if (typeof result.save != 'undefined' && result.save != null) {
            this.history = this.history || new HistoryCollection();

            //todo: move some where else when have more time
            for (var iterator = 0; iterator < result.save.length; iterator++) {
                if (result.save[iterator].transactionMessage == 'Processed successfully') {
                    this.history.add(result.save[iterator]);
                } else {
                    showErrors([result.save[iterator].transactionMessage]);
                }
            }

            delete result.save;
        }

        return result;
    }
});
