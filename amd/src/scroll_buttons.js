define(['jquery', 'core/log'], function($, log) {
    return {
        init: function(courseId) {
            log.debug('Streamview format initialized for course ' + courseId);
            // No scroll functionality needed anymore
        }
    };
});