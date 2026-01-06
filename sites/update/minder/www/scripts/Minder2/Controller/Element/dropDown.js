(function(Minder2){

    Minder2.Controller.Element.DropDown = function(name, options) {
        Minder2.Controller.Element.Multi.call(this, name, options);
    };

    Minder2.Controller.Element.DropDown.prototype = new Minder2.Controller.Element.Multi();
    Minder2.Controller.Element.DropDown.prototype.constructor = Minder2.Controller.Element.DropDown;

    Minder2.Controller.Element.DropDown.prototype._fetchOptionsFromHtmlElement = function() {
        var
            htmlElement = this._getHtmlElement(),
            result = [];

        if (htmlElement.length < 1)
            return result;


        htmlElement.find('option').each(function(){
            var option = {},
                attributes = this.attributes,
                attributesLength = attributes.length,
                index;

            for (index = 0; index < attributesLength; index++) {
                option[attributes[index].name] = attributes[index].value;
            }

            result.push(option);
        });

        return result;
    };

    Minder2.Controller.Element.DropDown.prototype._getOptions = function() {
        if (this._options === null)
            this._setOptions(this._fetchOptionsFromHtmlElement());

        return this._options;
    };

    Minder2.Controller.Element.DropDown.prototype._fillHtmlOptions = function(options) {
        var
            htmlElement = this._getHtmlElement(),
            htmlOptions,
            optionsPart,
            offset = 0;

        if (htmlElement.length < 1)
            return;

        htmlOptions = options.map(function(option){
            var index,
                attributes = [],
                label;

            for (index in option) {
                if (option.hasOwnProperty(index))
                    attributes.push(index + '= "' + option[index] + '"');
            }

            label = option.label || option.LABEL;

            return '<option ' + attributes.join(' ')  + '>' + label + '</option>';
        });

        htmlElement
            .each(function() {
                var
                    $this = $(this),
                    optionsPart,
                    offset = 0,
                    window = 500,
                    renderPartialCallback;

                $this.empty();
                optionsPart = htmlOptions.slice(offset, offset + window);

                renderPartialCallback = function() {
                    offset += window;
                    $this.append($(optionsPart.join('')));
                    optionsPart = htmlOptions.slice(offset, offset + window);

                    if (optionsPart.length > 0)
                        setTimeout(renderPartialCallback, 0);
                };

                if (optionsPart.length > 0)
                    setTimeout(renderPartialCallback, 0);
            })
            .val(this.getValue());

        return this;
    };

    Minder2.Controller.Element.DropDown.prototype.setOptions = function(options) {
        this._setOptions(options)._fillHtmlOptions(this.getOptions());
    };

    Minder2.Controller.Element.DropDown.prototype._setHtmlValue = function(value) {
        var
            scope = this;

        setTimeout(function(){
            var
                htmlElement = scope._getHtmlElement();

            if (htmlElement) {
                htmlElement.val(scope._decorateValue(value));
            }
        }, 0);

        return this;
    };
})(Minder2);