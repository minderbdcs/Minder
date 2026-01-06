/**
* Plugin for Minder project allow save/cancel edited search results
*/

/**
 * Requirements:
 * - jQuery (John Resig, http://www.jquery.com/)
 **/

(function($) {

    var methods = {
        'init' : function(url, settings, callBacks) {
            var defaultSettings = {
                'saveButtonSelector'       : '.save_btn',
                'cancelButtonSelector'     : '.cancel_btn',
                'modificationFlagSelector' : '.modification_flag',
                'namespace'                : '',
                'addRowButtonSelector'     : '.add_row_btn',
                'saveUrl'                  : url,
                'addRowUrl'                : url,
                'tabDatasetSelector'       : '.dataset'
            };

            var defaultCallbacks = {

                'onSave'     : function(evt) {
                    evt.preventDefault();
                    methods.save.call(scope);
                },

                'onCancel'   : function(evt) {
                    evt.preventDefault();
                    methods.cancel.call(scope);
                },

                'onAddRow' : function(evt) {
                    evt.preventDefault();
                    methods.addRow.call(scope);
                },

                'afterCancel' : function(evt) {
                    //just empty callback
                },

                'onElementChange' : function(evt) {
                    $(settings.modificationFlagSelector).html(' *');

                    var rowId = $(this).attr('row-id');
                    var columnId = $(this).attr('column-id');
                    var selectorString = '[row-id="' + rowId + '"][column-id="' + columnId + '"]';
                    $('input' + selectorString + ', select' + selectorString).minderNumberFormat('setValue', $(this).val()).data('changed', 'true');


                    $(settings.cancelButtonSelector).removeAttr('disabled');
                    $(settings.saveButtonSelector).removeAttr('disabled');
                },

                'onElementKeydown' : function(evt) {
                    //test for data changed is more easy in onKeyup event
//                var specialKeys = {
//                    9  : "tab",      13 : "return", 16 : "shift",  17 : "ctrl",     18 : "alt",     19 : "pause",
//                    20 : "capslock", 27 : "esc",    33 : "pageup", 34 : "pagedown", 35 : "end",     36 : "home",
//                    37 : "left",     38 : "up",     39 : "right",  40 : "down",     45 : "insert",
//                    112: "f1",       113: "f2",     114: "f3",     115: "f4",       116: "f5",      117: "f6",     118: "f7",    119: "f8",
//                    120: "f9",       121: "f10",    122: "f11",    123: "f12",      144: "numlock", 145: "scroll", 224: "meta"
//                };
//
//                if (typeof specialKeys[evt.which] !== 'undefined')
//                    return;
//
//                if (evt.altKey || evt.ctrlKey || evt.metaKey)
//                    return;
//
//                $(settings.modificationFlagSelector).html(' *');
//                $(this).data('changed', 'true');

//                $(settings.cancelButtonSelector).removeAttr('disabled');
//                $(settings.saveButtonSelector).removeAttr('disabled');
                },

                'onElementKeyup' : function(evt) {
                    if ($(this).attr('original_value') != $(this).val()) {
                        //data changed

                        $(settings.modificationFlagSelector).html(' *');
                        var rowId = $(this).attr('row-id');
                        var columnId = $(this).attr('column-id');

                        var selectorString = '[row-id="' + rowId + '"][column-id="' + columnId + '"]';
                        $('input' + selectorString + ', select' + selectorString).val($(this).val()).data('changed', 'true');

                        $(settings.cancelButtonSelector).removeAttr('disabled');
                        $(settings.saveButtonSelector).removeAttr('disabled');
                    }
                },

                'onError'    : function(response) {
                    $(settings.modificationFlagSelector).html(' *');
                    $(settings.cancelButtonSelector).removeAttr('disabled');
                    $(settings.saveButtonSelector).removeAttr('disabled');

                    if (response.errors && response.errors.length > 0)
                        showErrors(response.errors);
                },
                'onSuccess'  : function(response) {
                    if (response.warnings && response.warnings.length > 0)
                        showWarnings(response.warnings);

                    if (response.messages && response.messages.length > 0)
                        showMessage(response.messages);

                    if (response.location)
                        location.href = response.location;
                }
            };

            if (settings) $.extend(defaultSettings, settings);
            if (callBacks) $.extend(defaultCallbacks, callBacks);
            settings  = defaultSettings;
            callBacks = defaultCallbacks;

            var scope = this;

            $(settings.saveButtonSelector).unbind('click.minder-edit-results').bind('click.minder-edit-results', callBacks.onSave);
            $(settings.cancelButtonSelector).unbind('click.minder-edit-results').bind('click.minder-edit-results', callBacks.onCancel);
            $(settings.addRowButtonSelector).unbind('click.minder-edit-results').bind('click.minder-edit-results', callBacks.onAddRow);
            this.undelegate('input[type=text], select', 'change.minder-edit-results').delegate('input[type=text], select', 'change.minder-edit-results', callBacks.onElementChange);
            this.undelegate('input[type=text]', 'keydown.minder-edit-results').delegate('input[type=text]', 'keydown.minder-edit-results', callBacks.onElementKeydown);
            this.undelegate('input[type=text]', 'keyup.minder-edit-results').delegate('input[type=text]', 'keyup.minder-edit-results', callBacks.onElementKeyup);

            this.data('settings', settings);
            this.data('callbacks', callBacks);

            this.find('[data-number-format]').minderNumberFormat();

            return this;
        },

        'addRow' : function() {
            var downloadContainer = $('#tmp_download_container');
            var scope             = this;
            var settings          = scope.data('settings');

            if (downloadContainer.length < 1) {
                $('body').append('<div id="tmp_download_container" style="display: none;"></div>');
                downloadContainer = $('#tmp_download_container');
            }

            downloadContainer.load(
                settings.addRowUrl,
                {
                    'namespace' : settings.namespace
                },
                function() {
                    var jsonResponse = $.parseJSON(downloadContainer.find('.json').html());
                    downloadContainer.find('.json').remove();

                    if (jsonResponse.errors && jsonResponse.errors.length > 0) {
                        showErrors(jsonResponse.errors);
                        return;
                    }

                    $(settings.modificationFlagSelector).html(' *');

                    $(settings.cancelButtonSelector).removeAttr('disabled');
                    $(settings.saveButtonSelector).removeAttr('disabled');

                    downloadContainer.find('tr').each(function(){
                        var tabId = 'tab_id_' + $(this).attr('tab_id');
                        var insertingRow = this;
                        scope.each(function(){
                            if ($(this).attr('tab_id') == tabId) {
                                $(insertingRow).prependTo($(this).find(settings.tabDatasetSelector));
                            }
                        });
                    });
                }
            );
        },
        
        'save' : function() {
            var dataToSave = [];
            var tmpData    = {};
            var settings   = this.data('settings');
            var callBacks  = this.data('callbacks');

            this.find('input, select').each(function(){
                if ($(this).data('changed') || $(this).attr('is_new') == 'true') {
                    dataToSave.push({
                        'row_id'    : $(this).attr('row-id'),
                        'column_id' : $(this).attr('column-id'),
                        'new_value' : $(this).minderNumberFormat('getValue'),
                        'name'      : $(this).attr('name'),
                        'is_new'    : ($(this).attr('is_new') == 'true')
                    });
                }
            });

            //temporary disable buttons while request performing
            $(settings.cancelButtonSelector).attr('disabled', 'disabled');
            $(settings.saveButtonSelector).attr('disabled', 'disabled');
            $(settings.addRowButtonSelector).attr('disabled', 'disabled');

            $.post(
                settings.saveUrl,
                {
                    'data_to_save' : dataToSave,
                    'namespace'    : settings.namespace
                },
                function(response) {
                    $(settings.addRowButtonSelector).removeAttr('disabled');

                    if (response.errors && response.errors.length > 0 && typeof callBacks.onError == 'function') {
                        callBacks.onError(response);
                        return;
                    }

                    if (typeof callBacks.onSuccess == 'function') {
                        callBacks.onSuccess(response);
                    }
                },
                'json'
            );
        },

        'cancel': function() {
            var settings   = this.data('settings');
            var callBacks  = this.data('callbacks');

            $(settings.modificationFlagSelector).html('');
            this.find('tr').each(function(){

                if ($(this).attr('is_new') === 'true')
                    $(this).remove();
            });


            this.find('input, select').each(function(){
                if ($(this).data('changed')) {
                    $(this).minderNumberFormat('setValue', $(this).attr('original_value'));
                    $(this).removeData('changed');
                }
            });

            $(settings.cancelButtonSelector).attr('disabled', 'disabled');
            $(settings.saveButtonSelector).attr('disabled', 'disabled');

            if (typeof callBacks.afterCancel == 'function') {
                callBacks.afterCancel();
            }
        },

        'isModified': function() {
            var settings   = this.data('settings');
            return $(settings.modificationFlagSelector).html().indexOf('*') > -1;
        }
    };

    $.fn.minderEditResults = function(method, settings, callBacks) {
        if (methods[method]) {
            var args = (arguments.length > 1 ) ? Array.prototype.slice.apply( arguments, [1] ) : null; // second parametr to apply must be an array [1]

            return methods[method].call(this, args);
        }

        return methods.init.apply(this, arguments);
    };
 
})(jQuery);