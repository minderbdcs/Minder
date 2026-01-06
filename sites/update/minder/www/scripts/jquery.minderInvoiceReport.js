(function($){
    $.minderInvoiceReport = function(method) {
        if (typeof _methods[method] == 'function') {
            return _methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method == 'object'){
            return _methods.init.apply(this, [method]);
        } else {
            $.error( 'Unsupported minderReport method "' +  method + '".' );
        }
    };

    var runInvoiceReportCallback = function(response) {
        if (response.messages && response.messages.length > 0)
            showMessage(response.messages);

        if (response.warnings && response.warnings.length > 0)
            showWarnings(response.warnings);

        if (response.errors && response.errors.length > 0)
            showErrors(response.errors);

        if (response.reports && response.reports.length > 0) {
            $.each(response.reports, function(index, value) {
                window.open(_settings.viewServiceUrl + '/fileId/' + value);
            });
        }
    };

    var _settings = {
        "printServiceUrl": '/',
        "viewServiceUrl": '/',
        'namespaceMap': null,
        'runInvoiceReportCallback': runInvoiceReportCallback
    };

    var getNamespaceByScreen = function(sysScreen) {
        if (_settings.namespaceMap == null) return null;

        if (typeof _settings.namespaceMap[sysScreen] == 'undefined') return null;

        return _settings.namespaceMap[sysScreen];
    };

    var _methods = {
        init : function( options ) {
            $.extend(_settings, options);
            return this;
        },

        'runReport' : function(reportType, sysScreen, paramsMap, displayReports) {
            paramsMap = (typeof paramsMap == 'undefined') ? [] : paramsMap;
            displayReports = (typeof displayReports == 'undefined') ? false : !!displayReports;

            var namespace = getNamespaceByScreen(sysScreen);
            if (namespace == null) {
                showErrors(['Unknown Sys Screen "' + sysScreen + '"']);
                return this;
            }

            $.post(
                _settings.printServiceUrl,
                {
                    'reportType': reportType,
                    'namespace': namespace,
                    'paramsMap': paramsMap,
                    'displayReports': displayReports
                },
                _settings.runInvoiceReportCallback,
                'json'
            );

            return this;
        }
    };
})(jQuery);