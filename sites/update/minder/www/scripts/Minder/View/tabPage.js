Minder_View_TabPage = $.inherit(
    Minder_View_Container,
    {
        getTabId: function() {
            return this.getSetting('RECORD_ID');
        },

        getOrder: function() {
            return this._settings.SST_SEQUENCE;
        },

        setOrder: function(order) {
            this._settings.SST_SEQUENCE = order;
            return this;
        },

        getTitle: function() {
            return this._settings.SST_TITLE;
        },

        setTitle: function(title) {
            this._settings.SST_TITLE = title;
            return this;
        }
    },
    {
        //static members
    }
);