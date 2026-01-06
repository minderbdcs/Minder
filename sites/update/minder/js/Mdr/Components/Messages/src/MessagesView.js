Messages.MessagesView = (function(Backbone, $){
    return Backbone.View.extend({
        initialize: function() {
            this.errors = new Messages.MessageCollectionView({
                el: this._getContainer('mdr-errors')
            });

            this.warnings = new Messages.MessageCollectionView({
                el: this._getContainer('mdr-warnings')
            });

            this.messages = new Messages.MessageCollectionView({
                el: this._getContainer('mdr-messages')
            });
        },

        showResponseMessages: function(response, timeout) {
            timeout = timeout || null;

            if (response.errors) {
                this.showErrors(response.errors, timeout);
            }

            if (response.warnings) {
                this.showWarnings(response.warnings, timeout);
            }

            if (response.messages) {
                this.showMessages(response.messages, timeout);
            }
        },

        showErrors: function(errors, timeout) {
            this.errors.addMessages(errors, timeout);
        },

        showWarnings: function(warnings, timeout) {
            this.warnings.addMessages(warnings, timeout);
        },

        showMessages: function(messages, timeout) {
            this.messages.addMessages(messages, timeout);
        },

        clearAll: function() {
            this.messages.clearAll();
            this.warnings.clearAll();
            this.errors.clearAll();
        },

        _getContainer: function(type) {
            var result = this.$('.' + type);

            if (result.length < 1) {
                result = $('<ul class="' + type + ' hidden" style="display: none"></ul>');
                this.$el.prepend(result);
            }

            return result;
        }
    });
})(Backbone, jQuery);