Minder_Model_Page = $.inherit(
    Minder_Model,
    {
        showLeftPannel: function(origin, show) {
            this._setFieldValue('SM_SHORTCUTS', (typeof show == 'undefined') ? true : !!show, origin);
            this._setFieldValue('isLeftPannelVisible', (typeof show == 'undefined') ? true : !!show, origin);
            this.notifyFieldsChanged(origin);
        },

        hideLeftPannel: function(origin) {
            this.showLeftPannel(origin, false);
        },

        _getPageId: function() {
            return this.getFieldValue('SM_SUBMENU_ID');
        },

        isShortcutsVisible: function() {
            return this.getFieldValue('SM_SHORTCUTS');
        },

        isLimitVisible: function() {
            return this.getFieldValue('SM_LIMIT');
        },

        isLeftPannelVisible: function() {
            return this.getFieldValue('isLeftPannelVisible');
        },

        isModulesVisible: function() {
            return this.getFieldValue('SM_MENU_DISPLAY');
        },

        showModules: function(origin, show) {
            this._setFieldValue('SM_MENU_DISPLAY', (typeof show == 'undefined') ? true : !!show, origin);
            this.notifyFieldsChanged(origin);
        },

        hideModules: function(origin) {
            this.showModules(origin, false);
        },

        getCompanyLimit: function() {
            return this.getFieldValue('companyLimit');
        },

        setCompanyLimit: function(origin, companyId) {
            this._setFieldValue('companyLimit', companyId);
            this.notifyCompanyLimitChanged(origin);
        },

        notifyCompanyLimitChanged: function(origin) {
            this.notify(this.__self.companyLimitChangedEvent, origin);
        },

        getWarehouseLimit: function() {
            return this.getFieldValue('warehouseLimit');
        },

        setWarehouseLimit: function(origin, warehouseId) {
            this._setFieldValue('warehouseLimit', warehouseId);
            this.notifyWarehouseLimitChanged(origin);
        },

        notifyWarehouseLimitChanged: function(origin) {
            this.notify(this.__self.warehouseLimitChangedEvent, origin);
        },

        getCurrentPrinter: function() {
            return this.getFieldValue('currentPrinter');
        },

        setCurrentPrinter: function(origin, printerId) {
            this._setFieldValue('currentPrinter', printerId);
            this.notifyCurrentPrinterChanged(origin);
        },

        notifyCurrentPrinterChanged: function(origin) {
            this.notify(this.__self.currentPrinterChangedEvent, origin);
        }
    },
    {
        //static members
        companyLimitChangedEvent: 'company-limit-changed',
        warehouseLimitChangedEvent: 'warehouse-limit-changed',
        currentPrinterChangedEvent: 'current-printer-changed'
    }
);