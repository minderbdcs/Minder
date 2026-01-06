Messages.MessageView = (function(Backbone, $){
    return Backbone.View.extend({
        initialize: function() {
            this.listenTo(this.model, 'destroy', this.onModelDestroy);
            this.render();
        },

        onModelDestroy: function() {
            this.$el.remove();
        },

        render: function() {
            this.$el = $('<li>' + this.model.get('message') + '</li>');
        }
    })
})(Backbone, jQuery);