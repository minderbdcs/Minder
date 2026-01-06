(function(Minder2){

    Minder2.Controller.Element.Multi = function(name, options) {
        Minder2.Controller.Element.call(this, name);
        this._options = null;
        this._setOptions(options);
    };

    Minder2.Controller.Element.Multi.prototype = new Minder2.Controller.Element();
    Minder2.Controller.Element.Multi.prototype.constructor = Minder2.Controller.Element.Multi;

    Minder2.Controller.Element.Multi.prototype._setOptions = function(options) {
        if (typeof options == 'undefined')
            return this;

        this._options = options;

        return this;
    };

    Minder2.Controller.Element.Multi.prototype.setOptions = function(options) {
        return this._setOptions(options);
    };

    Minder2.Controller.Element.Multi.prototype._getOptions = function() {
        if (this._options == null)
            return [];

        return this._options;
    };

    Minder2.Controller.Element.Multi.prototype._getFilteredOptions = function() {
        //todo

        return this._getOptions();
    };

    Minder2.Controller.Element.Multi.prototype.getOptions = function() {
        return this._getFilteredOptions();
    };

    Minder2.Controller.Element.Multi.prototype.fill = function(elementData) {
        Minder2.Controller.Element.prototype.fill.call(this, elementData);

        if (elementData.multiOptions)
            this.setOptions(elementData.multiOptions);

        return this;
    }

})(Minder2);