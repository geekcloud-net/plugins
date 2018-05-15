!function(t){"function"==typeof define&&define.amd?define(["jquery"],t):"object"==typeof exports?module.exports=t(require("jquery")):t(jQuery)}(function(t){"use strict";function e(e,n){function s(){return p.update(),h(),p}function c(){b.css(M,p.thumbPosition),f.css(M,-p.contentPosition),w.css(g,p.trackSize),z.css(g,p.trackSize),b.css(g,p.thumbSize)}function h(){v?S[0].ontouchstart=function(t){1===t.touches.length&&(t.stopPropagation(),r(t.touches[0]))}:(b.bind("mousedown",function(t){t.stopPropagation(),r(t)}),z.bind("mousedown",function(t){r(t,!0)})),t(window).resize(function(){p.update("relative")}),p.options.wheel&&window.addEventListener?e[0].addEventListener(P,d,!1):p.options.wheel&&(e[0].onmousewheel=d)}function u(){return p.contentPosition>0}function a(){return p.contentPosition<=p.contentSize-p.viewportSize-5}function r(e,o){p.hasContentToSroll&&(t("body").addClass("noSelect"),x=o?b.offset()[M]:k?e.pageX:e.pageY,v?(document.ontouchmove=function(t){(p.options.touchLock||u()&&a())&&t.preventDefault(),m(t.touches[0])},document.ontouchend=l):(t(document).bind("mousemove",m),t(document).bind("mouseup",l),b.bind("mouseup",l),z.bind("mouseup",l)),m(e))}function d(o){if(p.hasContentToSroll){var i=o||window.event,n=-(i.deltaY||i.detail||-1/3*i.wheelDelta)/40,s=1===i.deltaMode?p.options.wheelSpeed:1;p.contentPosition-=n*s*p.options.wheelSpeed,p.contentPosition=Math.min(p.contentSize-p.viewportSize,Math.max(0,p.contentPosition)),p.thumbPosition=p.contentPosition/p.trackRatio,e.trigger("move"),b.css(M,p.thumbPosition),f.css(M,-p.contentPosition),(p.options.wheelLock||u()&&a())&&(i=t.event.fix(i),i.preventDefault())}}function m(t){if(p.hasContentToSroll){var o=k?t.pageX:t.pageY,i=v?x-o:o-x,n=Math.min(p.trackSize-p.thumbSize,Math.max(0,p.thumbPosition+i));p.contentPosition=n*p.trackRatio,e.trigger("move"),b.css(M,n),f.css(M,-p.contentPosition)}}function l(){p.thumbPosition=parseInt(b.css(M),10)||0,t("body").removeClass("noSelect"),t(document).unbind("mousemove",m),t(document).unbind("mouseup",l),b.unbind("mouseup",l),z.unbind("mouseup",l),document.ontouchmove=document.ontouchend=null}this.options=t.extend({},i,n),this._defaults=i,this._name=o;var p=this,S=e.find(".viewport"),f=e.find(".overview"),w=e.find(".scrollbar"),z=w.find(".track"),b=w.find(".thumb"),v="ontouchstart"in document.documentElement,P="onwheel"in document.createElement("div")?"wheel":void 0!==document.onmousewheel?"mousewheel":"DOMMouseScroll",k="x"===this.options.axis,g=k?"width":"height",M=k?"left":"top",x=0;return this.contentPosition=0,this.viewportSize=0,this.contentSize=0,this.contentRatio=0,this.trackSize=0,this.trackRatio=0,this.thumbSize=0,this.thumbPosition=0,this.hasContentToSroll=!1,this.update=function(t){var e=g.charAt(0).toUpperCase()+g.slice(1).toLowerCase();switch(this.viewportSize=S[0]["offset"+e],this.contentSize=f[0]["scroll"+e],this.contentRatio=this.viewportSize/this.contentSize,this.trackSize=this.options.trackSize||this.viewportSize,this.thumbSize=Math.min(this.trackSize,Math.max(this.options.thumbSizeMin,this.options.thumbSize||this.trackSize*this.contentRatio)),this.trackRatio=(this.contentSize-this.viewportSize)/(this.trackSize-this.thumbSize),this.hasContentToSroll=this.contentRatio<1,w.toggleClass("disable",!this.hasContentToSroll),t){case"bottom":this.contentPosition=Math.max(this.contentSize-this.viewportSize,0);break;case"relative":this.contentPosition=Math.min(Math.max(this.contentSize-this.viewportSize,0),Math.max(0,this.contentPosition));break;default:this.contentPosition=parseInt(t,10)||0}return this.thumbPosition=this.contentPosition/this.trackRatio,c(),p},s()}var o="tinyscrollbar",i={axis:"y",wheel:!0,wheelSpeed:40,wheelLock:!0,touchLock:!0,trackSize:!1,thumbSize:!1,thumbSizeMin:20};t.fn[o]=function(i){return this.each(function(){t.data(this,"plugin_"+o)||t.data(this,"plugin_"+o,new e(t(this),i))})}});

jQuery(document).ready(function ($) {

    $(".mts-languages").tinyscrollbar();

    if ($('#mts-language-btn-top').length) {
        $('#mts-language-btn-top a').click(function () {
            $(".mts-languages").toggleClass('active');
        });
    }
    $('#mts-language-btn').click(function () {
        $(".mts-languages").toggleClass('active');
    });

    $('body').click(function (evt) {
        var $target = $(evt.target);
        if ($target.is('#mts-language-btn') || $target.is('#mts-language-btn-top a'))
            return true;
        $("#mts_languages").removeClass("active");
    });

    $('.mts-languages ol li a').click(function () {
        $(".mts-languages").toggleClass('active');
    });

    function triggerHtmlEvent(element, eventName) {
        var event;
        if (document.createEvent) {
            event = document.createEvent('HTMLEvents');
            event.initEvent(eventName, true, true);
            element.dispatchEvent(event);
        } else {
            event = document.createEventObject();
            event.eventType = eventName;
            element.fireEvent('on' + event.eventType, event);
        }
    }

    $('.translation-links a').click(function (e) {
        e.preventDefault();
        var lang = $(this).data('lang');
        $('#mts_google_translate select option').each(function () {
            if ($(this).val().indexOf(lang) > -1) {
                $(this).parent().val($(this).val());
                var container = document.getElementById('mts_google_translate');
                var select = container.getElementsByTagName('select')[0];
                triggerHtmlEvent(select, 'change');
            }
        });
    });
});