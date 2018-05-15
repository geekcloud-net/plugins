/* 
    Created on : Mar 25, 2016, 5:50:55 PM
    Author     : Geometrix
*/
(function($){				
    jQuery.fn.lightTabs = function(options){
        
        var createTabs = function(){
            tabs = this;
            i = 0;
            
            showPage = function(rel){
                $(tabs).children("div.tab_content").hide();
                $(tabs).children('div.tab_content[rel="'+rel+'"]').show();
                
                $(tabs).children(".nav-tab-wrapper").children(".nav-tab").removeClass("nav-tab-active");
                $(tabs).children(".nav-tab-wrapper").children('.nav-tab[rel="'+rel+'"]').addClass("nav-tab-active");
            };
            
            showPage($(tabs).attr('default-rel'));				
            
            $(tabs).children(".nav-tab-wrapper").children(".nav-tab").click(function(){
                showPage($(this).attr("rel"));
                return false;
            });				
        };		
        return this.each(createTabs);
    };	
})(jQuery);

jQuery(document).ready(function(){
    jQuery(".light-tabs").lightTabs();
});

