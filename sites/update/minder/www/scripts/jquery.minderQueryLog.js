(function($){
    $.minderQueryLog = function(method) {
        if (typeof _methods[method] == 'function') {
            return _methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
            return _methods.init.apply(this, arguments);
        }
    };

    var
        _methods = {
            init: function(settings) {
                $.extend(globalSettins, settings);
                return this;
            },

            execute: function(command) {
                var
                    commandParts = /^(record|report):(\s|)(((mark) (.*))|((\w+|\d+)(\s(\d+)|)))/i.exec(command),
                    commandName,
                    range,
                    limit;

                if (commandParts === null)
                    return;

                commandName = commandParts[1] || '';
                range = commandParts[5] || commandParts[8] || '';
                limit = commandParts[6] || commandParts[10] || '';

                switch (commandName.toLowerCase()) {
                    case 'record':
                        _executeRecordCommand(range.toLowerCase(), limit);
                        break;
                    case 'report':
                        _executeReportCommand(range.toLowerCase(), limit.toLowerCase())
                }
            },

            setLimit: function(limit) {
                $.post(
                    globalSettins.url + '/set-limit',
                    {
                        'limit' : limit
                    },
                    null,
                    'json'
                );
            },

            setMark: function(label) {
                $.post(
                    globalSettins.url + '/set-mark',
                    {
                        'label' : label
                    },
                    null,
                    'json'
                );
            },

            getLog: function(range, limit) {
                $.post(
                    globalSettins.url + '/get-log',
                    {
                        'limit' : limit,
                        'range' : range
                    },
                    _logReady,
                    'json'
                );
            }


        },

        globalSettins = {
            url: ''
        },

        $logDialog = null;

    function _executeRecordCommand(limitStr, label) {
        var
            limit = parseInt(limitStr);

        if (limitStr == 'mark') {
            _methods.setMark(label);
        } else {
            if (isNaN(limit))
                return;

            _methods.setLimit(limit);
        }
    }

    function _executeReportCommand(range, limitStr) {
        var
            limit = parseInt(limitStr);

        if ((range == 'last' || range == 'first')) {
            if (isNaN(limit))
                return;
        } else if (range == 'all') {
            limit = 0;
        } else {
            limit = parseInt(range);
            range = 'last';

            if (isNaN(limit))
                return;
        }

        _methods.getLog(range, limit);
    }

    function _createLogDialog() {

        var
            $dialog;

        $('body').append("\
            <div id=\"debug-dialog\" style=\"display: none;\" title=\"Debug\" class=\"flora\"> \
                <h2>Query Log</h2> \
                <div style=\"max-height: 300px; overflow: auto;\"> \
                    <table class=\"withborder tablesorter\"> \
                        <thead> \
                            <tr> \
                                <th>#</th> \
                                <th>Query</th> \
                                <th>Params</th> \
                                <th>Time</th> \
                            </tr> \
                        </thead> \
                        <tbody></tbody> \
                    </table> \
                </div> \
            </div>\
        ");

        $dialog = $('#debug-dialog');

        $dialog.dialog({
            resizable: false,
            height: 400,
            width: 1000,
            autoOpen: false
        }).bind('dialogopen', function(evt){$(this).show()});

        $dialog.find('table').tablesorter({widgets: ['zebra']});
        return $dialog;
    }

    function _getLogDialog() {
        if ($logDialog == null) {
            $logDialog = _createLogDialog();
        }

        return $logDialog;
    }

    function _logReady(response) {
        var
            index,
            htmlRowsFragment,
            newTd,
            newTr;

        if (response.messages && response.messages.length > 0) {
            showMessage(response.messages);
        }

        if (response.warnings && response.messages.length > 0) {
            showWarnings(response.warnings)
        }

        if (response.errors && response.errors.length > 0) {
            showErrors(response.errors);
            return;
        }

        if (response.log) {
            _getLogDialog().find('table tbody').empty();

            htmlRowsFragment = document.createDocumentFragment();
            for (index = response.log.length - 1; index >= 0; index--) {
                newTr = document.createElement('tr');
                newTd = document.createElement('td');
                newTd.appendChild(document.createTextNode(index.toString()));
                newTr.appendChild(newTd);
                newTd = document.createElement('td');
                newTd.appendChild(document.createTextNode(response.log[index].query));
                newTr.appendChild(newTd);
                newTd = document.createElement('td');
                newTd.appendChild(document.createTextNode(response.log[index].args.join(', ')));
                newTr.appendChild(newTd);
                newTd = document.createElement('td');
                newTd.appendChild(document.createTextNode(parseFloat(response.log[index].time).toFixed(5)));
                newTr.appendChild(newTd);
                htmlRowsFragment.appendChild(newTr);
            }

            _getLogDialog().find('table tbody')[0].appendChild(htmlRowsFragment);
            _getLogDialog().find('table').trigger('update').trigger('applyWidgets');
        }
        $('#debug-dialog').dialog('open');
    }
})(jQuery);