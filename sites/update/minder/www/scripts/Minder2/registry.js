(function(Minder2){
    if (typeof Minder2.Registry == 'undefined')
        Minder2.Registry = {
            Forms:      {},
            Models:     {},
            Handlers:   {},
            Publishers: {}
        };

    Minder2.Registry.registerEditModel = function(modelName, model) {
        Minder2.Registry.setEditModel(modelName, model);
    };

    Minder2.Registry.setEditModel = function(modelName, model) {

        if (typeof Minder2.Registry.Models[modelName] == 'undefined')
            Minder2.Registry.Models[modelName] = {};

        Minder2.Registry.Models[modelName].editModel = model;
    };

    Minder2.Registry.getEditModel = function(modelName) {
        if (typeof Minder2.Registry.Models[modelName] == 'undefined')
            return null;

        if (typeof Minder2.Registry.Models[modelName].editModel == 'undefined')
            return null;

        return Minder2.Registry.Models[modelName].editModel;
    };

    Minder2.Registry._getRegisteredHandlers = function(publisherName, eventType) {
        if (typeof Minder2.Registry.Handlers[publisherName] == 'undefined')
            return [];

        if (typeof Minder2.Registry.Handlers[publisherName][eventType] == 'undefined')
            return [];

        return Minder2.Registry.Handlers[publisherName][eventType];
    };

    Minder2.Registry._getEventTypes = function(publisherName) {
        var
            result = [],
            eventType;

        if (typeof Minder2.Registry.Handlers[publisherName] == 'undefined')
            return result;

        for (eventType in Minder2.Registry.Handlers[publisherName])
            if (Minder2.Registry.Handlers[publisherName].hasOwnProperty(eventType))
                result.push(eventType);

        return result;
    };

    Minder2.Registry.getRegisteredHandlers = function(name) {
        var
            nameParts = name.split('.'),
            publisherName = nameParts.shift().toUpperCase(),
            eventType = nameParts.shift().toUpperCase();

        return Minder2.Registry._getRegisteredHandlers(publisherName, eventType);
    };

    Minder2.Registry._registerHandler = function(publisherName, eventType, handler) {
        var
            registeredHandlers = Minder2.Registry._getRegisteredHandlers(publisherName, eventType),
            index;

        for (index = 0; index < registeredHandlers.length; index++) {
            if (registeredHandlers[index] === handler)
                return;
        }

        registeredHandlers.push(handler);
        Minder2.Registry.Handlers[publisherName] = Minder2.Registry.Handlers[publisherName] || {};
        Minder2.Registry.Handlers[publisherName][eventType] = registeredHandlers;
    };

    Minder2.Registry.registerHandler = function(name, handler) {
        var
            nameParts = name.split('.'),
            publisherName = nameParts.shift().toUpperCase(),
            eventType = nameParts.shift().toUpperCase(),
            publisher = Minder2.Registry.getPublisher(publisherName);

        Minder2.Registry._registerHandler(publisherName, eventType, handler);

        if (publisher)
            publisher.bindEventType(eventType);

    };

    Minder2.Registry.registerPublisher = function(name, publisher) {
        var
            eventTypes = Minder2.Registry._getEventTypes(name),
            index = 0,
            eventType;

        while (eventType = eventTypes[index++])
            publisher.bindEventType(eventType);

        Minder2.Registry.Publishers[name] = publisher;
    };

    Minder2.Registry.getPublisher = function(name) {
        return Minder2.Registry.Publishers.hasOwnProperty(name) ? Minder2.Registry.Publishers[name] : undefined;
    }
})(Minder2);