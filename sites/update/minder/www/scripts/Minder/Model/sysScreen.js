Minder_Model_SysScreen = $.inherit(
    Minder_Model,
    {
        _dataSet: null,
        _offscreenSelectedRowsAmount: null,

        //-------------------------------------------------------------------------------------------
        //Model interface
        //-------------------------------------------------------------------------------------------
        getFieldValue: function(name) {
            switch(name) {
                case '_TOTAL_ROWS':
                    return this.getTotalRows();
                case '_SELECTED_ROWS':
                    return this.getSelectedRowsAmount();
                case 'selectComplete':
                    return this.getSelectCompeteValue();
                default:
                    return this.__base(name)
            }
        },

        getSlaveScreens: function() {
            return this._fields['SLAVE_SCREENS'] || [];
        },

        getName: function() {
            return this._fields['SS_NAME'];
        },

        _setFields: function(fields) {
            this._offscreenSelectedRowsAmount = null;
            this.__base(fields);

            if (this.getFieldValue('SS_REFRESH') > 0)
                this._refreshIn(this.getFieldValue('SS_REFRESH'));

            return this;
        },

        _getPageNo: function() {
            var pageNo = this._fields['_PAGE_NO'];
            return isNaN(pageNo) ? 1 : pageNo;
        },

        _getShowBy: function() {
            var showBy = this._fields['_SHOW_BY'];
            return isNaN(showBy) ? 5 : showBy;
        },

        getRowsOffset: function() {
            return this._getShowBy() * (this._getPageNo() - 1);
        },

        getRowsAmount: function() {
            if (this._dataSet == null) return 0;
            return this._dataSet.getRowsAmount();
        },

        getTotalRows: function() {
            return this._fields['_TOTAL_ROWS'];
        },

        setShowBy: function(value, origin) {
            var oldRowsOffset = this._getShowBy() * (this._getPageNo() - 1);
            this._setFieldValue('_SHOW_BY', value);

            var showBy = this._getShowBy();
            var pageNo = Math.floor(oldRowsOffset / showBy) + 1;
            var totalRows = this.getTotalRows();
            var normalizedPageNo = Math.min(pageNo, Math.floor(totalRows / showBy) + 1);

            this._setFieldValue('_PAGE_NO', normalizedPageNo);

            this.notifyFieldsChanged(origin);

            var rowsOffset = showBy * (normalizedPageNo - 1);

            this.loadItems(rowsOffset, showBy);
            this._storeFieldValue('_SHOW_BY', showBy);
            this._storeFieldValue('_PAGE_NO', normalizedPageNo);
        },

        getTotalPages: function() {
            return Math.ceil(this.getTotalRows() / this._getShowBy());
        },

        setPageNo: function(value, origin) {
            var showBy = this._getShowBy();
            var totalRows = this.getTotalRows();
            var normalizedPageNo = Math.min(value, Math.floor(totalRows / showBy) + 1);

            this._setFieldValue('_PAGE_NO', normalizedPageNo);
            this.notifyFieldsChanged(origin);
            this.loadItems(showBy * (normalizedPageNo - 1), showBy);
            this._storeFieldValue('_PAGE_NO', normalizedPageNo);
        },

        setFieldValue: function(name, value, origin) {
            switch(name) {
                case '_SHOW_BY':
                    this.setShowBy(value, origin);
                    break;
                case '_PAGE_NO':
                    this.setPageNo(value, origin);
                    break;
                case 'selectComplete':
                    return this.selectComplete(origin, value);
                case 'selectionMode':
                    this._setFieldValue(name, value);
                    this._storeFieldValue('selectionMode', value);
                    break;
                default:
                    this._setFieldValue(name, value);
            }
            this.notifyFieldsChanged(origin);
            return this;
        },

        _setFieldValue: function(name, value) {
            switch(name) {
                case '_SELECTED_ROWS':
                    this._offscreenSelectedRowsAmount = null;
                    this.__base(name, value);
                    break;
                default:
                    this.__base(name, value);
            }
        },

        makeSearch: function(searchFields) {
            this.notify(this.__self.dataUpdateStartedEvent, this);
            this._callRemote(
                'makeSearch', [
                    searchFields,
                    this._getRowOffset(),
                    this._getShowBy()
                ],
                this._makeSearchCallback
            );
            this.notify(Minder_Model_Search.searchEvent, this);
        },

        _makeSearchCallback: function(response) {
            if (response.error && response.error.message)
                showErrors([response.error.message]);

            if (response.result) {
                if (this.getFieldValue('SS_REFRESH') > 0)
                    this._refreshIn(this.getFieldValue('SS_REFRESH'));

                if (response.result.items)
                    this.setData(response.result.items);

                if (response.result.fields)
                    this.setFields(response.result.fields);
            }
        },


        _getOffscreenSelectedRowsAmount: function() {
            if (this._offscreenSelectedRowsAmount == null) {
                this._offscreenSelectedRowsAmount = this._fields['_SELECTED_ROWS'] - this._getOnscreenSelectedRowsAmount();
                this._offscreenSelectedRowsAmount = isNaN(this._offscreenSelectedRowsAmount) ? null : this._offscreenSelectedRowsAmount;
            }

            return this._offscreenSelectedRowsAmount;
        },

        _getOnscreenSelectedRowsAmount: function() {
            return (this._dataSet == null ? 0 : this._dataSet.getSelectedRows().length);
        },

        getSelectedRowsAmount: function() {
            return this._fields['_SELECTED_ROWS'] = this._getOffscreenSelectedRowsAmount() + this._getOnscreenSelectedRowsAmount();
        },

        getSelectCompeteValue: function() {
            return this.getSelectedRowsAmount() == this.getTotalRows();
        },

        getSelectionMode: function() {
            return (this.getFieldValue('selectionMode') == 'one') ? 'one' : 'all';
        },

        //-------------------------------------------------------------------------------------------
        //Model interface
        //-------------------------------------------------------------------------------------------

        _refreshIn: function(seconds) {
            setTimeout(this.loadItems, seconds * 1000);
        },

        _setData: function(data) {
            if (!(this._dataSet instanceof Minder_Model_DataSet))
                this._dataSet = new Minder_Model_DataSet();

            this._dataSet.setData(data, this);
            this._offscreenSelectedRowsAmount = null;
        },

        _updateData: function(data) {
            this._dataSet.updateData(data, this);
        },

        _getServiceUrl: function() {
            return this.getFieldValue('serviceUrl');
        },

        _getPageId: function() {
            return this.getFieldValue('SS_MENU_ID');
        },

        _getScreenName: function() {
            return this.getFieldValue('SS_NAME');
        },

        _getExportUrl: function() {
            return this.getFieldValue('exportUrl');
        },

        exportRows: function(format) {

            format = format || '';

            window.location = this._getExportUrl() + '/' + encodeURIComponent(format);
        },

        _callRemote: function(method, params, callback) {
            $.post(
                this._getServiceUrl(),
                {
                    'method': method,
                    'params': params
                },
                callback,
                'json'
            );
        },

        loadItems: function(rowOffset, itemsCountPerPage) {
            var args = [];
            if (typeof rowOffset != 'undefined') args.push(rowOffset);
            if (typeof itemsCountPerPage != 'undefined') args.push(itemsCountPerPage);

            this.notify(this.__self.dataUpdateStartedEvent, this);
            this._callRemote('getItems', args, this._loadItemsCallback);
        },

        _getRowOffset: function() {
            var showBy = this._getShowBy();
            var pageNo = this._getPageNo();
            return showBy * (pageNo - 1);
        },

        loadDependentItems: function(masterScreen) {
            this.notify(this.__self.dataUpdateStartedEvent, this);
            this._callRemote(
                'getDependItems',
                [
                    masterScreen.getName(),
                    masterScreen.getDataRows(),
                    this._getRowOffset(),
                    this._getShowBy()
                ],
                this._loadDependentItemsCallback
            );
        },

        _loadDependentItemsCallback: function(response) {
            if (response.result) {
                if (this.getFieldValue('SS_REFRESH') > 0)
                    this._refreshIn(this.getFieldValue('SS_REFRESH'));

                if (response.result.items)
                    this.setData(response.result.items);

                if (response.result.fields)
                    this.setFields(response.result.fields);

                if (response.result.statistics) {
                    if (typeof response.result.statistics.totalRowsAmount != undefined)
                        this.setFieldValue('_TOTAL_ROWS', response.result.statistics.totalRowsAmount);

                    if (typeof response.result.statistics.selectedRowsAmount != undefined)
                        this.setFieldValue('_SELECTED_ROWS', response.result.statistics.selectedRowsAmount);
                }
            }

        },

        _storeFieldValue: function(fieldName, value) {
            this._callRemote('storeFieldValue', [fieldName, value]);
        },

        _storeShowBy: function(showBy) {
            this._callRemote('storeShowBy', [showBy]);
        },

        _loadItemsCallback: function(response) {
            if (response.result) {
                if (this.getFieldValue('SS_REFRESH') > 0)
                    this._refreshIn(this.getFieldValue('SS_REFRESH'));

                this.setData(response.result);
            }
        },

        //------------------------------------------------------------------------------------------------
        //DataSet interface
        //------------------------------------------------------------------------------------------------
        setData: function(data, origin) {
            this._setData(data);
            this.notify(Minder_Model_DataSet.dataChangedEvent, origin);
        },

        updateData: function(data, origin) {
            this._updateData(data);
            this.notify(Minder_Model_DataSet.dataChangedEvent, origin);
        },

        getCellValue: function(rowId, fieldName) {
            if (this._dataSet === null) return null;
            return this._dataSet.getCellValue(rowId, fieldName);
        },

        getDataRows: function() {
            if (this._dataSet === null)
                return [];
            return this._dataSet.getDataRows();
        },
        
        getRow: function(rowId) {
            if (this._dataSet == null) return null;
            return this._dataSet.getRow(rowId);
        },

        rowSelected: function(rowId) {
            if (this._dataSet == null) return false;

            if (rowId == 'all') {
                return this._dataSet.allRowsSelected();
            }
            return this._dataSet.rowSelected(rowId);
        },

        selectRow: function(rowId, origin, selected) {
            this._selectRow(rowId, selected);
            this.notify(Minder_Model_DataSet.rowSelectedEvent, origin);
            this.notifyFieldsChanged(origin);
        },

        _selectRow: function(rowId, selected) {
            selected = (typeof selected == 'undefined') ? true : !!selected;
            if (this._dataSet == null) return this;

            var rows = [];
            if (rowId == 'all') {
                this._dataSet.selectAll(this, selected);
                rows = this._dataSet.getDataRows();
            } else {
                if (this.getSelectionMode() == 'one'){
                    this._dataSet.selectAll(this, false);
                    this.setFieldValue('_SELECTED_ROWS', 0);
                }
                this._dataSet.selectRow(rowId, this, selected);
                rows = [this._dataSet.getRow(rowId)];
            }

            this._callRemote('selectRows', {'rows': rows, 'selected': selected, selecttionMode: this.getSelectionMode()});

            return this;
        },

        selectComplete: function(origin, selected) {
            this._selectComplete(selected);
            this.notify(Minder_Model_DataSet.rowSelectedEvent, origin);
            this.notifyFieldsChanged(origin);
        },

        _selectComplete: function (selected) {
            selected = (typeof selected == 'undefined') ? true : !!selected;

            if (this._dataSet == null) return this;

            if (selected) {
                this._dataSet.selectAll(this, selected);
                this._setFieldValue('_SELECTED_ROWS', this.getTotalRows());
            } else {
                this._dataSet.selectAll(this, selected);
                this._setFieldValue('_SELECTED_ROWS', 0);
            }

            this._callRemote('selectComplete', {'selected': selected});
            return this;
        },

        getSelectedRows: function(selected) {
            if (this._dataSet == null) return [];
            return this._dataSet.getSelectedRows(selected);
        },

        setRowField: function(rowId, field, value, origin) {
            this._setRowField(rowId, field, value);
            this.notify(Minder_Model_DataSet.dataChangedEvent, origin);
        },

        _setRowField: function(rowId, field, value) {
            if (this._dataSet == null)
                return;

            this._dataSet.setRowField(rowId, field, value);
        },

        notifyDataUpdateStarted: function(origin) {
            this.notify(Minder_Model_SysScreen.dataUpdateStartedEvent, origin);
        },

        saveChanges: function() {
//            this.notifyDataUpdateStarted(this);
            var changedRows = this._dataSet.getChangedRows();

            if (changedRows.length < 1) {
                return;
            }
            this.notifyDataUpdateStarted(this);
            this._callRemote('update', [changedRows], this._saveChangesCallback);
        },

        _saveChangesCallback: function(response) {
            if (response.error && response.error.message)
                showErrors([response.error.message]);

            if (response.result) {
                if (response.result.errors && response.result.errors.length) {
                    showErrors(response.result.errors);
                }

                if (response.result.warnings && response.result.warnings.length) {
                    showWarnings(response.result.warnings);
                }

                if (response.result.messages && response.result.messages.length) {
                    showMessage(response.result.messages);
                }

                if (response.result.items) {
                    this.updateData(response.result.items);
                }
            }

        }

    },
    {
        //static members
        settingChangedEvent: 'settings-changed',
        dataChangedEvent: 'data-changed',
        dataUpdateStartedEvent: 'data-update-started',

        modelRegister: {},

        registerScreenModel: function(model, autoRegisterMasterSlaveHandler) {
            if (autoRegisterMasterSlaveHandler) {
                $(model).bind(Minder_Model_DataSet.rowSelectedEvent, function(evt){
                    evt.target.getSlaveScreens().forEach(function(screenName) {
                        var screen = Minder_Model_SysScreen.getModel(screenName);

                        if (screen) {
                            screen.loadDependentItems(evt.target);
                        }
                    });

                });

                $(model).bind(Minder_Model_Search.searchEvent, function(evt) {
                    evt.target.getSlaveScreens().forEach(function(screenName) {
                        var screen = Minder_Model_SysScreen.getModel(screenName);

                        if (screen) {
                            screen.loadDependentItems(evt.target);
                        }
                    });
                });
            }
            Minder_Model_SysScreen.modelRegister[model.getName()] = model;
        },

        getModel: function(sysScreenName) {
            return Minder_Model_SysScreen.modelRegister[sysScreenName];
        }
    }
);
