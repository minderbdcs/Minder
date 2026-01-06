(function(root, factory) {
    if (typeof define === 'function' && define.amd) {
        define(['Mdr'], function(Mdr) {
            return factory(Mdr);
        });
    } else if (typeof exports !== 'undefined') {
        var Mdr = require('Mdr');
        module.exports = factory(Mdr);
    } else {
        factory(root.Mdr);
    }

})(this, function(Mdr){
    "use strict";

    Mdr.Components = {};

    return Mdr.Components;
});