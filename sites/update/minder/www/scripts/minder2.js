/**
 * Minder2 namespace
 */
var Minder2 = {
    _requestId: 0,

    getRequestId: function() {
        return Minder2._requestId++;
    }
};