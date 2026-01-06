function pageLoaded()
{
}

function showHideLeft(url)
{
    var left = $('#left');
    var page = $('#page');
    
    url = (url) ? url : '/user/ajax-left-pannel-state';
    
    var pannelState = (left.css('display') == 'none')?'hide':'show';
    
    $.getJSON(url, {pannelAction: 'switch', state: pannelState}, function(json) {
        
        if (json.state == 'show') {
            left.css('display', 'block');
            page.css('margin-left', 190);
        } else {
            left.css('display', 'none');
            page.css('margin-left', 10);
        }
    });
    
//    if (left.css('display') == 'none') {
//        left.css('display', 'block');
//        page.css('margin-left', 190);
//    } else {
//        left.css('display', 'none');
//        page.css('margin-left', 10);
//    }
}

function tabCtrlChangePage(tabctrl, page, tab) {
/*
    var pages = document.getElementsByClassName('page-selected', $(tabctrl));
    pages.each(function(n) {
        n.className = 'page';
        });
    var tabs = $(tabctrl).getElementsByTagName('li');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].className = '';
    }
    $(page).className = 'page-selected';
    tab.className = 'selected';
*/
}

function pullUpDatePicker() {
    var
        datePickerDiv = $('#datepicker_div'),
        zIndex = parseInt($(this).parents(".ui-dialog").css('z-index') || datePickerDiv.css('z-index')) || 1000;

    datePickerDiv.css('z-index', zIndex+2);

    return {};
}

function minderNamespace(namespace, defineCallback) {
    function getOrDefineNamespace(namespace) {
        var
            path = namespace.split('.'),
            base = window,
            next;

        while (path.length > 0) {
            next = path.shift();

            base[next] = base[next] || {};
            base = base[next];
        }

        return base;
    }

    defineCallback.call(getOrDefineNamespace(namespace));
}

function getTopMostDialogZIndex() {
    var result = 0;

    $('.ui-dialog').filter(':visible').each(function(){
        result = Math.max(parseInt($(this).css('z-index')), result);
    });

    return result;
}

Mdr = {
    and: function() {
        var predicates = Array.prototype.slice.call(arguments);

        return function() {
            var args = Array.prototype.slice.call(arguments);

            for (var index = 0; index < predicates.length; index++) {
                if (predicates[index].apply(this, args) === false) {
                    return false;
                }
            }
            return true;
        };
    },

    or: function() {
        var predicates = Array.prototype.slice.call(arguments);

        return function() {
            var args = Array.prototype.slice.call(arguments);

            for (var index = 0; index < predicates.length; index++) {
                if (predicates[index].apply(this, args) === true) {
                    return true;
                }
            }
            return false;
        };
    },

    not: function(predicate) {
        return function() {
            return !predicate.apply(this, Array.prototype.slice.call(arguments));
        };
    },

    notOr: function() {
        return Mdr.not(Mdr.or.apply(this, Array.prototype.slice.call(arguments)));
    },

    logResult: function(predicate, message) {
        var log = [message || 'result'];
        return function() {
            var args = Array.prototype.slice.call(arguments),
                result = predicate.apply(this, args);
            log.push.apply(log, args);
            log.push(result);
            console.log(log);
            return result;
        }
    },

    jqAttr: function(attribute) {
        return function(){
            return $(this).attr(attribute);
        };
    },

    /**
     *
     * @param {Array} source
     * @param {Object} dimensions
     */
    slice: function(source, dimensions) {
        var dimension;

        return source.filter(function(sourceEntry) {
            for (dimension in dimensions) {
                if (dimensions.hasOwnProperty(dimension)) {
                    if (dimensions[dimension] != sourceEntry[dimension]) {
                        return false;
                    }
                }
            }

            return true;
        });
    },

    /**
     *
     * @param {Array} source
     * @param {string} keyColumnName
     * @returns {Array}
     */
    unique: function(source, keyColumnName) {
        var checked = {};

        return source.filter(function(sourceEntry) {
            if (!sourceEntry[keyColumnName]) {
                return false;
            }

            if (checked[sourceEntry[keyColumnName]]) {
                return false;
            } else {
                checked[sourceEntry[keyColumnName]] = true;
                return true;
            }
        });
    },

    /**
     *
     * @param {Array} source
     * @param {string} column
     */
    sortBy: function(source, column) {
        source.sort(function(a, b){
            if (a[column] === b[column]) {
                return 0;
            } else if (a[column] > b[column]) {
                return 1;
            } else {
                return -1;
            }
        });

        return source;
    }
};

Mdr.ProcessResult = function() {
    this.messages = [];
    this.warnings = [];
    this.errors   = [];

    return this;
};

Mdr.ProcessResult.prototype.addMessages = function(messages) {
    this.messages.push.apply(this.messages, messages);
};

Mdr.ProcessResult.prototype.addWarnings = function(warnings) {
    this.warnings.push.apply(this.warnings, warnings);
};

Mdr.ProcessResult.prototype.addErrors = function(errors) {
    this.errors.push.apply(this.errors, errors);
};

Mdr.ProcessResult.prototype.hasErrors = function() {
    return this.errors && this.errors.length > 0;
};

Mdr.ProcessResult.prototype.hasWarnings = function() {
    return this.warnings && this.warnings.length > 0;
};

Mdr.ProcessResult.prototype.hasMessages = function() {
    return this.messages && this.messages.length > 0;
};

/**
 *
 * @param {Array} data
 * @constructor
 */
Mdr.OptionsDataSource = function(data) {
    this.source = data;
    this.filter = {};
    this.filteredData = null;
};

/**
 *
 * @param {Object} filter
 */
Mdr.OptionsDataSource.prototype.setFilter = function(filter) {
    this.filter = filter;
    this.filteredData = null;
};

Mdr.OptionsDataSource.prototype.getFilteredData = function() {
    if (this.filteredData === null) {
        this.filteredData = Mdr.slice(this.source, this.filter);
    }

    return this.filteredData;
};

Mdr.OptionsDataSource.prototype.getColumn = function(columnName, unique) {
    unique = !!unique;

    return ((!!unique) ? Mdr.unique(this.getFilteredData(), columnName) : this.getFilteredData()).map(function(dataRow){
        return dataRow[columnName];
    });
};

Mdr.OptionsDataSource.prototype.fillOptions = function(domSelect, settings) {
    var  i = 0;

    settings = $.extend({
        valueColumn: 'value',
        labelColumn: 'label',
        addEmpty: true,
        sortBy: 'label'
    }, settings);

    if (settings.addEmpty) {
        domSelect.options[i++] = new Option('', '');
    }

    Mdr.sortBy(Mdr.unique(this.getFilteredData(), settings.valueColumn), settings.sortBy).forEach(function(data){
        domSelect.options[i++] = new Option(data[settings.labelColumn], data[settings.valueColumn]);
    });
};

Mdr.OptionsDataSource.prototype.disableOptions = function($container, valueColumn) {
    var validValues = ssnSubTypes.getColumn(valueColumn);
    $container.find('option').each(function(){
        var $this = $(this);
        if ($this.val() && (validValues.indexOf($this.val()) < 0)) {
            $this.attr('disabled', 'disabled');
        }
    });
};
