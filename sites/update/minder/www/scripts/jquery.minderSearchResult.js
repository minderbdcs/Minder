(function($) {
    
    $.minderSearchResultDump = function(expr){
        if(typeof console !== 'undefined' && typeof console.debug == 'function') console.debug(expr);
        return '';
    };

    $.minderSearchResultCommon = {
        'buildPagesRange'  : buildPagesRange,
        'buildShowByRange' : buildShowByRange,
        'getFields'        : getFields
    };

    function buildPagesRange(pagesCount) {
        var range = [];
        for (iterator = 0; iterator < pagesCount; iterator++)
            range.push(iterator);
        return range;
    };
    
    function buildShowByRange() {
        return [5, 10, 15, 20, 30, 40, 50, 100, 200, 500, 1000];
    }
    
    function getFields(screen, tabId) {
        var fields = [];
        for (iterator in screen.fields) {
            if (screen.fields[iterator].SSV_TAB == tabId || screen.fields[iterator].SSV_TAB == '' || screen.fields[iterator].SSV_TAB == null) {
                fields.push(screen.fields[iterator]);
            }
        }
        
        fields.sort(function(a, b){return a.SSV_SEQUENCE - b.SSV_SEQUENCE;});
        
        return fields;
    };
    
    function selectRowOnClick(evt) {
        evt.preventDefault();
        
        var selectRowEvent = $.Event('select-row');
        selectRowEvent.rowId     = $(this).attr('row_id');
        selectRowEvent.rowState  = $(this).attr('checked');
        selectRowEvent.namespace = $(this).attr('selection_namespace');
        
        $(this).parents('[tab-container]').trigger(selectRowEvent);
    };
    
    function switchSelectionOnClick(evt) {
//        evt.preventDefault();

        var currentMode = $(this).attr('selection_mode');
        var newMode     = 'all';
        var selectionNamespace = $(this).attr('selection_namespace');

        if (currentMode == 'all') {
            $('input.switch_selection.' + selectionNamespace).attr('selection_mode', 'one').attr('checked', true);
            $('input.select_all_rows.' + selectionNamespace).attr('disabled', 'disabled');
            $('input.select_complete.' + selectionNamespace).attr('disabled', 'disabled');
        } else {
            $('input.switch_selection.' + selectionNamespace).attr('selection_mode', 'all').removeAttr('checked');
            $('input.select_all_rows.' + selectionNamespace).removeAttr('disabled');
            $('input.select_complete.' + selectionNamespace).removeAttr('disabled');
        }
    }
    
    function pageselectorOnChange(evt) {
        var pageChangedEvent = $.Event('page-changed'),
            $container = $(this).parents('[tab-container]'),
            settings = $container.data('search-result-description');
        pageChangedEvent.pageNo = $(this).val();
        pageChangedEvent.screenName = settings.sysScreenName;
        $container.find('select.pageselector').val(pageChangedEvent.pageNo);
        $container.trigger(pageChangedEvent);
    }
    
    function showByOnChange(evt) {
        var showByChangedEvent = $.Event('show-by-changed'),
            $container = $(this).parents('[tab-container]'),
            settings = $container.data('search-result-description');
        showByChangedEvent.showBy = $(this).val();
        showByChangedEvent.screenName = settings.sysScreenName;
        $container.find('select.show_by').val(showByChangedEvent.showBy);
        $container.trigger(showByChangedEvent);
    }

    function onSortStart(evt) {
        $(this).trigger($.Event('pre-sort'));
    }

    function onSortEnd(evt) {
        var
            tabId = $(evt.target).attr('tab_id').split('-')[2],
            screen = $(this).data('search-result-description'),
            sortList = $(evt.target).data('tablesorter').sortList,
            fields = getFields(screen, tabId),
            sortOrders = ['asc', 'desc'],
            sortFields = [],
            iterator,
            sortEvent = $.Event('sort');

        for (iterator = 0; iterator < sortList.length; iterator++) {
            sortFields.push({
                'sortField': fields[sortList[iterator][0] - 1],
                'sortOrder': sortOrders[sortList[iterator][1]]
            });
        }

        screen.paginator.sortTabId  = $(evt.target).attr('tab_id');
        screen.paginator.sortFields = sortFields;
        screen.paginator.sortList   = sortList;
        $(this).data('search-result-description', screen);

        sortEvent.screen = screen;
        $(this).trigger(sortEvent);
    }
    
    $.fn.minderSearchResult = function(settings) {
        var defaultSettings = {
            'templateId' : 'tab-container-tmpl',
            'autorender' : true,
            'autotabs'   : true,
            'autosorter' : false,
            "customSort" : false,

            'tabContainerId' : 'search-result-tabs',
            'usePagination' : true,
            'paginator' : {
                'totalRows'     : 0,
                'pages'         : 0,
                'maxPages'      : 0,
                'selectedPage'  : 0,
                'showBy'        : 5,
                'selectedRows'  : 0,
                'selectionMode' : 'all',
                'shownFrom'     : 0,
                'shownTill'     : 0,
                'sortTabId'     : '',
                'sortFields'    : [],
                'sortList'      : []
            },

            'sysScreenName' : '',
            'sysScreenCaption' : '',

            'canSelect' : true,
            'hasHeader'  : true,
            'hasFooter'  : true,
            'hasButtons' : true,
            'autoTablesorter' : true,
            
            'selectionNamespace' : 'search_results',
            'tabs' : [
            ],
            'fields' : [
            ],
            'dataset' : [
            ],
            'buttons' : [
            ]
        };
        
        var savedData = $(this).data('search-result-description');
        $.extend(defaultSettings, savedData);
        
        if (settings)
            $.extend(defaultSettings, settings);
            
        settings = defaultSettings;
        $(this).data('search-result-description', settings);
        $(this).data('search-result-registered-dp-fields', {});
        
        $(this).attr('tab-container', 'tab-container');
        
        if (settings.autorender) {
            $(this).minderSearchResultRender();
        }
    };
    
    $.fn.minderSearchResultRender = function() {
        var srDescription = $(this).data('search-result-description');
        
        var $_this = $(this);
        var selectedTabId = null;
        var afterRenderEvent = $.Event('after-render');
        afterRenderEvent.sysScreen = srDescription;

        if (srDescription.autotabs) {
            var $_tabsUl = $_this.find('#' + srDescription.tabContainerId + ' > ul');
            
            if ($_tabsUl.length > 0 && $.data($_tabsUl[0], 'tabs')) {
                 selectedTabId = $_this.find('.ui-tabs-selected').attr('tab_id');
            }
            
        }

        $_this.empty();
        $('#' + srDescription.templateId).tmpl(
            srDescription,
            {
                'commonMethods' : {
                    'buildPagesRange'  : buildPagesRange,
                    'buildShowByRange' : buildShowByRange,
                    'getFields'        : getFields
                }
            }
        ).appendTo(this);
        $_this.trigger(afterRenderEvent);
        
        $_this.find('input[sub-type=DP]').datepicker({dateFormat: "yy-mm-dd"});
        
        if (srDescription.autotabs) {
            $_this.find('#' + srDescription.tabContainerId + ' > ul').tabs();
            if (selectedTabId) {
                $_this.find('#' + srDescription.tabContainerId + ' > ul').tabs('select', selectedTabId);
            }
        }

        if (srDescription.autosorter) {
            $_this.find('.data-table-' + srDescription.sysScreenName).each(function() {
                var
                    $this = $(this),
                    tablesorter = {headers:{0:{sorter:false}}, widgets: ['zebra']},
                    css,
                    order;

                if ($this.attr('tab_id') == srDescription.paginator.sortTabId) {
                    if (srDescription.customSort) {
                        tablesorter.appender = function(){};
                    }

                    $(this).tablesorter(tablesorter);

                    if (srDescription.customSort) {
                        tablesorter = $this.data('tablesorter');

                        tablesorter.sortList = srDescription.paginator.sortList;
                        css = {'desc' : tablesorter.cssAsc, 'asc': tablesorter.cssDesc};
                        order = {'desc' : 1, 'asc': 0};

                        for (var index = 0; index < srDescription.paginator.sortFields.length; index++) {
                            $this.find('.header[column-id="' + srDescription.paginator.sortFields[index].sortField.RECORD_ID + '"]').each(function() {
                                this.order = order[srDescription.paginator.sortFields[index].sortOrder];
                                this.count = this.order + 1;
                                $(this).addClass(css[srDescription.paginator.sortFields[index].sortOrder]);
                            });
                        }

                        $this.data('tablesorter', tablesorter);
                    }
                } else {
                    $(this).tablesorter({headers:{0:{sorter:false}}, widgets: ['zebra']});
                }
            });
        }
        
        $_this.find('input.row_selector[type=checkbox]').unbind('click.search-result').bind('click.search-result', selectRowOnClick);
        $_this.find('input.select_all_rows[type=checkbox]').unbind('click.search-result').bind('click.search-result', selectRowOnClick);
        $_this.find('input.select_complete[type=checkbox]').unbind('click.search-result').bind('click.search-result', selectRowOnClick);
        $_this.find('input.switch_selection').unbind('click.search-result').bind('click.search-result', switchSelectionOnClick);
        
        $_this.find('select.pageselector').unbind('change.search-result').bind('change.search-result', pageselectorOnChange);
        $_this.find('select.show_by').unbind('change.search-result').bind('change.search-result', showByOnChange);

        $_this.undelegate('.tablesorter', 'sortStart').delegate('.tablesorter', 'sortStart', $.proxy(onSortStart, $_this));
        $_this.undelegate('.tablesorter', 'sortEnd').delegate('.tablesorter', 'sortEnd', $.proxy(onSortEnd, $_this));

        $.each(srDescription.buttons, function(index, button){
            if (button.SSB_ACTION && button.SSB_ACTION.length > 0) {
                var buttonSelector = '.SCREEN_BUTTON_' + button.RECORD_ID;
                $(buttonSelector).unbind('click.search-result').bind('click.search-result', function(evt){eval(button.SSB_ACTION);});
            }
        });
    };
    
    $.fn.minderSearchGetPaginatorState = function(){
        var srDescription = $(this).data('search-result-description');
        var paginator = srDescription.paginator;
        
        paginator.showBy        = $(this).find('select.show_by').val();
        paginator.selectedPage  = $(this).find('select.pageselector').val();
        paginator.selectionMode = $(this).find('input.switch_selection').attr('selection_mode');

        return paginator;
    };

    $.fn.minderSearchResultButton = function(attribute, value) {
        var
            buttons = [];

        $(this).each(function(){
            var
                $this = $(this),
                $container = $this.parents('[tab-container]'),
                srDescription = $container.data('search-result-description'),
                name = $this.attr('name').toUpperCase();

            buttons.push.apply(buttons, srDescription.buttons.filter(function(button) {return button['SSB_BUTTON_NAME'] === name}));
        });

        if (undefined !== value) {
            buttons.forEach(function(button) {
                button[attribute] = value;
            });
        }

        return buttons.map(function(button) {
            return button[attribute];
        });
    };
})(jQuery);