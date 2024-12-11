define(['core/log'], function(log) {
    return {
        init: function(selector) {
            log.debug('Streamview format initialized for ' + selector);
            // No scroll functionality needed anymore
        }
    };
});