var EmbedMessagesAwareMixin = {
    showEmbedMessages: function() {
        showResponseMessages(this._getEmbedMessages());
    },

    _forgetEmbedMessages: function() {
        this._embedMessges = null;
    },

    _getEmbedMessages: function() {
        return this._embedMessges || (this._embedMessges = this._fetchEmbedMessages());
    },

    _fetchEmbedMessages: function() {
        var $messagesContainer = $('#json'),
            messagesJson = $messagesContainer.html() || '{}';

        $messagesContainer.remove();

        return JSON.parse(messagesJson);
    }
};