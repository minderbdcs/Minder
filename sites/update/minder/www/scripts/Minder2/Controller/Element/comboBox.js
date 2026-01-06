(function(Minder2){

    Minder2.Controller.Element.ComboBox = function(name, options) {
        Minder2.Controller.Element.Multi.call(this, name, options);
        this._selectedLabel = null;
    };

    Minder2.Controller.Element.ComboBox.prototype = new Minder2.Controller.Element.Multi();
    Minder2.Controller.Element.ComboBox.prototype.constructor = Minder2.Controller.Element.ComboBox;

    Minder2.Controller.Element.ComboBox.prototype.init = function() {
        var
            scope = this;

        Minder2.Controller.Element.Multi.prototype.init.call(this);

        this._getHtmlElement().autocomplete({
            minLength: 0,
            source: function(term, response){
                response(scope._getHtmlOptions(term.term));
            },
            select: function(event, ui) {
                if (ui.item) {
                    scope.setValue(ui.item.value || ui.item.VALUE)._setSelectedLabel(ui.item.label || ui.item.LABEL);
                }
            }
        });

        this._getHtmlElement().data('autocomplete')._renderItem = function(ul, item) {
            var
                label = item.label || item.LABEL;
            return $( "<li></li>" )
       						.data( "item.autocomplete", item )
       						.append( "<a>" + label + "</a>" )
       						.appendTo( ul );
        };
    };

    Minder2.Controller.Element.ComboBox.prototype._getHtmlOptions = function(term) {

        var matcher = new RegExp($.ui.autocomplete.escapeRegex(term), 'i');

        return this._getOptions().filter(function(item) {
            var value = item.value || item.VALUE;
            var label = item.label || item.LABEL;
            return !term || matcher.test(value) || matcher.test(label);
        }).slice(0, 100); //show only first 100 elements in drop-down list as it too complex for browser to render more elements
    };

    Minder2.Controller.Element.ComboBox.prototype._setSelectedLabel = function(label) {
        this._selectedLabel = label;
        return this;
    };

    Minder2.Controller.Element.ComboBox.prototype._getSelectedLabel = function() {
        return this._selectedLabel;
    };

    Minder2.Controller.Element.ComboBox.prototype._getOptionByLabel = function(label) {
        var result = this.getOptions().filter(function(item) {
            var
                itemLabel = item.label || item.LABEL;

            return label === itemLabel;
        });

        return result.length > 0 ? result.shift() : null;
    };

    Minder2.Controller.Element.ComboBox.prototype._getOptionByValue = function(value) {
        var result = this.getOptions().filter(function(item) {
            var
                itemValue = item.value || item.VALUE;

            return value === itemValue;
        });

        return result.length > 0 ? result.shift() : null;
    };

    /**
     * If value in options list, then decorated value is corresponding option label.
     * In other case decorated value is the value itself.
     *
     * @param value
     */
    Minder2.Controller.Element.ComboBox.prototype._decorateValue = function(value) {
        var option = this._getOptionByValue(value);

        return option === null ? value : (option.label || option.LABEL);
    };

    /**
     * If passed value the same as previously selected label, then assume that this is change_after_select event, so leave
     * value the same.
     * If passed value is in fact option label, then filtered value is corresponding option value.
     * In other case this is real value.
     *
     * @param value
     */
    Minder2.Controller.Element.ComboBox.prototype._filterValue = function(value) {
        var option;

        if (this._getSelectedLabel() === value)
            return this.getValue();

        option = this._getOptionByLabel(value);
        return option === null ? value : (option.value || option.VALUE);
    }
})(Minder2);