var PrintPickBlockDialogView = (function() {
    var
        DEFAULT_LABEL_AMOUNT    = 0,
        DEFAULT_LABEL_LIMIT     = 99;

    return DialogView.extend({
        initialize: function(options) {
            options = _.extend({labelLimit: DEFAULT_LABEL_LIMIT}, options);

            this.labelAmount = DEFAULT_LABEL_AMOUNT;
            this.labelLimit = options.labelLimit || DEFAULT_LABEL_LIMIT;
            this.printUrl   = options.printUrl || '';

            this.listenTo(this, 'on-before-init-ui', this.onBeforeInitUi);
            this.listenTo(this, 'dialog:before-open', this.onBeforeOpen);
        },

        onBeforeOpen: function() {
            this.labelAmount = DEFAULT_LABEL_AMOUNT;
            this.$('[name="label-amount"]').val(this.labelAmount);
        },

        onBeforeInitUi: function(options) {
            options.buttons = {
                Print: $.proxy(this.onPrintButtonClick, this),
                'HTML': true,
                CANCEL: $.proxy(this.onCancelButtonClick, this)
            };

            this.$el.unbind('print-pick-block-request').bind('print-pick-block-request', $.proxy(this.onPrintPickBlockRequest, this));
            this.$('.max-label-amount').text(this.labelLimit);
            this.$('[name="label-amount"]').change($.proxy(this.onLabelAmountChange, this));
        },

        onLabelAmountChange: function(evt) {
            var $target = $(evt.target);

            this.labelAmount = parseInt($target.val()) || this.labelAmount;
            $target.val(this.labelAmount);
        },

        onPrintButtonClick: function() {
            if (this.labelAmount > this.labelLimit) {
                showErrors(['Label amount cannot be greater then ' + this.labelLimit]);
            } else if (this.labelAmount < 1) {
                showErrors(['Please, specify label amount.']);
            } else {
                $.post(this.printUrl, {labelAmount: this.labelAmount}, $.proxy(this.printLabelCallback, this), 'json');
                this.closeDialog();
            }
        },

        printLabelCallback: function(response) {
            showResponseMessages(response);
        },

        onCancelButtonClick: function() {
            this.closeDialog();
        },

        onPrintPickBlockRequest: function() {
            this.openDialog();
        }
    });
})();

