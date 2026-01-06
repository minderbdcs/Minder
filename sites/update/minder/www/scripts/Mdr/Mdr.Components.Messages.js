(function(root, factory) {

    if (typeof define === 'function' && define.amd) {
        define(['Mdr/Components', 'backbone'], function(Components, Backbone) {
            return factory(Components, Backbone);
        });
    } else if (typeof exports !== 'undefined') {
        var Components = require('Mdr/Components'),
            Backbone = require('backbone');
        module.exports = factory(Components, Backbone);
    } else {
        factory(root.Mdr.Components, root.Backbone);
    }

})(this, function(Components, Backbone){
    "use strict";

    var Messages = Components.Messages = {};

    Messages.Message = (function(Backbone, $) {
        var DEFAULT_TIMEOUT = 7000;
    
        return Backbone.Model.extend({
            defaults: {
                message: '',
                expired: false
            },
    
            initialize: function(attributes, options) {
                setTimeout($.proxy(this.onTimeout, this), options.timeout || DEFAULT_TIMEOUT);
            },
    
            onTimeout: function() {
                this.set('expired', true);
            }
        });
    })(Backbone, jQuery);
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

    return Components.Messages;
});