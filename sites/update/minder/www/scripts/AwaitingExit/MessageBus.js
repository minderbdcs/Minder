/**
* Auto-generated file. Do not edit manually. Modify source instead and use grunt mustache_render task to rebuild.
*
* @source "js/events/awaiting-exit.json"
*/
Mdr.Pages.AwaitingExit.MessageBus = (function(){

    var MessageBus = function() {};

    _.extend(
        MessageBus.prototype,
        Backbone.Events,
        {
            notifyChangeSentFromRequest: function() {
                this.trigger('change-sent-from-request');
            },

            onChangeSentFromRequest: function(callback, object) {
                object.listenTo(this, 'change-sent-from-request', callback);
            },

            notifyDespatchedRowsSelectionChanged: function(sysScreenSelectionState) {
                this.trigger('despatched-rows-selection-changed', sysScreenSelectionState);
            },

            onDespatchedRowsSelectionChanged: function(callback, object) {
                object.listenTo(this, 'despatched-rows-selection-changed', callback);
            },

            notifyDespatchedDataReady: function(sysScreenData) {
                this.trigger('despatched-data-ready', sysScreenData);
            },

            onDespatchedDataReady: function(callback, object) {
                object.listenTo(this, 'despatched-data-ready', callback);
            },

            notifyReloadDataRequest: function() {
                this.trigger('reload-data-request');
            },

            onReloadDataRequest: function(callback, object) {
                object.listenTo(this, 'reload-data-request', callback);
            }

        }
    );

    return MessageBus;
})();
