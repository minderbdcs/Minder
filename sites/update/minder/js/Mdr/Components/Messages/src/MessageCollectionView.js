Messages.MessageCollectionView = (function(Backbone){
    return Backbone.View.extend({
        initialize: function() {
            this.collection = this.collection || new Messages.MessageCollection([]);

            this.listenTo(this.collection, 'add', this.onMessageAdd);
            this.listenTo(this.collection, 'remove', this.onMessageRemove);
            this.listenTo(this.collection, 'reset', this.onCollectionReset);
        },

        clearAll: function() {
            this.collection.reset([]);
        },

        addMessages: function(messages, timeout) {
            this.collection.add(messages.map(function(messageText){
                return {message: messageText}
            }), {timeout: timeout});
        },

        onMessageAdd: function(message) {
            var messageView = new Messages.MessageView({model: message});

            this.$el.show();
            this.$el.append(messageView.$el);
        },

        onCollectionReset: function(collection, options) {
            if (collection.length > 0) {
                this.$el.show();
            } else {
                this.$el.hide();
            }

            options.previousModels.forEach(function(message) {
                message.destroy();
            });
        },

        onMessageRemove: function() {
            if (this.collection.length < 1) {
                this.$el.hide();
            }
        }
    });
})(Backbone);