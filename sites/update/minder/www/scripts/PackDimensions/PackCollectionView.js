var PackCollectionView = Backbone.View.extend({
    $rowTemplate: null,

    initialize: function() {
        this.$rowTemplate = this.$('.sample_row');
        this.listenTo(this.collection, 'add', this.onPackAdded);
    },

    onPackAdded: function(pack) {
        var packView = new PackView({
            model: pack,
            $template: this.$rowTemplate
        });

        this.$el.append(packView.$el);
    }
});