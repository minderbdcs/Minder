(function(Minder2){

    Minder2.Controller.EditForm = function(formName) {
        this._elements = {};
        this._model    = null;
        this._modelName = null;
        this._name     = formName;
        this._serviceUrl = null;
    };

    Minder2.Controller.EditForm.prototype._findModel = function(modelName) {
        return Minder2.Registry.getEditModel(modelName);
    };

    Minder2.Controller.EditForm.prototype.setModel = function(newModel) {
        var formElements;

        this._model = newModel;

        formElements = this.getElements();
        for (var elementIndex in formElements) {
            if (formElements.hasOwnProperty(elementIndex))
                formElements[elementIndex].setModel(newModel);
        }

        return this;
    };

    Minder2.Controller.EditForm.prototype.addElement = function(element) {
        this._elements[element.getAlias()] = element;
        element.setForm(this);
        return this;
    };

    Minder2.Controller.EditForm.prototype.getElementByName = function(name) {
        var
            elements = this.getElements(),
            elementAlias;

        for (elementAlias in elements) {
            if (elements.hasOwnProperty(elementAlias)) {
                if (elements[elementAlias].getName() === name) {
                    return elements[elementAlias];
                }
            }
        }

        return null;
    };



    Minder2.Controller.EditForm.prototype.getElement = function(alias) {
        var elements = this.getElements();

        return elements.hasOwnProperty(alias) ? elements[alias] : null;
    };

    Minder2.Controller.EditForm.prototype.getElements = function() {
        return this._elements;
    };

    Minder2.Controller.EditForm.prototype.getName = function() {
        return this._name;
    };

    Minder2.Controller.EditForm.prototype.getModel = function() {
        var
           model;

        if (!this._model) {
            model = this._findModel(this.getName());

            if (model)
                this.setModel(model);
        }

        return this._model;
    };

    Minder2.Controller.EditForm.prototype.setServiceUrl = function(url) {
        this._serviceUrl = url;
        return this;
    };

    Minder2.Controller.EditForm.prototype._error = function(error) {
        showErrors([error]);
        if(typeof console !== 'undefined' && typeof console.debug == 'function') console.debug(error);
    };

    Minder2.Controller.EditForm.prototype._getServiceUrl = function() {
        if (this._serviceUrl === null)
            this.setServiceUrl('/minder/service/sys-screen/name/' + this.getName() + '/type/FORM_TYPE_EDIT_FORM');

        return this._serviceUrl;
    };

    Minder2.Controller.EditForm.prototype.callRemote = function(method, data, success, fail, scope) {
        var _this = this;

        $.post(
            this._getServiceUrl(),
            {
                'method': method,
                'params': data,
                'id': Minder2.getRequestId()
            },
            function(response) {
                scope = scope || window;

                if (response.error) {
                    _this._error(response.error);

                    if (typeof fail == 'function')
                        fail.call(scope, response.result);

                    return;
                }

                if (typeof success == 'function')
                    success.call(scope, response.result);
            },
            'json'
        );
    };

    Minder2.Controller.EditForm.prototype.load = function(recordId) {
        this.callRemote('load', {'recordId': recordId}, this.loadCallback, null, this);
    };

    Minder2.Controller.EditForm.prototype._fillElementOptions = function(elementName, options) {
        var
            element = this.getElementByName(elementName);

        if (element === null)
            return;

        element.setOptions(options);
    };

    Minder2.Controller.EditForm.prototype._fillElements = function(elementsData) {
        var
            elementName,
            formElement;

        for (elementName in elementsData) {
            if (elementsData.hasOwnProperty(elementName)) {
                formElement = this.getElementByName(elementName);

                if (formElement)
                    formElement.fill(elementsData[elementName]);
            }
        }

        return this;
    };

    Minder2.Controller.EditForm.prototype.fill = function(formData) {
        var model;

        formData = formData || {};

        if (formData.elements)
            this._fillElements(formData.elements);

        if (formData.modelData) {
            model = this.getModel();

            if (model)
                model.setData(formData.modelData, true);
        }
    };

    Minder2.Controller.EditForm.prototype.loadCallback = function(result) {
        this.fill(result);
    };

    Minder2.Controller.EditForm.prototype._getDataToSave = function() {
        var
            dataToSave = this.getModel().getData(),
            fieldIndex;

        for (fieldIndex in dataToSave) {
            if (dataToSave.hasOwnProperty(fieldIndex)) {
                if (dataToSave[fieldIndex] === null)
                    delete dataToSave[fieldIndex];
            }
        }

        return dataToSave;
    };

    Minder2.Controller.EditForm.prototype.save = function(settings) {

        var
            scope = this,
            successCallback = settings.success || function(){scope.saveCallback();},
            failedCallback  = settings.failed || function(){},
            dataToSave = this._getDataToSave();

        this.callRemote('update', {'data': dataToSave}, successCallback, failedCallback, this);
    };

    Minder2.Controller.EditForm.prototype.saveCallback = function() {
        //do nothing
    };

    Minder2.Controller.EditForm.prototype.cancelChanges = function() {
        this.getModel().cancelChanges();
        return this;
    };

    Minder2.Controller.EditForm.prototype._getHtmlContainer = function() {
        return Minder2.Controller.EditForm.Builder.getEditFormHtmlContainer(this.getName());
    };

    Minder2.Controller.EditForm.prototype.block = function(msg) {
        this._getHtmlContainer().block(msg);
    };

    Minder2.Controller.EditForm.prototype.unblock = function() {
        this._getHtmlContainer().unblock();
    };

})(Minder2);