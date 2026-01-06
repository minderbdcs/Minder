(function($){
    $.minderReport = function(method) {
        if (typeof _methods[method] == 'function') {
            return _methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method == 'object'){
            return _methods.init.apply(this, [method]);
        } else {
            $.error( 'Unsupported minderReport method "' +  method + '".' );
        }
    };

    var runReportCallback = function(response) {
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
        "printLabelCallback": runReportCallback
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

        'runReport' : function(reportId, sysScreen, paramsMap, displayReports) {
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
                    'reportId': reportId,
                    'namespace': namespace,
                    'paramsMap': paramsMap,
                    'displayReports': displayReports
                },
                _settings.printLabelCallback,
                'json'
            );

            return this;
        }
    };

    function runReportDirectCallback(response) {
        if (response.messages && response.messages.length > 0)
            showMessage(response.messages);

        if (response.warnings && response.warnings.length > 0)
            showWarnings(response.warnings);

        if (response.errors && response.errors.length > 0)
            showErrors(response.errors);

        if (response.reports && response.reports.length > 0) {
            $.each(response.reports, function(index, value) {
                window.open(_directSettings.viewServiceUrl + '/fileId/' + value);
            });
        }
    }

    var _directSettings = {
        "printServiceUrl": '/minder/service/run-report-direct',
        "viewServiceUrl": '/minder/service/show-report',
        "printLabelCallback": runReportDirectCallback
    };

    $.minderReportDirect = function(method) {
        if (typeof _directMethods[method] == 'function') {
            return _directMethods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method == 'object'){
            return _directMethods.init.apply(this, [method]);
        } else {
            $.error( 'Unsupported minderReport method "' +  method + '".' );
        }
    };

    var
        _directMethods = {
            'init': function(options) {
                $.extend(_directSettings, options);
                return this;
            },

            'runReport' : function(reportId, paramsMap, displayReports) {
                paramsMap = (typeof paramsMap == 'undefined') ? [] : paramsMap;
                displayReports = (typeof displayReports == 'undefined') ? false : !!displayReports;

                $.post(
                    _directSettings.printServiceUrl,
                    {
                        'reportId': reportId,
                        'paramsMap': paramsMap,
                        'displayReports': displayReports
                    },
                    _directSettings.printLabelCallback,
                    'json'
                );

                return this;
            }
        };
})(jQuery);