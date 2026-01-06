(function($){
    var
        BEFORE_RENDER = 'BEFORE_RENDER',
        COMPLETE = 'COMPETE',
        channels = {};

    $.fn.mdrLoadChannel = function(url, params, channelName) {
        var loadRequest, loadChannel;

        if (channelName) {
            loadChannel = _getChannelOrNew(channelName);
            loadRequest = this.mdrLoad(url, params, {'channel-request-id': loadChannel.nextRequestId()});
            loadRequest.beforeRender(function(evt){
                var requestId = evt.res.getResponseHeader('channel-request-id');

                if (requestId !== null) {
                    if (requestId != loadChannel.latestRequestId) {
                        evt.preventDefault();
                    }
                }
            });

        } else {
            loadRequest = this.mdrLoad(url, params)
        }

        return loadRequest;
    };

    function _getChannelOrNew(channelName) {
        if (!channels[channelName]) {
            channels[channelName] = new LoadChannel();
        }

        return channels[channelName];
    }

    $.fn.mdrLoad = function( url, params, headers) {
        var loadRequest = new LoadRequest();

        // Don't do a request if no elements are being requested
        if ( !this.length ) {
            return loadRequest;
        }

        // Default to a GET request
        var type = "GET";

        // If the second parameter was provided
        if ( params ) {
            // Otherwise, build a param string
            if ( typeof params === "object" ) {
                params = jQuery.param( params, jQuery.ajaxSettings.traditional );
                type = "POST";
            }
        }

        var $containers = this;

        // Request the remote document
        jQuery.ajax({
            url: url,
            type: type,
            dataType: "html",
            data: params,
            headers: headers || {},
            complete: function(res, status){
                _ajaxComplete(res, status, $(loadRequest), $containers)
            }
        });

        return loadRequest;
    };

    function _ajaxComplete(res, status, $loadRequest, $containers) {
        // If successful, inject the HTML into all the matched elements
        if (status === "success" || status === "notmodified" ) {

            $containers.each(function(){
                var beforeRenderEvent = $.Event(BEFORE_RENDER),
                    $this = $(this);
                beforeRenderEvent.res = res;
                beforeRenderEvent.status = status;
                beforeRenderEvent.$container = $this;

                $loadRequest.trigger(beforeRenderEvent);

                if (!beforeRenderEvent.isDefaultPrevented()) {
                    $this.html(res.responseText);
                    var completeEvent = $.Event(COMPLETE);
                    completeEvent.res = res;
                    completeEvent.status = status;
                    completeEvent.$container = $this;
                    $loadRequest.trigger(completeEvent);
                }
            });
        }
    }

    function LoadChannel() {
        this.latestRequestId = 0;
    }

    LoadChannel.prototype.nextRequestId = function() {
        return ++this.latestRequestId;
    };

    function LoadRequest() {}

    LoadRequest.prototype.beforeRender = function(callback, namespace) {
        $(this).bind((callback) ? BEFORE_RENDER + '.' + namespace : BEFORE_RENDER, callback);
    };

    LoadRequest.prototype.complete = function(callback, namespace) {
        $(this).bind((callback) ? COMPLETE + '.' + namespace : COMPLETE, callback);
    };
})(jQuery);