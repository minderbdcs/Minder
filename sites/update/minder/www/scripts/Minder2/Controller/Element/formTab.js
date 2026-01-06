(function(Minder2){
    
    Minder2.Controller.Element.FormTab = function(name) {
        Minder2.Controller.Element.call(this, name);
    };

    Minder2.Controller.Element.FormTab.prototype = new Minder2.Controller.Element();
    Minder2.Controller.Element.FormTab.prototype.constructor = Minder2.Controller.Element.FormTab;

    Minder2.Controller.Element.FormTab.prototype.init = function() {
        this._getHtmlElement().find('ul').tabs();
    };

    Minder2.Controller.Element.FormTab.prototype.getAlias = function() {
        return this.getName();
    }
})(Minder2);