Mdr.Pages.AwaitingExit.DespatchesView = (function(Backbone){
    return Backbone.View.extend({
        initialize: function(options) {
            this._messageBus = options.messageBus;
            this.despatchesNamespace = options.despatchesNamespace;
            this.$el.bind('change-sent-from-request', $.proxy(this._onDomChangeSentFromRequest, this));
            this.$el.bind('after-render', $.proxy(this._onDomAfterRender, this));
            this.$el.bind('screen-selection-changed', $.proxy(this._onDomScreenSelectionChanged, this));
            this._messageBus.onReloadDataRequest(this.onReloadDataRequest, this);
        },

        onReloadDataRequest: function() {
            loadData(this.despatchesNamespace);
        },

        _onDomScreenSelectionChanged: function(evt) {
            this._messageBus.notifyDespatchedRowsSelectionChanged(evt.sysScreenSelectionState);
        },

        _onDomAfterRender: function(evt) {
            this._messageBus.notifyDespatchedDataReady(evt.sysScreen);
        },

        _onDomChangeSentFromRequest: function(evt) {
            this._messageBus.notifyChangeSentFromRequest();
        }
    });
})(Backbone);