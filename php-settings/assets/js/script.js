/**
 * @package   PHP Settings
 * @date      2017-03-04
 * @version   1.0.6
 * @author    Askupa Software <hello@askupasoftware.com>
 * @link      http://products.askupasoftware.com/php-settings
 * @copyright 2017 Askupa Software
 */

jQuery(document).ready(function($) {
    var editor = ace.edit("php-settings");
    editor.setTheme("ace/theme/kuroir");
    editor.getSession().setMode("ace/mode/php");
    
    $('#save-php-settings').processButton(function(){
        var self = this;
        $.post(
            ajaxurl, // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            {
                'action': 'save_php_settings',
                'nonce': php_settings.nonce,
                'ini_settings': editor.getValue()
            }, 
            function(response) {
                if( response.success ) self.done();
                else
                {
                    notify(response.data, 'error');
                    self.abort();
                }
            }
        );
    });
    
    $('#delete-files').processButton(function(){
        var self = this;
        $.post(
            ajaxurl, // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            {
                'nonce': php_settings.nonce,
                'action': 'delete_ini_files'
            }, 
            function(response) {
                if( response.success ) 
                {
                    self.done();
                    editor.setValue('',1);
                }
                else
                {
                    notify(response.data, 'error');
                    self.abort();
                }
            }
        );
    });
    
    $('#refresh-table').processButton(function(){
        var self = this;
        $.post(
            ajaxurl, // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            {
                'nonce': php_settings.nonce,
                'action': 'refresh_table'
            }, 
            function(response) {
                if( response.success ) 
                {
                    $('#phpinfo-wrapper').html(response.data);
                    PHPInfo.clearSelection();
                    PHPInfo.updateListJs();
                    self.done();
                }
                else
                {
                    notify('Unable to refresh table', 'error');
                    self.abort();
                }
            }
        );
    });
    
    show_tab( $('.nav-tab').first().attr('href') );
    $('.nav-tab').click(function(e){
        e.preventDefault();
        show_tab( $(this).attr('href') );
    });
    
    function show_tab( id )
    {
        $('.tab').hide();
        $('.nav-tab').removeClass('nav-tab-active');
        $(id).show();
        $('[href="'+id+'"]').addClass('nav-tab-active');
    }
    
    function notify( message, type )
    {
        var $notification = $('<div>').addClass('nav-tab-notification nav-tab-'+type),
            $icon = $('<i>').addClass('dashicons dashicons-no'),
            $text = $('<p>').html(message);
        $('.nav-tab-notifications').html($notification.append($icon,$text));
    }
    
    /**
     * PHP Info table utility class
     */
    function PHPInfo () {};

    PHPInfo.clipboard = [];
    PHPInfo.listJs;

    PHPInfo.init = function()
    {
        $('#copy-directives').on('click', PHPInfo.copyDirectives);
        $('#search-wrapper .dashicons-dismiss').on('click', PHPInfo.clearSearch);
        $('#search-wrapper input').on('keyup', PHPInfo.onSearchKeyup);
        $('#phpinfo-wrapper').on('click','#phpinfo li',PHPInfo.onDirectiveClick);
        PHPInfo.updateListJs();
        PHPInfo.updateCounter();
    };

    PHPInfo.copyDirectives = function()
    {
        if(0 === PHPInfo.clipboard.length) return;
        
        var directives = PHPInfo.clipboard.map(function(e){return e.value;}).join('\n'),
            content = editor.getValue();

        if(content.trim() !== '') content += '\n';
        editor.setValue(content+directives, 1);
        show_tab( '#tab1' );
        editor.focus();
        PHPInfo.clearSelection();
    }

    PHPInfo.onDirectiveClick = function(e)
    {
        var checkbox = $(this).find('.list-checkbox input'),
            id = checkbox.val(),
            directive = $(this).children('.list-key').text()+' = '+$(this).find('.list-value .value').text(),
            checked = checkbox.prop("checked");
    
        // Toggle checkbox if not clicking on the checkbox itself
        if( !$(e.target).is('input') ) checkbox.prop("checked", !checked);
        
        // Add/remove directive from clipboard
        if( !checked ) PHPInfo.addToClipboard(id, directive);
        else PHPInfo.removeFromClipboard(id);
        
        PHPInfo.updateCounter();
    };
    
    PHPInfo.onSearchKeyup = function()
    {
        var $input = $(this),
            $clear = $input.parent().children('.dashicons-dismiss');
        if($input.val() === '') $clear.hide();
        else $clear.show();
    };
    
    PHPInfo.clearSelection = function()
    {
        $('#phpinfo .list-checkbox input:checked').each(function(){
            $(this).prop("checked", false);
        });
        PHPInfo.clipboard = [];
        PHPInfo.updateCounter();
    };
    
    PHPInfo.clearSearch = function()
    {
        var $input = $('#search-wrapper input');
        $input.val('');
        // Must use dispatch event to make list.js refresh
        $input[0].dispatchEvent(new KeyboardEvent("keyup"));
    };
    
    PHPInfo.addToClipboard = function(id, value)
    {
        PHPInfo.clipboard.push({id: id, value: value});
    };
    
    PHPInfo.removeFromClipboard = function(id)
    {
        for( var i = 0; i < PHPInfo.clipboard.length; i++)
        {
            if( PHPInfo.clipboard[i].id === id )
            {
                PHPInfo.clipboard.splice(i, 1);
                break;
            }
        }
    };
    
    PHPInfo.updateListJs = function()
    {
        PHPInfo.listJs = new List('directives', {valueNames: [ 'list-key', 'list-value' ]});
    };
    
    PHPInfo.updateCounter = function()
    {
        $('#directive-counter').text(PHPInfo.clipboard.length+' directives selected');
    };
    
    PHPInfo.init();
});