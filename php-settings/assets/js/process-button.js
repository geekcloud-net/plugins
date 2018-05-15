/**
 * @package   PHP Settings
 * @date      2017-03-04
 * @version   1.0.6
 * @author    Askupa Software <hello@askupasoftware.com>
 * @link      http://products.askupasoftware.com/php-settings
 * @copyright 2017 Askupa Software
 */

(function($){
    $.fn.processButton = function( callback ) {
        return this.each(function(){
            var self       = this,
                $self      = $(this),
                $icon      = $('<i>').addClass('dashicons dashicons-'+$self.attr('data-icon')),
                $loader    = $('<i>').addClass('loader').hide(),
                title      = $self.text(),
                $title     = $('<span>').text(title),
                processing = false;
            
            $self.html($icon).append($loader, $title);
            
            $self.click(function(){
                if(processing) return;
                processing = true;
                $icon.hide();
                $loader.css('display', 'inline-block');
                $self.addClass('loading');
                $title.text($self.attr('data-processing-text'));
                callback.call(self);
            });
            
            this.done = function() {
                $loader.hide();
                $self.removeClass('loading');
                $self.addClass('success');
                $title.text($self.attr('data-done-text'));
                setTimeout(function(){
                    $title.text(title);
                    $self.removeClass('success');
                    $icon.show();
                    processing = false;
                },2000);
            }
            
            this.abort = function() {
                $loader.hide();
                $title.text(title);
                $self.removeClass('loading');
                $icon.show();
                processing = false;
            }
        });
    };
})(jQuery);