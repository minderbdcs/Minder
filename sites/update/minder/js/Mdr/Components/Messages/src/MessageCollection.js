Messages.MessageCollection = (function(Backbone){
    return Backbone.Collection.extend({
        model: Messages.Message,

        initialize: function() {
            this.listenTo(this, 'change:expired', this.onExpiredChanged);
        },

        onExpiredChanged: function(message, expired) {
            if (expired) {
                this.remove(message);
                message.destroy();
            }
        }
    });
})(Backbone);