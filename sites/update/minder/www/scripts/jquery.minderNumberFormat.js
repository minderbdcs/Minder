(function($){
    $.fn.minderNumberFormat = function(method) {
        if (typeof _methods[method] == 'function') {
            return _methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
            return _methods.format.apply(this, arguments);
        }
    };

    var _methods = {
        'format': function(numberFormat) {
            this.unbind('.numberFormat');
            _initHandlers(this, numberFormat);

            this.each(function(){
                _setRawValue($(this), _getRawValue($(this)), numberFormat);
            });

            return this;
        },

        'remove' : function() {
            this.unbind('.numberFormat').each(function(index, element){
                _removeFormat($(this));
            });

            return this;
        },

        'getValue' : function() {
            return _getRawValue($(this));
        },

        'setValue' : function(value, numberFormat) {
            this.unbind('.numberFormat');
            _initHandlers(this, numberFormat);
            this.each(function(){
                _setRawValue($(this), value, numberFormat);
            });

            return this;
        }
    };

    function _initHandlers($elements, numberFormat) {
        $elements
            .bind('focus.numberFormat', function(evt) {
                                                _removeFormat($(this));
                                            })
            .bind('blur.numberFormat', function(evt) {
                                                _setRawValue($(this), _getRawValue($(this)), numberFormat);
                                            });
    }

    function _removeFormat($element) {
        if ($element.attr('value') === undefined)
            $element.html(_getRawValue($element));
        else
            $element.val(_getRawValue($element));

        $element.removeAttr('data-raw-value');
    }

    function _setRawValue($element, value, numberFormat) {
        var formatObject = {};

        //set element value even if no number format given
        //if number format given or element has data-number-format attribute
        //this value will be overwritten with formatted one
        if ($element.attr('value') === undefined)
            $element.html(value);
        else
            $element.val(value);

        numberFormat = numberFormat || $element.attr('data-number-format') || 'not_defined';

        if (numberFormat == 'not_defined')
            return;

        try{
            formatObject = _parseNumberFormat(numberFormat);
        } catch (e) {
            return; //bad number format
        }

        $element.attr('data-raw-value', value);

        if ($element.attr('value') === undefined)
            $element.html(_formatValue(value, formatObject));
        else
            $element.val(_formatValue(value, formatObject));
    }
    
    function _getRawValue($element) {
        if (_valueIsFormatted($element))
            return $element.attr('data-raw-value');

        return $element.attr('value') === undefined ? $element.html() : $element.val();
    }

    function _valueIsFormatted($element) {
        return $element.attr('data-raw-value') !== undefined;
    }

    function _formatElementValue($element, numberFormat) {

    }

    function _formatValue(rawValue, format) {
        rawValue = rawValue || '0';
        var value = Number(rawValue);

        if (isNaN(value))
            return rawValue;

        value = parseFloat(value.toFixed(format.fractionalLength)); //convert to required precision

        var sign = (value < 0 ? '-' : '');
        var valueString = Math.abs(value).toString();
        var parts = valueString.split('.');

        var integralPart = parts[0];
        var fractionalPart = (parts.length > 1 ? parts[1] : '');

        while (fractionalPart.length < format.fractionalStaticLength)
            fractionalPart += '0';

        while (integralPart.length < format.integralStaticLength)
            integralPart = '0' + integralPart;

        if (format.thousandLength > 0) {
            var slicePos = Math.max(0, integralPart.length - format.thousandLength);
            var tmpIntegralPart = integralPart.slice(slicePos);
            integralPart = integralPart.slice(0, slicePos);

            while (integralPart.length > 0) {
                slicePos = Math.max(0, integralPart.length - format.thousandLength);
                tmpIntegralPart = integralPart.slice(slicePos) + ',' + tmpIntegralPart;
                integralPart = integralPart.slice(0, slicePos);
            }

            integralPart = tmpIntegralPart;
        }

        return format.prepend + sign + integralPart + (fractionalPart.length > 0 ? '.' + fractionalPart : '') + format.append;
    }

    function _occurrencesNumber(target, search) {
        var result = 0;
        var pos = target.indexOf(search);

        while (pos > -1) {
            result++;
            pos = target.indexOf(search, pos + 1);
        }

        return result;
    }

    function _parseFractionalPart(part, result) {
        if (part.length < 1) {
            result.fractionalLength = 0;
            result.fractionalStaticLength = 0;
            return result;
        }

        var regExp = /^(0*)(#*)(.*)$/;
        var execResult = regExp.exec(part);

        if (execResult == null)
            throw "Bad number format";

        result.fractionalStaticLength = execResult[1].length;
        result.fractionalLength = result.fractionalStaticLength + execResult[2].length;
        result.append = execResult[3];

        return result;
    }

    function _parseIntegralPart(part, result, hasFractionalPart) {
        if (part.length < 1) {
            result.integralStaticLength = 0;
            result.thousandLength = 0;
            return result;
        }

        var thousandSeparatorIndex = part.lastIndexOf(',');

        if (thousandSeparatorIndex > -1) {
            result.thousandLength = part.length - thousandSeparatorIndex - 1;
        } else {
            result.thousandLength = 0;
        }

        while (part.indexOf(',') > -1)
            part = part.replace(',', '');

        var indexOf0 = part.indexOf('0');
        indexOf0 = (indexOf0 < 0) ? part.length : indexOf0;
        var indexOfSharp = part.indexOf('#');
        indexOfSharp = (indexOfSharp < 0) ? part.length : indexOfSharp;

        var prependLength = Math.min(indexOf0, indexOfSharp);

        result.prepend = part.slice(0, prependLength);
        part = part.slice(prependLength);

        var regExp = hasFractionalPart ? /^(#*)(0*)$/ : /^(#*)(0*)(.*)$/;
        var execResult = regExp.exec(part);

        if (execResult == null)
            throw "Bad number format";

        result.integralStaticLength = execResult[2].length;
        result.append = hasFractionalPart ? result.append : execResult[3];

        return result;
    }

    function _parseNumberFormat(format) {
        format = String(format || '');

        if (_occurrencesNumber(format, '.') > 1)
            throw "Bad number format";

        var result = {
            prepend: '',
            append: '',
            integralStaticLength: 0,
            fractionalLength: 0,
            fractionalStaticLength: 0,
            thousandLength: 0
        };

        var formatParts = format.split('.');
        var hasFractionalPart = (formatParts.length > 1);

        result = _parseIntegralPart(formatParts[0], result, hasFractionalPart);

        if (hasFractionalPart)
            result = _parseFractionalPart(formatParts[1], result);
        else
            result = _parseFractionalPart('', result);

        return result;
    }

})(jQuery);