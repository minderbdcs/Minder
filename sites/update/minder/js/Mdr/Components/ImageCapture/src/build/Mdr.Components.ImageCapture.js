(function(root, factory) {

    if (typeof define === 'function' && define.amd) {
        define(['Mdr/Components', 'backbone'], function(Components, Backbone) {
            return factory(Components, Backbone);
        });
    } else if (typeof exports !== 'undefined') {
        var Components = require('Mdr/Components'),
            Backbone = require('backbone');
        module.exports = factory(Components, Backbone);
    } else {
        factory(root.Mdr.Components, root.Backbone);
    }

})(this, function(Components, Backbone){
    "use strict";

    var ImageCapture = Components.ImageCapture = {};

    // @include ../UserMedia.js
    // @include ../View.js

    return Components.ImageCapture;
});