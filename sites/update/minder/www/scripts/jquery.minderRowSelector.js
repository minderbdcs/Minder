/**
* Row selector plugin for Minder project
*/

/**
 * Requirements:
 * - jQuery (John Resig, http://www.jquery.com/)
 **/

(function($) {
    $.fn.minderRowSelector = function(url, settings, callBacks) {
        var defaultSettings = {
            'selectionNamespace'   : 'row-selection-namespace',
            'selectionAction'      : 'select-row',
            'selectionController'  : '',
            
            'showBySelector'       : '#show_by',
            'pageselectorSelector' : '#pageselector',
            
            'selectedCountClass'   : 'selected_count',
            'totalCountClass'      : 'total_count',
            'selectAllRowsClass'   : 'select_all_rows',
            'selectCompleteClass'  : 'select_complete',
            'rowSelectorClass'     : 'row_selector',
            'switchSelectionClass' : 'switch_selection',
            
            'rowIdAttrName'        : 'row_id',
            
            'scopeSelector'        : ''
        };
        
        var defaultCallbacks = {
            'beforeLoad' : function() {
                return true;
            },
            'onError'    : function(response) {
                if (response.errors && response.errors.length > 0)
                    showErrors(response.errors);
            },
            'onSuccess'  : function(response) {
                if (response.warnings && response.warnings.length > 0)
                    showWarnings(response.warnings);

                if (response.messages && response.messages.length > 0)
                    showMessage(response.messages);
            }
        };
        
        if (settings) $.extend(defaultSettings, settings);
        if (callBacks) $.extend(defaultCallbacks, callBacks);
        settings  = defaultSettings;
        callBacks = defaultCallbacks;

        $(settings.scopeSelector + ' ' + '.' + settings.switchSelectionClass + '.' + settings.selectionNamespace).unbind('.minder-row-selector').bind('click.minder-row-selector', function(evt){
//            evt.preventDefault();
            
            var currentSelectionMode    = $(this).attr('selection_mode');
            var selectionSwitchers      = $(settings.scopeSelector + ' ' + '.' + settings.switchSelectionClass + '.' + settings.selectionNamespace);
            var selectAllRowsSelectors  = $(settings.scopeSelector + ' ' + '.' + settings.selectAllRowsClass + '.' + settings.selectionNamespace)
            var selectCompleteSelectors = $(settings.scopeSelector + ' ' + '.' + settings.selectCompleteClass + '.' + settings.selectionNamespace)
            
            if (currentSelectionMode == 'all') {
                selectionSwitchers.attr('checked', 'checked');
                selectionSwitchers.attr('selection_mode', 'one');
                selectAllRowsSelectors.attr('disabled', 'disabled');
                selectCompleteSelectors.attr('disabled', 'disabled');
            } else {
                selectionSwitchers.removeAttr('checked');
                selectionSwitchers.attr('selection_mode', 'all');
                selectAllRowsSelectors.removeAttr('disabled', 'disabled');
                selectCompleteSelectors.removeAttr('disabled', 'disabled');
            }
        });

        this.unbind('.minder-row-selector').bind('click.minder-row-selector', function(evt){
            evt.preventDefault();
            
            if (callBacks.beforeLoad && !callBacks.beforeLoad()) {
                return;
            }
            
            var rowId         = $(this).attr(settings.rowIdAttrName);
            var state         = $(this).attr('checked');
            var showBy        = $(settings.scopeSelector + ' ' + settings.showBySelector).val();
            var pageselector  = $(settings.scopeSelector + ' ' + settings.pageselectorSelector).val();
            var selectionMode = $(settings.scopeSelector + ' ' + '.' + settings.switchSelectionClass + '.' + settings.selectionNamespace).attr('selection_mode');
        
            $.getJSON(
                url,
                {
                    row_id: rowId, 
                    state: state, 
                    pageselector: pageselector, 
                    show_by: showBy,
                    selection_mode: selectionMode,
                    selection_namespace: settings.selectionNamespace,
                    selection_action: settings.selectionAction,
                    selection_controller: settings.selectionController
                },
                function (response) {
                    if (response.errors && response.errors.length > 0 && callBacks.onError) {
                        callBacks.onError(response);
                        return;
                    }
                    
                    $(settings.scopeSelector + ' ' + '.' + settings.selectedCountClass + '.' + response.selectionNamespace).html(response.selected);
        
                    var rowSelectors   = $(settings.scopeSelector + ' ' + '.' + settings.rowSelectorClass + '.' + response.selectionNamespace);
                    var totalOnPage    = rowSelectors.length;
                    var selectedOnPage = 0;

                    rowSelectors.each(function() {
                        var tmpRowId = $(this).attr(settings.rowIdAttrName);
                        if (response.selectedRows[tmpRowId]) {
                            $(this).attr('checked', 'checked');
                            selectedOnPage++;
                        } else {
                            $(this).removeAttr('checked');
                        }
                    });
        
                    if (totalOnPage == selectedOnPage) {
                        $(settings.scopeSelector + ' ' + '.' + settings.selectAllRowsClass + '.' + response.selectionNamespace).attr('checked', 'checked');
                    } else {
                        $(settings.scopeSelector + ' ' + '.' + settings.selectAllRowsClass + '.' + response.selectionNamespace).removeAttr('checked');
                    }
        
                    var totalRows = parseInt($(settings.scopeSelector + ' ' + '.' + settings.totalCountClass + '.' + response.selectionNamespace).html());
                    totalRows = (isNaN(totalRows)) ? 0 : totalRows;
        
                    if (totalRows == response.selected) {
                        $(settings.scopeSelector + ' ' + '.' + settings.selectCompleteClass + '.' + response.selectionNamespace).attr('checked', 'checked');
                    } else {
                        $(settings.scopeSelector + ' ' + '.' + settings.selectCompleteClass + '.' + response.selectionNamespace).removeAttr('checked');
                    }
                    
                    if (callBacks.onSuccess) {
                        callBacks.onSuccess(response);
                    }
                }
            );
        });
        
        return this;
    };
 
})(jQuery);