(function(Minder2){

    Minder2.Controller.Element = function(name) {
        this._alias = null;
        this._name = name;
        this._value = null;
        this._model = null;
        this._form  = null;
    };

    Minder2.Controller.Element.prototype.EVENT_NAMESPACE = 'ElementNamespace';

    Minder2.Controller.Element.prototype._getOnHtmlElementChangeHandler = function() {
        var scope = this;

        if (typeof this._onHtmlElementChangeHandler == 'undefined') {
            this._onHtmlElementChangeHandler = function(evt) {
                scope._onHtmlElementChange(evt);
            };
        }

        return this._onHtmlElementChangeHandler;
    };

    Minder2.Controller.Element.prototype.init = function() {
        var
            $htmlElement = this._getHtmlElement();

        Minder2.Registry.registerPublisher(this.getName(), this);

        if ($htmlElement.length < 1)
            return;

        $htmlElement
            .unbind('change.' + this.EVENT_NAMESPACE, this._getOnHtmlElementChangeHandler())
            .bind('change.' + this.EVENT_NAMESPACE, this._getOnHtmlElementChangeHandler());
    };

    Minder2.Controller.Element.prototype._setHtmlValue = function(value) {
        var
            htmlElement = this._getHtmlElement();

        if (htmlElement) {
            htmlElement.val(this._decorateValue(value));
        }

        return this;
    };

    Minder2.Controller.Element.prototype._setFieldValue = function(value) {
        var
            model = this._getModel();

        if (model)
            model.setFieldValue(this.getName(), value);

        return this;
    };

    Minder2.Controller.Element.prototype._setValue = function(value) {
        this._value = value;
        return this;
    };

    Minder2.Controller.Element.prototype._onHtmlElementChange = function(evt) {
        this.setValue($(evt.target).val());
        this._onCommonEvent(evt);
    };

    Minder2.Controller.Element.prototype._filterValue = function(value) {
        //todo add filters
        return value;
    };

    Minder2.Controller.Element.prototype.setValue = function(value) {
        var filteredValue = this._filterValue(value);
        this._setFieldValue(filteredValue);
        return this;
    };

    Minder2.Controller.Element.prototype.getValue = function() {
        return this._value;
    };

    Minder2.Controller.Element.prototype._getModel = function() {
        return this._model;
    };

    Minder2.Controller.Element.prototype.getModel = function() {
        var model = this._getModel();

        if (!model)
            model = this.getForm().getModel();

        return model;
    };

    Minder2.Controller.Element.prototype._onModelDataChanged = function(event) {
        var
            fieldValue;

        if (event.target !== this.getModel())
            return;

        if (event.fieldName === null || event.fieldName == this.getName()) {
            fieldValue = event.target.getFieldValue(this.getName());
            this._setValue(fieldValue)._setHtmlValue(fieldValue);
        }
    };

    Minder2.Controller.Element.prototype._getOnDataChangedHandler = function() {
        var scope = this;

        if (typeof this._onModelDataChangedHandler == 'undefined') {
            this._onModelDataChangedHandler = function(event) {
                scope._onModelDataChanged(event);
            };
        }

        return this._onModelDataChangedHandler;
    };

    Minder2.Controller.Element.prototype.setModel = function(model) {
        var oldModel = this._getModel();

        if (oldModel !== null)
            $(oldModel).unbind('.' + this.EVENT_NAMESPACE, this._getOnDataChangedHandler());

        this._model = model;

        if (model == null)
            return;

        $(model).bind(model.DATA_CHANGED_EVENT + '.' + this.EVENT_NAMESPACE, this._getOnDataChangedHandler());
    };

    Minder2.Controller.Element.prototype._getHtmlElement = function() {
        return $('[data-name="' + this.getName() + '"]');
    };

    Minder2.Controller.Element.prototype._decorateValue = function(value) {
        //todo add decorators
        return value;
    };

    Minder2.Controller.Element.prototype.getName = function() {
        return this._name;
    };

    Minder2.Controller.Element.prototype._fetchAlias = function() {
        return this._getHtmlElement().attr('data-ssv_alias');
    };

    Minder2.Controller.Element.prototype.getAlias = function() {
        if (this._alias === null)
            this._alias = this._fetchAlias();

        return this._alias;
    };

    Minder2.Controller.Element.prototype.setForm = function(form) {
        this._form = form;
        return this;
    };

    Minder2.Controller.Element.prototype.getForm = function() {
        return this._form;
    };

    Minder2.Controller.Element.prototype.fill = function(elementData) {
        if (typeof elementData.value != undefined)
            this.setValue(elementData.value);
        return this;
    };

    Minder2.Controller.Element.prototype._getCommonEventHandler = function() {
        var
            scope = this;

        if (typeof this._onCommonEventHandler == 'undefined')
            this._onCommonEventHandler = function(evt) {
                scope._onCommonEvent(evt);
            };

        return this._onCommonEventHandler;
    };

    Minder2.Controller.Element.prototype.bind = function(type, handler) {
        Minder2.Registry.registerHandler(this.getName() + '.' + type.toLowerCase(), handler);
        return this;
    };

    Minder2.Controller.Element.prototype.getSpecialEventTypes = function() {
        return ['change'];
    };

    Minder2.Controller.Element.prototype.bindEventType = function(type) {
        type = type.toLowerCase();

        if (this.getSpecialEventTypes().indexOf(type) !== -1)
            return this;

        this._getHtmlElement()
            .unbind(type + '.' + this.EVENT_NAMESPACE, this._getCommonEventHandler())
            .bind(type + '.' + this.EVENT_NAMESPACE, this._getCommonEventHandler());
        return this;
    };

    Minder2.Controller.Element.prototype._onCommonEvent = function(evt) {
        var
            type = evt.type.toUpperCase(),
            registeredHandlers = Minder2.Registry.getRegisteredHandlers(this.getName() + '.' + type),
            handlersLength = registeredHandlers.length,
            index;

        for (index = 0; index < handlersLength; index++) {
            if (typeof(registeredHandlers[index]) == 'function')
                registeredHandlers[index].call(this, evt);
        }
    };
})(Minder2);