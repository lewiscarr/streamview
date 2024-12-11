YUI.add('moodle-format_streamview-scroll', function (Y) {
    M.format_streamview = M.format_streamview || {};
    M.format_streamview.scroll = {
        init: function() {
            Y.all('.streamview-scroll-button').on('click', function(e) {
                e.preventDefault();
                var container = e.currentTarget.siblings('.streamview-activities').item(0);
                var scrollAmount = container.get('offsetWidth') * 0.8;

                if (e.currentTarget.hasClass('left')) {
                    this.scrollContainer(container, -scrollAmount);
                } else {
                    this.scrollContainer(container, scrollAmount);
                }
            }, this);

            Y.all('.streamview-activities').on('scroll', this.updateButtonVisibility);
            Y.one(window).on('resize', function() {
                Y.all('.streamview-activities').each(this.updateButtonVisibility);
            }, this);

            Y.all('.streamview-activities').each(this.updateButtonVisibility);
        },

        scrollContainer: function(container, amount) {
            var currentScroll = container.get('scrollLeft');
            container.set('scrollLeft', currentScroll + amount);
        },

        updateButtonVisibility: function() {
            var container = this;
            var leftButton = container.siblings('.streamview-scroll-button.left').item(0);
            var rightButton = container.siblings('.streamview-scroll-button.right').item(0);

            if (leftButton && rightButton) {
                leftButton.setStyle('display', container.get('scrollLeft') > 0 ? 'block' : 'none');
                rightButton.setStyle('display', 
                    container.get('scrollLeft') < container.get('scrollWidth') - container.get('offsetWidth') ? 'block' : 'none'
                );
            }
        }
    };
}, '1.0', {requires: ['node', 'event']});