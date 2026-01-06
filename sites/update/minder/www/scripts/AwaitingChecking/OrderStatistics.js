var OrderStatistics = Backbone.Model.extend({
    defaults: {
        totalSscc : 0,
        cancelledSscc : 0,
        despatchedSscc: 0,
        checkedSscc : 0,
        uncheckedSscc : 0,
        inProgressSscc : 0,
        nextPrintedSscc : '',
        nextUnprintedSscc : '',

        totalPickItems : 0,
        despatchedItems : 0,
        readyForDespatchItems : 0,
        pickedQty : 0,
        checkedQty : 0,
        totalWeight: 0,
        totalVolume: 0,
        pallets: 0,
        satchels: 0,
        cartons: 0,

        awbConsignmentNo: ''
    },

    getSsccConsignmentNo: function() {
        return this.get('awbConsignmentNo');
    },

    getPackSsccTotals: function() {
        return {
            VOL: this.getTotalVolume(),
            WT: this.getTotalWeight(),
            PACKS: {
                PALLETS: this.getPalletsQty(),
                SATCHELS: this.getSatchelsQty(),
                CARTONS: this.getCartonsQty(),
                TOTAL: this.getCheckedQty()
            }
        }
    },

    getDespatchedSscc: function() {
        return parseInt(this.get('despatchedSscc')) || 0;
    },

    getCheckedQty: function() {
        return parseInt(this.get('checkedQty')) || 0;
    },

    getPickedQty: function() {
        return parseInt(this.get('pickedQty'));
    },

    getUncheckedQty: function() {
        return this.getPickedQty() - this.getCheckedQty();
    },

    getPalletsQty: function() {
        return parseInt(this.get('pallets')) || 0;
    },

    getSatchelsQty: function() {
        return parseInt(this.get('satchels')) || 0;
    },

    getCartonsQty: function() {
        return parseInt(this.get('cartons')) || 0;
    },

    getTotalWeight: function() {
        return parseFloat(this.get('totalWeight')) || 0;
    },

    getTotalVolume: function() {
        return parseFloat(this.get('totalVolume')) || 0;
    },

    addCheckedQty: function(diff) {
        this.set('checkedQty', this.getCheckedQty() + diff);
    },

    getUncheckedSscc: function() {
        return parseInt(this.get('uncheckedSscc'));
    },

    getInProgressSscc: function() {
        return parseInt(this.get('inProgressSscc'));
    },

    getCheckedSscc: function() {
        return parseInt(this.get('checkedSscc'));
    },

    getCheckingSscc: function() {
        return this.getUncheckedSscc() + this.getInProgressSscc();
    },

    canBeDespatched: function() {
        return this.getCheckedQty() > 0
            && this.getUncheckedQty() < 1
            && this.getCheckedSscc() > 0
            && this.getCheckingSscc() < 1;
    },

    hasPrintedSscc: function() {
        return !!this.get('nextPrintedSscc');
    },

    getNextPrintedSscc: function() {
        return this.get('nextPrintedSscc');
    }
});