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