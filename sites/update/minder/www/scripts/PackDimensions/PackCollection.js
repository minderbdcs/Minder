var PackCollection = Backbone.Collection.extend({
    model: Pack,

    commit: function(lock) {
        this.models.forEach(function(pack){
            pack.commit(lock);
        });
    },

    recalculate: function(uoms) {
        var result = {
            VOL: 0,
            WT:0
        };

        this.each(function(pack){
            var calculated = pack.recalculate(uoms, true);

            result.VOL += calculated.TOTAL_VOL;
            result.WT += calculated.TOTAL_WT;
        });

        return result;
    },

    hasUncommitted: function() {
        var pack = this.find(function(pack) {
            return pack.isNew();
        });

        return !!pack;
    },

    getUncommitted: function() {
        return this.filter(function(pack){
            return pack.isNew();
        });
    }
});