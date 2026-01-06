(function(Minder2){
    Minder2.Controller.Element.ToolButton = function(name) {
        Minder2.Controller.Element.call(this, name);
    };

    Minder2.Controller.Element.ToolButton.prototype = new Minder2.Controller.Element();
    Minder2.Controller.Element.ToolButton.prototype.constructor = Minder2.Controller.Element.ToolButton;

    Minder2.Controller.Element.ToolButton.prototype.getAlias = function() {
        return this.getName();
    }

})(Minder2);