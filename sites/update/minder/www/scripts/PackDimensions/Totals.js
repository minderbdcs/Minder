var Totals = Backbone.Model.extend({
    defaults: {
        VOL: 0,
        WT: 0,
        PACKS: {
            PALLETS: 0,
            SATCHELS: 0,
            CARTONS: 0,
            TOTAL: 0
        }
    }
});