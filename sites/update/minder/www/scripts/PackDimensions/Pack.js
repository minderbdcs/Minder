var Pack = Backbone.Model.extend({
    defaults: {
        PALLET_OWNER: 'NONE',
        TYPE: '',
        QTY: 1,
        L: 0,
        W: 0,
        H: 0,
        WT: 0,
        UOM: {
        },
        CALCULATED: {
            VOL: 0,
            TOTAL_VOL: 0,
            TOTAL_WT:0
        },
        EXPLAIN: {
            VOL: '',
            TOTAL_VOL: '',
            TOTAL_WT: ''
        },
        RAW: {

        }
    },

    locked: false,
    uoms: null,

    commit: function(lock) {
        this.locked = this.locked || !!lock;
        this.set({
            id: this.cid
        });
    },

    recalculate: function(dimensionUoms, silent) {
        var calculated = _.clone(this.get('CALCULATED')),
            dtFactor = this.uoms.getDtFactor(dimensionUoms.DT),
            wtFactor = this.uoms.getWtFactor(dimensionUoms.WT);

        if (this.locked) {
            return calculated;
        }

        calculated.VOL = this.get('L') * this.get('W') * this.get('H') * dtFactor * dtFactor * dtFactor;
        calculated.TOTAL_VOL = calculated.VOL * this.get('QTY');
        calculated.TOTAL_WT = this.get('WT') * this.get('QTY') * wtFactor;

        this.set('UOM', dimensionUoms);
        this.set('CALCULATED', calculated);

        silent = (silent === undefined) ? false : !!silent;
        if (!silent) {
            this.trigger('recalculate', this, calculated);
        }

        return calculated;
    },

    setRaw: function(name, value) {
        var raw = this.get('RAW');
        raw[name] = value;
        this.set('RAW', raw);
    },

    parse: function(response) {
        if (response.hasOwnProperty('QTY')) {
            response.QTY = parseInt(response.QTY) || 0;
        }

        if (response.hasOwnProperty('L')) {
            response.L = parseFloat(response.L) || 0;
        }

        if (response.hasOwnProperty('W')) {
            response.W = parseFloat(response.W) || 0;
        }

        if (response.hasOwnProperty('H')) {
            response.H = parseFloat(response.H) || 0;
        }

        if (response.hasOwnProperty('WT')) {
            response.WT = parseFloat(response.WT) || 0;
        }

        if (response.hasOwnProperty('VOL')) {
            response.VOL = parseFloat(response.VOL) || 0;
        }

        return response;
    },

    initialize: function(attributes, options) {
        var dimensionUoms = _.defaults({}, this.get('UOM'), options.defaultDimensionUoms);

        this.uoms = options.uoms;

        this.listenTo(this, 'change:L', this.onLengthChanged);
        this.listenTo(this, 'change:W', this.onWidthChanged);
        this.listenTo(this, 'change:H', this.onHeightChanged);
        this.listenTo(this, 'change:WT', this.onWeightChanged);
        this.listenTo(this, 'change:QTY', this.onQuantityChanged);

        this.recalculate(dimensionUoms);

        if (options.commit) {
            this.commit(options.lockOnCommit);
        }
    },

    onLengthChanged: function(model, value) {
        this.setRaw('L', value);
        this.set('L', parseFloat(value) || 0, {silent: true});
        this.recalculate(this.get('UOM'));
    },

    onWidthChanged: function(model, value) {
        this.setRaw('W', value);
        this.set('W', parseFloat(value) || 0, {silent: true});
        this.recalculate(this.get('UOM'));
    },

    onHeightChanged: function(model, value) {
        this.setRaw('H', value);
        this.set('H', parseFloat(value) || 0, {silent: true});
        this.recalculate(this.get('UOM'));
    },

    onWeightChanged: function(model, value) {
        this.setRaw('WH', value);
        this.set('WT', parseFloat(value) || 0, {silent: true});
        this.recalculate(this.get('UOM'));
    },

    onQuantityChanged: function(model, value) {
        this.setRaw('QTY', value);
        this.set('QTY', parseInt(value) || 0, {silent: true});
        this.recalculate(this.get('UOM'));
    }
});
