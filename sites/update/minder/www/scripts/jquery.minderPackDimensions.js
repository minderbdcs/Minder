(function($){
    var nextRowId = 1;

    function _updateRowFieldFast($row, column, data) {
        var $input = $row.find('[name="' + column + '"]');

        if ($input.length > 0) {
            $input.val(data);
        } else {
            $row.find('[data-column="' + column + '"]').text(data);
        }
    }

    function _updateRowField(id, column, data) {
        var $fields = $(this).find('[data-row-id="' + id + '"]'),
            $input = $fields.filter('[name="' + column + '"]');

        if ($input.length > 0) {
            $input.val(data);
        } else {
            $fields.filter('[data-column="' + column + '"]').text(data);
        }
    }

    function _fillRowDataFast($row, data) {
        $.each(data, function(fieldName, value){
            _updateRowFieldFast($row, fieldName, value);
        });
    }

    function _fillRowData(id, data) {
        var self = this;
        $.each(data, function(fieldName, value){
            _updateRowField.call(self, id, fieldName, value);
        });
    }

    function _doAddRow(reset, rowData, newRowId, $sampleRow) {
        var $newRow = $sampleRow.clone();

        $newRow.find('td, input, select').each(function(){
            if ($(this).attr('data-row-id') == 'sample') {
                $(this).attr('data-row-id', newRowId);
                $(this).attr('data-new-row', true);
            }
        });
        $newRow.removeClass('sample_row').insertBefore($sampleRow);

        if (reset) {
            $newRow.find('input, select').val('');
        }

        if (rowData) {
            _fillRowDataFast($newRow, rowData);
        }

        _updateRow.call(this, newRowId, _getRowData.call(this, newRowId));
        $newRow.show().removeClass('hidden');

        return $newRow;
    }

    function _updateRow(id, data) {
        var $row = $(this).find('[data-row-id="' + id + '"]');
        $row.filter('[data-column="VOL"]').attr('title', data.EXPLAIN.VOL).text(parseFloat(data.CALCULATED.VOL).toFixed(4));
        $row.filter('[data-column="TOTAL_WT"]').attr('title', data.EXPLAIN.TOTAL_WT).text(parseFloat(data.CALCULATED.TOTAL_WT).toFixed(4));
        $row.filter('[data-column="TOTAL_VOL"]').attr('title', data.EXPLAIN.TOTAL_VOL).text(parseFloat(data.CALCULATED.TOTAL_VOL).toFixed(4));

        if (data.TYPE == 'P') {
            $row.filter('[name="PALLET_OWNER"]').show();
        } else {
            $row.filter('[name="PALLET_OWNER"]').hide();
        }
    }

    function _getRowDataFast($rowFields) {
        var
            totalWTUom = _getTotalWTUom.call(this),
            DTToVTUom = _getDTToWtUom.call(this),
            DTUom = _getDTUom.call(this),
            WTUom = _getWTUom.call(this),
            wtFactor = parseFloat(totalWTUom.TO_STANDARD_CONV) / parseFloat(WTUom.TO_STANDARD_CONV),
            dtFactor = parseFloat(DTToVTUom.TO_STANDARD_CONV) / parseFloat(DTUom.TO_STANDARD_CONV),
            dimensionalFactor = _getDimensionalFactor.call(this);
            result = {
                isNew: false,
                rowId: $rowFields.attr('data-row-id'),
                PALLET_OWNER: 'NONE',
                TYPE: '',
                QTY: 1,
                L: 0,
                W: 0,
                H: 0,
                WT: 0,
                UOM: {
                    DT: _getDTUom.call(this).CODE,
                    WT: _getWTUom.call(this).CODE,
                    VT: _getTotalVTUom.call(this).CODE,
                    DTtoVT: _getDTToWtUom.call(this).CODE,
                    TOTAL_WT: _getTotalWTUom.call(this).CODE,
                    TOTAL_VT: _getTotalVTUom.call(this).CODE
                },
                CALCULATED: {
                    VOL: 0,
                    TOTAL_VOL: 0,
                    TOTAL_WT:0,
                    CUBIC_WEIGHT: 0,
                    CHARGE_WEIGHT: 0
                },
                EXPLAIN: {
                    VOL: '',
                    TOTAL_VOL: '',
                    TOTAL_WT: ''
                },
                RAW: {}
            };
        wtFactor = (isNaN(wtFactor) || wtFactor === 0) ? 1 : wtFactor;
        dtFactor = (isNaN(dtFactor) || dtFactor === 0) ? 1 : dtFactor;

        $rowFields.filter('td').each(function(){
            var $this = $(this), name = $this.attr('data-column'), $input = $this.find('input, select').filter('[name="' + name + '"]'), tmpVal;
            if ($input.length > 0) {
                tmpVal = $input.val();
            } else {
                tmpVal = $this.text();
            }

            if ((name == 'PALLET_OWNER') ||  (name == 'TYPE') || (name == 'SSCC')) {
                result[name] = tmpVal;
            } else {
                result[name] = parseFloat(tmpVal);
                result[name] = isNaN(result[name]) ? 0 : result[name];
            }
            result.RAW[name] = tmpVal;
        });
        result.isNew = ($rowFields.filter('[data-new-row]').length > 0);

        result.CALCULATED.VOL = result.L * result.W * result.H * dtFactor * dtFactor * dtFactor;
        result.CALCULATED.TOTAL_VOL = result.CALCULATED.VOL*result.QTY;
        result.CALCULATED.TOTAL_WT = result.WT * result.QTY * wtFactor;
        result.CALCULATED.CUBIC_WEIGHT = (dimensionalFactor > 0) ? result.CALCULATED.VOL / dimensionalFactor : 0;
        result.CALCULATED.CHARGE_WEIGHT = Math.max(result.CALCULATED.TOTAL_WT, result.CALCULATED.CUBIC_WEIGHT);

        return result;
    }

    function _getRowData(id) {
        return _getRowDataFast.call(this, $(this).find('[data-row-id="' + id + '"]'));
    }

    function _recalculateAllRows() {
        var
            self = this;

        $(this).find('tbody.dimensions').find('tr').filter(function(){
            return !$(this).hasClass('sample_row');
        }).each(function(){
            var rowId = $(this).find('[data-row-id]').attr('data-row-id');
            _updateRow.call(self, rowId, _getRowData.call(self, rowId));
        });
    }

    function _getAllRows() {
        var self = this;

        return $(this).find('tbody.dimensions').find('tr').filter(function(){
            return !$(this).hasClass('sample_row');
        }).map(function(){
            return _getRowDataFast.call(self, $(this).find('[data-row-id]'));
        }).get();
    }

    function _getTotals() {
        var
            self = this,
            result = {
                VOL: 0,
                WT: 0,
                PACKS: {
                    PALLETS: 0,
                    SATCHELS: 0,
                    CARTONS: 0,
                    TOTAL: 0
                }
            };

            _getAllRows.call(this).forEach(function(rowData){
                result.VOL += rowData.CALCULATED.TOTAL_VOL;
                result.WT += rowData.CALCULATED.TOTAL_WT;
                result.PACKS.TOTAL += rowData.QTY;

                switch (rowData.TYPE) {
                    case 'P':
                        result.PACKS.PALLETS += rowData.QTY;
                        break;
                    case 'C':
                        result.PACKS.CARTONS += rowData.QTY;
                        break;
                    case 'S':
                        result.PACKS.SATCHELS += rowData.QTY;
                        break;
                }
            });

        return result;
    }

    function _updateTotals(data) {
        var $this = $(this);

        $this.find('.total_pallets').text(data.PACKS.PALLETS);
        $this.find('.total_cartons').text(data.PACKS.CARTONS);
        $this.find('.total_satchels').text(data.PACKS.SATCHELS);
        $this.find('.total_packages').text(data.PACKS.TOTAL);
        $this.find('.total_labels').text(data.PACKS.TOTAL);
        $this.find('.total_weight').text(data.WT.toFixed(4));
        $this.find('.total_volume').text(data.VOL.toFixed(4));
    }

    function _onRowDataChanged(evt) {
        var
            $this = $(this),
            rowId = $(evt.target).attr('data-row-id'),
            rowChangedEvent = $.Event('row-changed'),
            totalsChangedEvent = $.Event('totals-changed');

        rowChangedEvent.rowData = _getRowData.call(this, rowId);
        _updateRow.call(this, rowId, rowChangedEvent.rowData);

        $this.trigger(rowChangedEvent);

        totalsChangedEvent.totals = _getTotals.call(this);
        _updateTotals.call(this, totalsChangedEvent.totals);

        $this.trigger(totalsChangedEvent);
    }

    function _getNewRowId() {
        return $(this).find('[data-new-row]').filter('[data-row-id]').filter(':first-child').attr('data-row-id');
    }

    function _setUoms(uoms) {
        if (!uoms.DEFAULT_DT) {
            showErrors(['Standard "DT" UOM was not found in UOM_TYPE table. UOM convertion not possible. Check system setup.']);
        }

        if (!uoms.DEFAULT_WT) {
            showErrors(['Standard "WT" UOM was not found in UOM_TYPE table. UOM convertion not possible. Check system setup.']);
        }

        $(this).data('uom', uoms);
    }

    function _setUomInputs(inputs) {
        $(this).data('uom-inputs', inputs);
    }

    function _getUomInputs() {
        return $(this).data('uom-inputs');
    }

    function _getTotalVTUom() {
        return _getUoms.call(this).VT;
    }

    function _getTotalWTUom() {
        return _getUoms.call(this).WT;
    }

    function _getDTToWtUom() {
        return _getUoms.call(this).DTtoVT;
    }

    function _getUoms() {
        return $(this).data('uom');
    }

    function _getWTUom() {
        var wtUom = _getUomInputs.call(this).$weightUomSelect.val();
        return _getUoms.call(this).uom[wtUom];
    }

    function _getDTUom() {
        var dtUom = _getUomInputs.call(this).$dimensionsUomSelect.val();
        return _getUoms.call(this).uom[dtUom];
    }

    function _getDimensionalFactor() {
        return _getOptions.call(this).dimensionalFactor;
    }

    function _getOptions() {
        return $.extend({}, defaultOptions, $(this).data('dimension-options') || {});
    }

    function _setOptions(options) {
        $(this).data('dimension-options', options);
    }

    var
        defaultOptions = {
            dimensionalFactor: 0
        },

        methods = {
            init: function(uom, options) {
                var
                    $this = $(this),
                    self = this,
                    inputs = {
                        '$dimensionsUomSelect' : $this.find('select[name="DIMENSION_UOM"]'),
                        '$weightUomSelect' : $this.find('select[name="PACK_WEIGHT_UOM"]')
                    };

                _setOptions.call(this, $.extend({}, defaultOptions, options || {}));
                _setUoms.call(this, uom || {});
                _setUomInputs.call(this, inputs);

                inputs.$dimensionsUomSelect.unbind('.packDimensions').bind('change.packDimensions', function(){
                    inputs.$dimensionsUomSelect.val($(this).val());
                    _recalculateAllRows.call(self);
                    _updateTotals.call(self, _getTotals.call(self));
                });

                inputs.$weightUomSelect.unbind('.packDimensions').bind('change.packDimensions', function(){
                    inputs.$weightUomSelect.val($(this).val());
                    _recalculateAllRows.call(self);
                    _updateTotals.call(self, _getTotals.call(self));
                });

                inputs.$dimensionsUomSelect.change();
                inputs.$weightUomSelect.change();

                $this.find('tbody').delegate('input, select', 'change', $.proxy(_onRowDataChanged, this));
                $this.unbind('remove.stop-remove-propagation').bind('remove.stop-remove-propagation', function(evt){
                    evt.stopPropagation();
                    return false;
                });
            },

            setUoms: function(uoms) {
                if (uoms.DT) {
                    _getUomInputs.call(this).$dimensionsUomSelect.val(uoms.DT);
                }

                if (uoms.WT) {
                    _getUomInputs.call(this).$weightUomSelect.val(uoms.WT);
                }

                _recalculateAllRows.call(this);
                _updateTotals.call(this, _getTotals.call(this));
            },

            setDimensionalFactor: function(dimensionalFactor) {
                var options = _getOptions.call(this);
                options.dimensionalFactor = dimensionalFactor;
                _setOptions.call(this, options);

                _recalculateAllRows.call(self);
                _updateTotals.call(self, _getTotals.call(self));
            },

            addRow: function(reset, rowData) {
                var
                    sampleRow = $(this).find('.sample_row'),
                    newRowId = nextRowId++;

                _doAddRow.call(this, reset, rowData, newRowId, sampleRow);
                _updateTotals.call(this, _getTotals.call(this));

                return newRowId;
            },

            addRows: function(rows) {
                var
                    sampleRow = $(this).find('.sample_row'),
                    newRowId,
                    self = this;

                rows.forEach(function(rowData){
                    newRowId = nextRowId++;
                    _doAddRow.call(self, false, rowData, newRowId, sampleRow);
                });

                _updateTotals.call(this, _getTotals.call(this));
            },

            addRowChangedListener: function(namespace, callback) {
                var fullEventName = 'row-changed.' + namespace;
                $(this).unbind(fullEventName).bind(fullEventName, callback);
            },

            addTotalsChangedListener: function(namespace, callback) {
                var fullEventName = 'totals-changed.' + namespace;
                $(this).unbind(fullEventName).bind(fullEventName, callback);
            },

            setLastRowFieldValue: function(column, newValue) {
                _updateRowField.call(this, _getNewRowId.call(this), column, newValue);
            },

            getLastRowData: function() {
                return _getRowData.call(this, _getNewRowId.call(this));
            },

            getTotals: function() {
                return _getTotals.call(this);
            },

            getAllRows: function() {
                return _getAllRows.call(this);
            },

            commit: function(disableCommitted) {
                var fields = $(this).find('[data-new-row]');
                fields.removeAttr('data-new-row');

                if (disableCommitted) {
                    fields.filter('input, select').attr('disabled', 'disabled').attr('data-locked', 'data-locked');
                }
            },

            hasUncommitted: function() {
                return $(this).find('[data-new-row]').length > 0;
            },

            removeAll: function() {
                $(this).find('tbody.dimensions').find('tr').filter(function(){
                    return !$(this).hasClass('sample_row');
                }).remove();
                _recalculateAllRows.call(this);
                _updateTotals.call(this, _getTotals.call(this));
            },

            removeUncommitted: function() {
                $(this).find('[data-new-row]').parent().filter(function(){
                    return !$(this).hasClass('sample_row');
                }).remove();
                _recalculateAllRows.call(this);
                _updateTotals.call(this, _getTotals.call(this));
            },

            disableDimensionInputs: function() {
                $(this).find('thead').find('select, input').attr('disabled', 'disabled');
            }
        };

    $.fn.minderPackDimensions = function(methodOrSettings) {
        if ($.isFunction(methods[methodOrSettings])) {
            return methods[methodOrSettings].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
            return methods.init.apply(this, Array.prototype.slice.call(arguments, 0));
        }
    };
})(jQuery);