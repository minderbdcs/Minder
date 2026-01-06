var Dimensions = Backbone.Model.extend({
    defaults: {
        DT: '',
        WT: '',
        VT: '',
        DTtoVT: '',
        TOTAL_WT: '',
        TOTAL_VT: ''
    },

    initialize: function(attributes, options) {
        this.set({
            'VT': options.uoms.VT.CODE,
            'DTtoVT': options.uoms.DTtoVT.CODE,
            'TOTAL_VT': options.uoms.VT.CODE,
            'TOTAL_WT': options.uoms.WT.CODE
        });
    }
});