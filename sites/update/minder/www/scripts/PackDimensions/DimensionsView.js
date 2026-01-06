var DimensionsView = Backbone.View.extend({
    packs: null,
    totals: null,
    dimensions: null,
    defaultDimensions: {},

    ui: {
        $dimensionUom: null,
        $weightUom: null
    },

    getDefaultDimensions: function() {
        return {
            DT: this.ui.$dimensionUom.find(':selected').val(),
            WT: this.ui.$weightUom.find(':selected').val()
        };
    },

    getPackDefaults: function() {
        var $sampleRow = this.$('.sample_row'),
            result = {},
            numericFields = ['L', 'W', 'H', 'QTY', 'VOL', 'TOTAL_VOL', 'TOTAL_WT'];

        function setDefault(name, value) {
            if (numericFields.indexOf(name) > -1) {
                result[name] = parseFloat(value) || 0;
            } else {
                result[name] = value;
            }
        }

        $sampleRow.find('input, select').each(function(){
            var $this = $(this);
            setDefault($this.attr('name'), $this.val());
        });

        $sampleRow.find('label').each(function(){
            var $this = $(this);
            setDefault($this.attr('for'), $this.text());
        });

        return result;
    },

    uoms: null,

    initialize: function(options) {
        this.ui = {
            $dimensionUom: this.$('[name="DIMENSION_UOM"]'),
            $weightUom: this.$('[name="PACK_WEIGHT_UOM"]')
        };

        this.uoms = new Uoms(options.uoms);
        this.defaultDimensions = this.getDefaultDimensions();
        this.dimensions = new Dimensions(this.defaultDimensions, {uoms: options.uoms});
        this.totals = new Totals();
        this.packs = new PackCollection([], {
            model: Pack.extend({
                defaults: _.defaults({}, this.getPackDefaults(), Pack.prototype.defaults)
            })
        });

        new PackCollectionView({
            collection: this.packs,
            el: this.$('.dimensions')
        });

        this.ui.$dimensionUom.change($.proxy(this.onDimensionUomChange, this));
        this.ui.$weightUom.change($.proxy(this.onWeightUomChange, this));
        this.listenTo(this.dimensions, 'change:DT', this.onDTChange);
        this.listenTo(this.dimensions, 'change:WT', this.onWTChange);
        this.listenTo(this.dimensions, 'change', this.onDimensionsChange);
        this.listenTo(this.packs, 'recalculate', this.onCalculatedChanged);
        this.listenTo(this.packs, 'change:TYPE', this.onPackTypeChanged);
        this.listenTo(this.packs, 'change:QTY', this.onPackQtyChanged);
        this.listenTo(this.packs, 'add', this.onPackAdded);
        this.listenTo(this.totals, 'change', this.onTotalsChanged);
    },

    onPackAdded: function(pack) {
        var qty = pack.get('QTY'),
            packs = _.clone(this.totals.get('PACKS')),
            current = pack.get('CALCULATED');

        packs.TOTAL = packs.TOTAL + qty;
        packs = this.increasePacks(packs, qty, pack.get('TYPE'));

        this.totals.set({
            'VOL': this.totals.get('VOL') + current.TOTAL_VOL,
            'WT' : this.totals.get('WT') + current.TOTAL_WT,
            'PACKS': packs
        });
    },

    onTotalsChanged: function(totals) {
        var
            packs = totals.get('PACKS');

        this.$('.total_weight').text(totals.get('WT').toFixed(4));
        this.$('.total_volume').text(totals.get('VOL').toFixed(4));
        this.$('.total_pallets').text(packs.PALLETS);
        this.$('.total_cartons').text(packs.CARTONS);
        this.$('.total_satchels').text(packs.SATCHELS);
        this.$('.total_packages').text(packs.TOTAL);
    },

    decreasePacks: function(packs, qty, type) {
        return this.increasePacks(packs, -qty, type)
    },

    increasePacks: function(packs, qty, type) {
        switch (type) {
            case 'C':
                packs.CARTONS += qty;
                break;
            case 'P':
                packs.PALLETS += qty;
                break;
            case 'S':
                packs.SATCHELS += qty;
                break;
        }

        return packs;
    },

    onPackQtyChanged: function(pack) {
        var qty = pack.get('QTY'),
            previousQty = pack.previous('QTY'),
            packs = _.clone(this.totals.get('PACKS'));

        packs.TOTAL = packs.TOTAL - previousQty + qty;
        packs = this.decreasePacks(packs, previousQty, pack.get('TYPE'));
        packs = this.increasePacks(packs, qty, pack.get('TYPE'));

        this.totals.set('PACKS', packs);
    },

    onPackTypeChanged: function(pack) {
        var packs = _.clone(this.totals.get('PACKS'));

        packs = this.decreasePacks(packs, pack.get('QTY'), pack.previous('TYPE'));
        packs = this.increasePacks(packs, pack.get('QTY'), pack.get('TYPE'));

        this.totals.set('PACKS', packs);
    },

    onCalculatedChanged: function(pack) {
        var previous = pack.previous('CALCULATED'),
            current = pack.get('CALCULATED');

        this.totals.set({
            'VOL': this.totals.get('VOL') - previous.TOTAL_VOL + current.TOTAL_VOL,
            'WT' : this.totals.get('WT') - previous.TOTAL_WT + current.TOTAL_WT
        });
    },

    onDimensionsChange: function(dimensions) {
        this.totals.set(this.packs.recalculate(dimensions.toJSON()));
    },

    onDimensionUomChange: function(evt) {
        this.dimensions.set('DT', $(evt.target).val());
    },

    onWeightUomChange: function(evt) {
        this.dimensions.set('WT', $(evt.target).val());
    },

    onDTChange: function(target, value) {
        this.ui.$dimensionUom.val(value);
    },

    onWTChange: function(target, value) {
        this.ui.$weightUom.val(value);
    },

    show: function() {
        this.$el.show();
    },

    hide: function() {
        this.$el.hide();
    },

    reset: function() {
        this.packs.remove(this.packs.models);
        this.totals.set({
            VOL: 0,
            WT: 0,
            PACKS: {
                PALLETS: 0,
                SATCHELS: 0,
                CARTONS: 0,
                TOTAL: 0
            }
        });
        this.dimensions.set(this.defaultDimensions);
    },

    commit: function(lock) {
        this.packs.commit(lock);
    },

    hasUncommitted: function() {
        return this.packs.hasUncommitted();
    },

    removeUncommitted: function() {
        return this.packs.remove(this.packs.getUncommitted());
    },

    getUncommittedData: function() {
        return this.packs.getUncommitted().map(function(pack){
            return pack.toJSON();
        });
    },

    getTotals: function() {
        return this.totals.toJSON();
    },

    getAllRows: function() {
        return this.packs.toJSON();
    },

    addPacks: function(packsData, options) {
        options = options || {};

        options.uoms = this.uoms;
        options.defaultDimensionUoms = this.dimensions.toJSON();
        options.parse = true;
        this.packs.add(packsData, options);
    },

    setDimensions: function(uoms) {
        if (uoms.hasOwnProperty('DT')) {
            if (!uoms.DT || uoms.DT == 'null') {
                delete uoms.DT;
            }
        }

        if (uoms.hasOwnProperty('WT')) {
            if (!uoms.WT || uoms.WT == 'null') {
                delete uoms.WT;
            }
        }

        this.dimensions.set(uoms);
    }
});