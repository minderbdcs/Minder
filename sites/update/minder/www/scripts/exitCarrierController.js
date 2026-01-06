minderNamespace('Minder.Dlg.ExitCarrier', function () {
    $.extend(this, Minder.AbstractPageController);

    var
        _active = false,
        _knownCarriers = [],
        _scannedCarriers = [],
        _lastCarrierId = '',
        _$dialogContainer,
        _loadUrl = '',
        _despatchPackUrl = '';

    this.init = function (searchResults, sysScreenData, knownCarriers, $dialogContainer, loadUrl, despatchPackUrl) {
        this.initScreenVarianceSearchResults(searchResults, true);
        this.initDialog($dialogContainer);
        this.onDataReady(sysScreenData);
        this.setKnownCarriers(knownCarriers);
        _loadUrl = loadUrl;
        this.setDespatchPackUrl(despatchPackUrl);
    };

    this.setKnownCarriers = function(knownCarriers) {
        _knownCarriers = knownCarriers;
    };

    this.setDespatchPackUrl = function(despatchPackUrl) {
        _despatchPackUrl = despatchPackUrl;
    };

    this.initDialog = function ($dialogContainer) {
        _$dialogContainer = $dialogContainer;

        $dialogContainer.dialog(
            {
                buttons: {
                    CANCEL: function (evt) {
                        evt.preventDefault();
                        $dialogContainer.dialog('close');
                    }},
                buttonStyle: 'green-button',
                width: 1024,
                height: 600,
                insertHtml: '&nbsp;',
                autoOpen: false,
                resizable: false,
                modal: false
            }
        ).unbind('dialogopen').bind('dialogopen', function (event, ui) {
            $(this).show();
            $('#barcode').focus();
            _active = true;
        }).unbind('dialogclose').bind('dialogclose', function (event, ui) {
            _active = false;
            $dialogContainer.find('[name="packId"]').val('');
            $dialogContainer.find('[name="packCarrier"]').val('');
            $dialogContainer.find('[name="carrierId"]').val('');
            $dialogContainer.find('[data-carrier-message]').text('');
        });
    };

    this.isActive = function () {
        return _active;
    };

    this.getDialogContainer = function () {
        return _$dialogContainer;
    };

    this.getLoadUrl = function() {
        return _loadUrl;
    };

    this.showDialog = function () {
        this.getDialogContainer().removeClass('error success');
        this.getDialogContainer().dialog('open');
    };

    this.closeDialog = function () {
        this.getDialogContainer().dialog('close');
    };

    this.isKnownCarrier = function (carrierId) {
        return _knownCarriers.indexOf(carrierId) > -1;
    };

    this.isNewCarrier = function (carrierId) {
        return _scannedCarriers.indexOf(carrierId) < 0;
    };

    this.setCarrier = function (carrierId) {
        var $dialog = this.getDialogContainer();

        _lastCarrierId = carrierId;
        $dialog.find('[name="carrierId"]').val(carrierId);

        if (this.isKnownCarrier(carrierId)) {
            this.setMessageText('');

            if (this.isNewCarrier(carrierId)) {
                _scannedCarriers.push(carrierId);
            }
            this.getDialogContainer().addClass('success');
        } else {
            this.getDialogContainer().addClass('error');
            this.setMessageText('Unknown Carrier "' + carrierId + '"');
        }
    };

    this.setMessageText = function (text) {
        this.getDialogContainer().find('[data-carrier-message]').text(text);
    };

    this.onDataReady = function (response) {
        if (response.errors && response.errors.length > 0) {
            this.getDialogContainer().addClass('error');
            this.setMessageText(response.errors.join("\n"));
        } else {
            this.getDialogContainer().addClass('success');

            if (response.messages && response.messages.length > 0) {
                this.setMessageText(response.messages.join("\n"));
            }
        }

        Minder.AbstractPageController.onDataReady.call(this, response);

        if (response.carriersStatistics) {
            this.updateCarriersStatistics(response.carriersStatistics);
        }
        this.getDialogContainer().find('[name="packCarrier"]').val(response.pickedCarrierId);
    };

    this.getDespatchPackUrl = function() {
        return _despatchPackUrl;
    };

    this.setPackId = function (despatchLabelNo) {
        var $dialog = this.getDialogContainer(),
            data,
            self = this;

        $dialog.removeClass('error success');
        $dialog.find('[name="packId"]').val(despatchLabelNo);
        $dialog.find('[name="packCarrier"]').val('');


        if (_lastCarrierId && this.isKnownCarrier(_lastCarrierId)) {
            this.setMessageText('');
            data = this.prepareLoadDataToSend(this.getRegisteredNamespaces());
            data.scannedCarriers = _scannedCarriers;
            data.carrierId = _lastCarrierId;
            data.despatchLabelNo = despatchLabelNo;

            $(document).queue(function () {
                $.post(
                    self.getDespatchPackUrl(),
                    data,
                    $.proxy(self.onDataReady, self),
                    'json'
                )
                ;
            });
        } else {
            this.setMessageText('Unknown Carrier "' + _lastCarrierId + '"');
        }
    };

    this.updateCarriersStatistics = function (carriersStatistics) {
        var contentParts = [], evenOdd = ['odd', 'even'];

        carriersStatistics.forEach(function (carrierTotals) {
            var rowClass = evenOdd.shift(),
                rowParts = [
                    '<tr class="' + rowClass + '">',
                    '<td>',
                    carrierTotals.PICKD_CARRIER_ID,
                    '</td>',
                    '<td>',
                    carrierTotals.TOTAL_PACKS,
                    '<tr>',
                    '</tr>'
                ];

            evenOdd.push(rowClass);
            contentParts.push(rowParts.join(''));
        });
        $('.carriers-statistics').html(contentParts.join(''));
    };

    this.setCloseListener = function (namespace, callback) {
        var fullEventName = 'dialogclose.' + namespace;
        this.getDialogContainer().unbind(fullEventName).bind(fullEventName, callback);
    };
});
