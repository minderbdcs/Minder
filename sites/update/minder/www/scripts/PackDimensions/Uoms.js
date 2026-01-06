var Uoms = Backbone.Model.extend({
    defaults: {
        'uom' : {},
        'DTtoVT' : {
            'CODE': '',
            'DESCRIPTION': '',
            'UOM_TYPE': '',
            'TO_STANDARD_CONV': 1
        },
        'VT' : {
            'CODE': '',
            'DESCRIPTION': '',
            'UOM_TYPE': '',
            'TO_STANDARD_CONV': 1
        },
        'WT' : {
            'CODE': '',
            'DESCRIPTION': '',
            'UOM_TYPE': '',
            'TO_STANDARD_CONV': 1
        },
        'DEFAULT_DT' : {
            'CODE': '',
            'DESCRIPTION': '',
            'UOM_TYPE': '',
            'TO_STANDARD_CONV': 1
        },
        'DEFAULT_WT' : {
            'CODE': '',
            'DESCRIPTION': '',
            'UOM_TYPE': '',
            'TO_STANDARD_CONV': 1
        }
    },

    dtFactors: {

    },

    wtFactors: {

    },

    initialize: function(attributes) {
        if (!attributes.DEFAULT_DT) {
            showErrors(['Standard "DT" UOM was not found in UOM_TYPE table. UOM conversion is not possible. Check system setup.']);
        }

        if (!attributes.DEFAULT_WT) {
            showErrors(['Standard "WT" UOM was not found in UOM_TYPE table. UOM conversion is not possible. Check system setup.']);
        }
    },

    getDtFactor: function(uomCode) {
        if (!this.dtFactors.hasOwnProperty(uomCode)) {
            this.dtFactors[uomCode] = parseFloat(this.get('DTtoVT').TO_STANDARD_CONV) / parseFloat((this.get('uom'))[uomCode].TO_STANDARD_CONV);
        }

        return this.dtFactors[uomCode];
    },

    getWtFactor: function(uomCode) {
        if (!this.wtFactors.hasOwnProperty(uomCode)) {
            this.wtFactors[uomCode] = parseFloat(this.get('WT').TO_STANDARD_CONV) / parseFloat((this.get('uom'))[uomCode].TO_STANDARD_CONV);
        }
        return this.wtFactors[uomCode];
    }
});