(function($){

    function printLabelCallback(response) {
        if (response.errors && response.errors.length > 0)
            showErrors(response.errors);

        if (response.warnings && response.warnings.length > 0)
            showWarnings(response.warnings);

        if (response.messages && response.messages.length > 0)
            showMessage(response.messages);
    }

    var _settings = {
        "printServiceUrl": '/minder/services/label/print-label',
        'namespaceMap': null,
        "printLabelCallback": printLabelCallback
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

        'printLabel' : function(sysScreen, labelName, paramsMap, companyId, whId) {
            paramsMap = (typeof paramsMap == 'undefined') ? [] : paramsMap;
            labelName = labelName || '';
            companyId = companyId || '';
            whId      = whId || '';

            var namespace = getNamespaceByScreen(sysScreen);
            if (namespace == null) {
                showErrors(['Unknown Sys Screen "' + sysScreen + '"']);
                return this;
            }

            $.post(
                _settings.printServiceUrl,
                {
                    'labelName': labelName,
                    'namespace': namespace,
                    'paramsMap': paramsMap,
                    'companyId': companyId,
                    'whId'     : whId
                },
                _settings.printLabelCallback,
                'json'
            );

            return this;
        }
    };

    $.minderLabel = function(method) {
        if (typeof _methods[method] == 'function') {
            return _methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method == 'object'){
            return _methods.init.apply(this, [method]);
        } else {
            $.error( 'Unsupported minderReport method "' +  method + '".' );
        }
    };

})(jQuery);