(function($) {
    
    $.fn.minderFilteredDD = function(settings) {
    };

    var _fetchOptionsFromDropDown = function($element) {
        var
            result = [];

        $element.filter(':first').find('option').each(function(){
            var option = {},
                attributes = this.attributes,
                attributesLength = attributes.length,
                index,
                attributeName;

            for (index = 0; index < attributesLength; index++) {
                attributeName = attributes[index].name.toUpperCase();

                if (attributeName != 'SELECTED') {
                    option[attributeName] = attributes[index].value;
                }
            }

            result.push(option);
        });

        return result;
    };

    var _getOptions = function($element) {
        var
            options = $element.data('options');

        if (options == null) {
            options = _fetchOptionsFromDropDown($element);
        }

        $element.data('options', options);

        return options;
    };

    var _getFilterHelper = function(filters) {
        return function(option, index){
            var
                filterIndex;

            for (filterIndex in filters) {
                if (filters.hasOwnProperty(filterIndex) && option.hasOwnProperty(filterIndex)) {
                    if (filters[filterIndex] != option[filterIndex])
                        return false;
                }
            }

            return true;
        }
    };

    var _getFilteredOptions = function($element, filtersOrCallback) {
        return _getOptions($element).filter($.isFunction(filtersOrCallback) ? filtersOrCallback : _getFilterHelper(filtersOrCallback));
    };

    function _toDomOption(option) {
        var result = new Option(option.label || option.LABEL),
            index;

        for (index in option) {
            if (option.hasOwnProperty(index))
                result.setAttribute(index, option[index]);
        }

        return result;
    }

    var _renderOptions = function($element, options, done) {
        var
            optionsPart,
            renderPartialCallback,
            offset = 0,
            window = 500;
        optionsPart = options.slice(offset, offset + window);

        renderPartialCallback = function() {

            $element.get().forEach(function(select){
                var index = offset;

                optionsPart.forEach(function(option){
                    select.options[index++] = _toDomOption(option);
                });
            });

            offset += window;
            optionsPart = options.slice(offset, offset + window);

            if (optionsPart.length > 0) {
                setTimeout(renderPartialCallback, 0);
            } else {
                if (typeof done === 'function')
                    done();
            }
        };

        if (optionsPart.length > 0) {
            setTimeout(renderPartialCallback, 0);
        } else {
            if (typeof done === 'function')
                done();
        }
    };
    
    $.fn.minderFilteredDDSetFilter = function(filtersOrCallback) {
        var
            deferred = $.Deferred(),
            $_this = $(this),
            options = _getFilteredOptions($_this, filtersOrCallback);

        $_this.empty();

        _renderOptions(
            $_this,
            options,
            function(){
                deferred.resolve($_this);
            }
        );

        return deferred.promise();
    };

    $.fn.minderFilteredDDGetFiltered = function(filtersOrCallback) {
        return _getFilteredOptions($(this), filtersOrCallback);
    };
})(jQuery);
