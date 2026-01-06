/**
* Plugin for Minder project add barcode checking ability for Input
*/

/**
 * Requirements:
 * - jQuery (John Resig, http://www.jquery.com/)
 **/

(function($) {

    function initBarcodeDescriptior(descriptor) {
        descriptor.prefixTests        = [];
        descriptor.validWithoutPrefix = false;

        try {
            if ((descriptor.PREFIX_ARRAY.length < 1) && (descriptor.SYMBOLOGY_PREFIX.length > 0)) {
                tmpPrefixTest = {
                    len: descriptor.SYMBOLOGY_PREFIX.length,
//                    regExp: new RegExp('^(' + descriptor.SYMBOLOGY_PREFIX + ')'), //not using RegExp for testing prefixes, so comment this
                    prefix: descriptor.SYMBOLOGY_PREFIX
                };

                descriptor.prefixTests.push(tmpPrefixTest);
            }

            for (prefixId in descriptor.PREFIX_ARRAY) {
                tmpPrefixTest = {len: descriptor.PREFIX_ARRAY[prefixId].length, regExp: /(.*)/, prefix: ''};

                if (tmpPrefixTest.len > 0) {
//                    tmpPrefixTest.regExp = new RegExp('^(' + descriptor.PREFIX_ARRAY[prefixId] + ')'); //not using RegExp for testing prefixes, so comment this
                    tmpPrefixTest.prefix = descriptor.PREFIX_ARRAY[prefixId];
                } else {
                    descriptor.validWithoutPrefix = true;
                }
                descriptor.prefixTests.push(tmpPrefixTest);
            }
        } catch (exception) {
            throw 'Bad SYMBOLOGY_PREFIX "' + descriptor.SYMBOLOGY_PREFIX + '"';
        }

        descriptor.dataRegExp = /(.*)/;

        try {
            if (descriptor.DATA_EXPRESSION.length > 0)
                descriptor.dataRegExp = new RegExp('^(' + descriptor.DATA_EXPRESSION + ')');
        } catch (exception) {
            throw 'Bad DATA_EXPRESSION "' + descriptor.DATA_EXPRESSION + '"';
        }

        return descriptor;
    }

    function initBarcodeDescriptors(descriptors) {
        var tmpPrefixTest;
        var result = [];
        for (var descriptorId in descriptors) {
            try {
                result.push(initBarcodeDescriptior(descriptors[descriptorId]));
            } catch (exceptionMessage) {
                showWarnings(['Data Identifier "' + descriptors[descriptorId].DATA_ID + '" has wrong description and will be ignored: ' + exceptionMessage], 20000);
            }
        }
        
        return result;
    }
    
    function nextValidPrefix(prefixes, rawValue) {
        var tmpNextPrefix = null;
        while (prefixes.length > 0) {
            tmpNextPrefix = prefixes.shift();
            
            if (tmpNextPrefix.len < 1 || rawValue.substr(0, tmpNextPrefix.len) === tmpNextPrefix.prefix) {
                return tmpNextPrefix;
            }
        }
        
        return false;
    }
    
    $.fn.minderBarcodeInput = function(settings, callBacks) {
        var defaultSettings = {
            'barcodeDescriptors' : [],
            'isUppercase' : false, // flag for charcase
            'onFocusOut' : true //parse data on focus out
        };

        var elementTransformations = this.attr('data-transformations');

        if (typeof elementTransformations != 'undefined') {
            defaultSettings.isUppercase = (elementTransformations.search(/uppercase/i) != -1);
        }

        var savedSettings = this.data('settings');
        $.extend(defaultSettings, savedSettings);
        
        var defaultCallbacks = {
            
            'onKeyUp'     : function(evt) {
                if (evt.keyCode == 13) {

                     if($('#barcode').val()!=']A0CHECKED'){
                            $('#barcode_value').val( $('#barcode').val() );
                    }
                    $(this).minderCheckBarcodeAsync();
                }
            },

            'onKeyDown'    : function(evt) {
                if (evt.keyCode == 13) {

                    evt.preventDefault();
                }
            },
            
            'onParseError' : function(evt) {
                //do nothing, should override
            },
            
            'onParseSuccess' : function(evt) {
                //do nothing, should override
            },

            'onFocusOut'     : function(evt) {
                if ($(this).val() !== "") {     

                    $(this).minderCheckBarcodeAsync();
                }
            }
            
        };
        
        if (settings) {
            var tmpBarcodeDescriptor = defaultSettings.barcodeDescriptors;
            
            if (settings.barcodeDescriptors)
                $.merge(tmpBarcodeDescriptor, settings.barcodeDescriptors);
            
            $.extend(defaultSettings, settings);
            
            defaultSettings.barcodeDescriptors = tmpBarcodeDescriptor;
        }
        
        if (callBacks) $.extend(defaultCallbacks, callBacks);
        settings  = defaultSettings;
        callBacks = defaultCallbacks;
        
        settings.barcodeDescriptors = initBarcodeDescriptors(settings.barcodeDescriptors);
        
        this.data('settings', settings);
        
        this.unbind('keyup.minder-barcode-input'). bind('keyup.minder-barcode-input', callBacks.onKeyUp);
        this.unbind('keydown.minder-barcode-input'). bind('keydown.minder-barcode-input', callBacks.onKeyDown);
        this.unbind('parse-error.minder-barcode-input'). bind('parse-error.minder-barcode-input', callBacks.onParseError);
        this.unbind('parse-success.minder-barcode-input'). bind('parse-success.minder-barcode-input', callBacks.onParseSuccess);
        if (settings.onFocusOut) {
            this.unbind('focusout.minder-barcode-input'). bind('focusout.minder-barcode-input', callBacks.onFocusOut);
        }

        return this;
    };

    function preparePrefixes(barcodeDesc) {
        var tmpTestsArray = [], //array of tests in descending by length order
            tmpTestsLists = [], //list of descriptors grouped by valid prefix
            iterator, iterator2;

        for (iterator in barcodeDesc) {
            for (iterator2 in barcodeDesc[iterator].prefixTests) {
                if (!(barcodeDesc[iterator].prefixTests[iterator2].prefix in tmpTestsLists)) {
                    tmpTestsArray.push(barcodeDesc[iterator].prefixTests[iterator2]);
                    tmpTestsLists[barcodeDesc[iterator].prefixTests[iterator2].prefix] = [];
                }
                tmpTestsLists[barcodeDesc[iterator].prefixTests[iterator2].prefix].push(barcodeDesc[iterator]);
            }
        }

        tmpTestsArray.sort(function(a, b){return b.len - a.len;});

        return {'testArray': tmpTestsArray, 'testMap': tmpTestsLists};
    }

    function getNewParamDescription(rawValue, inputValue) {
        return {param_name: '', param_type: '', param_raw_value: rawValue, param_input_value: inputValue, param_filtered_value: '', param_prefix: '', valid: false};
    }

    function testPrefix(prefixDesc, rawValue, inputValue, foundDesc, getFirst) {
        var filteredValue = rawValue.slice(prefixDesc.len),
            iterator,
            paramDescription = getNewParamDescription(rawValue, inputValue),
            result = [];

        foundDesc.sort(function(a, b){return b.MAX_LENGTH - a.MAX_LENGTH;});

        for (iterator in foundDesc) {
            if (foundDesc[iterator].FIXED_LENGTH.toUpperCase() == 'T') {
                if (foundDesc[iterator].MAX_LENGTH != filteredValue.length)
                //length doesn't match try next PARAM
                    continue;
            } else {
                if ((foundDesc[iterator].MAX_LENGTH > 0) && (foundDesc[iterator].MAX_LENGTH < filteredValue.length))
                //length is greater then allowed length try next PARAM
                    continue;
            }

            if (foundDesc[iterator].dataRegExp.test(filteredValue)) {
                paramDescription = {
                    param_name: foundDesc[iterator].DATA_ID,
                    param_type: foundDesc[iterator].DATA_TYPE_ID,
                    param_raw_value: rawValue,
                    param_input_value: inputValue,
                    param_filtered_value: filteredValue,
                    param_prefix: prefixDesc.prefix,
                    valid: true
                };

                if (getFirst) {
                    return paramDescription;
                } else {
                    result.push(paramDescription);
                }
            }
        }

        return getFirst ? paramDescription : result;
    }
    
    $.fn.minderCheckBarcode = function() {

        var settings      = this.data('settings');
        var inputValue    = this.val();

/****************************************************************/
if($('#urlpickorder').length && $('#urlpickorder').val().length){
			var urlpath = document.getElementById('urlpickorder').value;

		// ajax to pass pickorder value

			$.ajax({
				data: 'pickorder='+inputValue,
				type: 'POST',
				url: urlpath,
 				success: function(data){
					//alert(data);
    				},
				error: function(){
					alert("error");
				}

			});
}
/****************************************************************/

        var rawValue      = inputValue.replace(/\s+/, '');

        if (settings.isUppercase) {
            rawValue = rawValue.toUpperCase();
        }

        var tmpPrefixes   = preparePrefixes(settings.barcodeDescriptors);
        var paramDescription = getNewParamDescription(rawValue, inputValue);
        var prefixDesc;
        
        while ((prefixDesc = nextValidPrefix(tmpPrefixes.testArray, rawValue)) !== false) {
            paramDescription = testPrefix(prefixDesc, rawValue, inputValue, tmpPrefixes.testMap[prefixDesc.prefix], true);

            if (paramDescription.valid)
                break;
        }

        var result = {
            'error' : false,
            'errorCode'  : '',
            'errorMsg'   : '',
            'prefixDesc' : prefixDesc,
            'paramDesc'  : paramDescription
        };
        
        switch (true) {
            case !paramDescription.valid:
                //no param desc found
                result.error  = true;
                result.errorMsg = 'Global Input = "' + rawValue + '". No Match Found in PARAM Table.';
                result.errorCode = 'BAD_PARAM';
                break;
            default:
                result.paramDesc  = paramDescription;
        }
        return result;
    };

    $.fn.minderGetAllValidParams = function(rawValue) {
        var settings      = this.data('settings');
        var inputValue    = rawValue;
        rawValue = rawValue.replace(/\s+/, '');

        if (settings.isUppercase) {
            rawValue = rawValue.toUpperCase();
        }

        var tmpPrefixes   = preparePrefixes(settings.barcodeDescriptors);
        var foundDescriptions;
        var prefixDesc;
        var result = [];

        while ((prefixDesc = nextValidPrefix(tmpPrefixes.testArray, rawValue)) !== false) {
            foundDescriptions = testPrefix(prefixDesc, rawValue, inputValue, tmpPrefixes.testMap[prefixDesc.prefix], false);

            result = result.concat(foundDescriptions);
        }

        return result;
    };

    $.fn.minderCheckBarcodeAsync = function() {
        var $_this = $(this);

        var parseStartingEvent = $.Event('parse-starting');
        parseStartingEvent.scannedValue = $_this.val();
        $_this.trigger(parseStartingEvent);

        var parseResult = $_this.minderCheckBarcode();

        if (parseResult.error) {
            var parseErrorEvent = $.Event('parse-error');
            parseErrorEvent.parseResult = parseResult;
            $_this.trigger(parseErrorEvent);
        } else {
            var parseSuccessEvent = $.Event('parse-success');
            parseSuccessEvent.parseResult = parseResult;
            $_this.trigger(parseSuccessEvent);
        }

        return $_this;
    };
    
    $.fn.minderBarcodeInputAddDescriptors = function(newDescriptors) {
        var defaultSettings = {
            'barcodeDescriptors' : []
        };
        
        var savedSettings = this.data('settings');
        $.extend(defaultSettings, savedSettings);

        $.merge(defaultSettings.barcodeDescriptors, initBarcodeDescriptors(newDescriptors));

        this.data('settings', defaultSettings);
        return this;
    };

    $.fn.minderBarcodeInputGetPrefixes = function(param) {
        var
            settings = this.data('settings') || {'barcodeDescriptors' : []};

        return settings.barcodeDescriptors.filter(function(descriptor){
            return descriptor.DATA_ID == param;
        }).map(function(descriptor) {
            var
                prefixes = descriptor.PREFIX_ARRAY;

            if (descriptor.validWithoutPrefix && (prefixes.indexOf('') < 0)) {
                prefixes.push('');
            }

            return prefixes;
        }).reduce(function(previousValue, prefixes) {
            previousValue.push.apply(previousValue, prefixes);
            return previousValue;
        }, []);
    };

    $.fn.minderBarcodeInputEmulateSuccessEvent = function(paramName, value) {
        var
            settings = this.data('settings') || {'barcodeDescriptors' : []},
            descriptors = settings.barcodeDescriptors.filter(function(descriptor){
                return descriptor.DATA_ID == paramName;
            }),
            descriptor,
            prefixDesc,
            parseSuccessEvent = $.Event('parse-success');

        if (descriptors.length < 1) {
            return;
        }

        descriptor = descriptors.shift();
        prefixDesc = descriptor.prefixTests[0];

        parseSuccessEvent.parseResult ={
            'error' : false,
            'errorCode'  : '',
            'errorMsg'   : '',
            'prefixDesc' : prefixDesc,
            'paramDesc'  : {
                param_name: descriptor.DATA_ID,
                param_type: descriptor.DATA_TYPE_ID,
                param_raw_value: '' + prefixDesc.prefix + value,
                param_filtered_value: value,
                param_prefix: prefixDesc.prefix,
                valid: true
            }
        };

        $(this).trigger(parseSuccessEvent);
    };


})(jQuery);
