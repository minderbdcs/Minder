(function(Minder2){
    Minder2.Builder = {

    };

    Minder2.Builder.buildControllers = function() {
        $('form[data-type="EditForm"]').each(function(){
            Minder2.Builder.registerEditForm(Minder2.Builder.buildEditForm($(this).attr('data-ss_name')));
        });
    };

    Minder2.Builder.buildEditForm = function(sysScreenName) {
        return Minder2.Controller.EditForm.Builder.build(sysScreenName);
    };

    Minder2.Builder._getFormsFromRegistry = function(sysScreenName) {
        if (typeof Minder2.Registry.Forms[sysScreenName] == 'undefined')
            Minder2.Registry.Forms[sysScreenName] = {};

        return Minder2.Registry.Forms[sysScreenName];
    };

    Minder2.Builder.registerEditForm = function(formObject) {
        Minder2.Builder._getFormsFromRegistry(formObject.getName()).editForm = formObject;
    };

    Minder2.Builder.getEditForm = function(sysScreenName, buildIfNotExist) {
        var
            sysScreenForms = this._getFormsFromRegistry(sysScreenName),
            editForm = sysScreenForms.editForm;

        buildIfNotExist = (typeof buildIfNotExist == 'undefined') ? true : !!buildIfNotExist;

        if (!editForm && buildIfNotExist) {
            editForm = this.buildEditForm(sysScreenName);
            this.registerEditForm(editForm);
        }

        return editForm;
    };
})(Minder2);