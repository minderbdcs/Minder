Mdr.Pages.AwaitingExit.PageView = (function(Backbone, AwaitingExit){
    return Backbone.View.extend({
        initialize: function(options){
            this._messageBus = new Mdr.Pages.AwaitingExit.MessageBus();

            new AwaitingExit.DespatchesView({
                el: this.$('#despatched_container'),
                messageBus: this._messageBus,
                despatchesNamespace: options.despatchesNamespace
            });
            new AwaitingExit.ChangeSentFromDialogView({
                el: this.$('#change-sent-from-dialog'),
                messageBus: this._messageBus,
                changeUrl: options.changeUrl,
                carrierService: options.carrierService
            });
        }
    });
})(Backbone, Mdr.Pages.AwaitingExit);