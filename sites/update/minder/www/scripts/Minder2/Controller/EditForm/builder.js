(function(Minder2){
    Minder2.Controller.EditForm.Builder = {

    };

    Minder2.Controller.EditForm.Builder.getEditFormHtmlContainer = function(ssName) {
        return $('form[data-ss_name="' + ssName + '"][data-type="EditForm"]');
    };

    Minder2.Controller.EditForm.Builder.build = function(ssName) {
        var $form = this.getEditFormHtmlContainer(ssName);

        if ($form.length < 1)
            return null;

        return Minder2.Controller.EditForm.Builder._buildForm($form);
    };

    Minder2.Controller.EditForm.Builder._buildForm = function($form) {
        var formObject = new Minder2.Controller.EditForm($form.attr('data-ss_name')),
            elementIterator,
            formElements;

        $form.find('input, select, textarea, div, button').filter('[data-type="MinderElement"]').each(function(){
            formObject.addElement(Minder2.Controller.EditForm.Builder._buildFormElement($(this)));
        });

        formObject.setModel(Minder2.Controller.EditForm.Builder._getEditModel(formObject));

        formElements = formObject.getElements();
        for (elementIterator in formElements) {
            if (formElements.hasOwnProperty(elementIterator))
                formElements[elementIterator].init();
        }

        return formObject;
    };

    Minder2.Controller.EditForm.Builder._getEditModel = function(formObject) {
        return Minder2.Registry.getEditModel(formObject.getName());
    };

    Minder2.Controller.EditForm.Builder._buildFormElement = function($element) {

        switch ($element.attr('data-element-type')) {
            case 'FormTabs':
                return new Minder2.Controller.Element.FormTab($element.attr('data-name'));
            case 'DropDown':
                return new Minder2.Controller.Element.DropDown($element.attr('data-name'));
            case 'DatePicker':
                return new Minder2.Controller.Element.DatePicker($element.attr('data-name'));
            case 'ComboBox':
                return new Minder2.Controller.Element.ComboBox($element.attr('data-name'));
            case 'ToolButton':
                return new Minder2.Controller.Element.ToolButton($element.attr('data-name'));
            default:
                return new Minder2.Controller.Element($element.attr('data-name'));
        }
    }
})(Minder2);