var SearchOrderByProdIdDialogView = (function(){

    var internalMessageBus = _.extend({}, Backbone.Events);

    var Order = Backbone.Model.extend({
        defaults: {
            order: '',
            selected: ''
        }
    });

    var OrderView = Backbone.View.extend({
        template: _.template('<tr><td><input type="radio" name="order" value="<%= order %>"></td><td><%= order %></td></tr>'),

        initialize: function() {
            this.render();
            this.listenTo(this.model, 'remove', this.onModelRemove);
        },

        onModelRemove: function() {
            this.remove();
        },

        render: function() {
            this.$el.remove();
            this.$el = $(this.template(this.model.attributes));

            if (this.model.get('selected')) {
                this.$('input').attr('checked', 'checked');
            }
        }
    });

    var OrderCollection = Backbone.Collection.extend({
        model: Order
    });


    return Backbone.View.extend({
        messageBus: null,
        active: false,
        scannedProducts: [],
        searchUrl: '',
        selectedOrder: null,

        initialize: function(options) {
            this.collection = new OrderCollection();
            this.messageBus = options.messageBus;
            this.searchUrl = options.searchUrl;

            this.initDialog();
            this.bindMessageBusEvents();
            this.listenTo(this.collection, 'add', this.onOrderAdded)
        },

        initDialog: function() {
            var $searchOrderByProductDialog = this.$el,
                $ordersList = $searchOrderByProductDialog.find('.order_list'),
                buttonPane;

            $searchOrderByProductDialog.dialog(
                {
                    buttons: {
                        SELECT: $.proxy(this.onSelectButtonClick, this),
                        'HTML': false,
                        CANCEL: $.proxy(this.onCancelButtonClick, this)
                    },
                    buttonStyle: 'green-button',
                    insertHtml:  '&nbsp;',
                    autoOpen  : false,
                    width     : 400,
                    height    : 260,
                    resizable : false,
                    modal     : false
                }
            ).unbind('dialogopen').bind('dialogopen', $.proxy(this.onDialogOpen, this))
            .unbind('dialogclose').bind('dialogclose', $.proxy(this.onDialogClose, this));

            buttonPane = $searchOrderByProductDialog.parents('.ui-dialog').find('.ui-dialog-buttonpane');
            buttonPane.find('::nth-child(2)').css('height', 'auto'); //workaround to remove unnecessary height property

            this.$el.delegate('[name="order"]', 'click', $.proxy(this.onOrderClick, this));
            this.$('.order_list').bind('remove', function(evt) {
                evt.stopPropagation();
                return false;
            });
        },

        onOrderClick: function(evt) {
            this.selectedOrder = $(evt.currentTarget).val();
            $('#barcode').focus();
        },

        onOrderAdded: function(order) {
            var orderView = new OrderView({model: order});

            this.$('.order_list').append(orderView.$el);
        },

        bindMessageBusEvents: function() {
            this.messageBus.onSearchOrderByProdId(this.onSearchOrderByProdId, this);
            this.messageBus.onScreenButtonSelect(this.onScreenButtonSelect, this);
            this.messageBus.onScreenButtonCancel(this.onScreenButtonCancel, this);
        },

        onScreenButtonSelect: function() {
            this.doSelect();
        },

        onSelectButtonClick: function(evt) {
            evt.preventDefault();
            this.doSelect();
        },

        doSelect: function() {
            if (this.active) {
                this.messageBus.notifySearchOrderByProdIdDialogSelectOrder(this.selectedOrder, this.scannedProducts);
                this.$el.dialog('close');
            }
        },

        onScreenButtonCancel: function() {
            if (this.active) {
                this.messageBus.notifyScreenButtonServed();
                this.$el.dialog('close');
            }
        },

        onCancelButtonClick: function(evt) {
            evt.preventDefault();

            if (this.active) {
                this.$el.dialog('close');
            }
        },

        onDialogOpen: function() {
            $('#barcode').focus();
            this.$el.show();
            this.active = true;
            this.messageBus.notifySearchOrderByProdIdDialogOpen();
        },

        onDialogClose: function() {
            this.active = false;
            this.messageBus.notifySearchOrderByProdIdDialogClose();
        },

        reset: function() {
            this.collection.remove(this.collection.models);
            this.scannedProducts = [];
            this.selectedOrder = null;
            this.$('.scanned_products').html('');
        },

        doSearch: function(prodId) {
            if (this.scannedProducts.indexOf(prodId) < 0) {
                this.scannedProducts.push(prodId);
                this.$('.scanned_products').html(this.scannedProducts.join(', '));
            }

            this.collection.remove(this.collection.models);

            $.post(
                this.searchUrl,
                {scannedProducts: this.scannedProducts},
                $.proxy(this.doSearchCallback, this),
                'json'
            );

        },

        doSearchCallback: function(response) {
            var selectedOrder = this.selectedOrder;
            this.collection.add(response.orders.map(function(order){
                return {
                    'order': order,
                    'selected': (order === selectedOrder)

                }
            }));
        },

        onSearchOrderByProdId: function(prodId) {
            if (!this.active) {
                this.reset();
                this.$el.dialog('open');
            }

            if (prodId) {
                this.doSearch(prodId);
            }
        }
    });
})();


