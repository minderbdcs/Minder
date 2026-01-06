var ToolTransactionAwareMixin = {
    onDescriptionBarcode: function(descriptionLabel) {
        if (this.active) {
            this.messageBus.notifyLogRequest('Description label: ' + descriptionLabel + '. Tab: ' + this.model.getProcessId() + '. Via: B');
            this.model.executeToolTransaction(descriptionLabel);
        }
    },

    onToolTransactionChanged: function(state, newToolTransaction) {
        var $toolTransactionMessage = this.$('.tool_transaction_message');

        $toolTransactionMessage.removeClass('error');
        $toolTransactionMessage.removeClass('success');

        if (newToolTransaction.executed) {
            $toolTransactionMessage.val(newToolTransaction.message);

            if (newToolTransaction.error) {
                $toolTransactionMessage.addClass('error');
            } else {
                $toolTransactionMessage.addClass('success');
            }
        } else {
            $toolTransactionMessage.val('');
        }
    }
};