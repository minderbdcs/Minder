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

    var Messages = Components.Messages = {};

    // @include ../Message.js
    // @include ../MessageView.js
    // @include ../MessageCollection.js
    // @include ../MessageCollectionView.js
    // @include ../MessagesView.js

    return Components.Messages;
});