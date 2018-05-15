var wpeae_aliexpress_variation_debug = false;
var wpeae_aliexpress_variation_chrome_extension = false;
document.addEventListener('wpeaeAPILoaded', function () {
    wpeae_aliexpress_variation_chrome_extension = true;
});
var wpeae_ali_page_query = {
    pages: [],
    warnings: [],
    max_active_cnt: 1,
    max_page_cnt: 50,
    active: 0,
    add_page: function (params) {
        if (this.pages.length < this.max_page_cnt) {
            this.pages.push(params);
            this.show_query_dialog();
            if (this.active < this.max_active_cnt) {
                this.load_page();
            }
        }
    },
    get_count: function () {
        return this.pages.length + this.active;
    },
    has_warning: function () {
        return this.warnings.length > 0;
    },
    load_page: function () {
        var cur_params = this.pages.shift();
        if (typeof cur_params !== 'undefined') {
            this.active++;
            if (wpeae_aliexpress_variation_debug) {
                console.log('(' + cur_params.post_id + ') loading: ' + cur_params.url);
            }

            wpeae_get_product(cur_params.url, function (encode_html) {
                if (wpeae_aliexpress_variation_debug) {
                    console.log('(' + cur_params.post_id + ') parsing...');
                }

                var data = {'action': 'wpeae_aliexpress_variation_add_to_post', 'post_id': cur_params.post_id, 'encode_html': encode_html};
                jQuery.post(ajaxurl, data)
                        .done(function (response) {
                            var json = jQuery.parseJSON(response);
                            if (json.state === 'warning') {
                                wpeae_ali_page_query.warnings.push(cur_params);
                            }
                            if (wpeae_aliexpress_variation_debug) {
                                console.log('(' + cur_params.post_id + ') done; state: ', json.state);
                            }
                            wpeae_ali_page_query.active--;
                            wpeae_ali_page_query.update_query_dialog();
                            wpeae_ali_page_query.load_page();
                        })
                        .fail(function (xhr, status, error) {
                            console.log('[' + status + ']error: ', error);
                            if (wpeae_aliexpress_variation_debug) {
                                console.log('(' + cur_params.post_id + ') fail');
                            }

                            // on error add to queue again
                            wpeae_ali_page_query.add_page(cur_params);
                            wpeae_ali_page_query.active--;
                            wpeae_ali_page_query.update_query_dialog();
                            wpeae_ali_page_query.load_page();
                        });
            });
        }

        if (this.get_count() === 0 && !this.has_warning()) {
            wpeae_ali_page_query.remove_query_dialog();
            if (typeof wpeae_reload_page_after_ajax !== 'undefined' && wpeae_reload_page_after_ajax) {
                wpeae_reload_page_after_ajax = false;
                location.reload();
            }
        }
    },
    show_query_dialog: function () {
        if (jQuery("#wpeae-aliexpress-variation-loader-dialog").length === 0) {
            this.warnings = [];
            jQuery('<div id="wpeae-aliexpress-variation-loader-dialog"></div>').dialog({
                dialogClass: 'wp-dialog',
                modal: true,
                title: "Loading aliexpress variations",
                open: function () {
                    jQuery('#wpeae-aliexpress-variation-loader-dialog').html('Loading');
                },
                close: function (event, ui) {
                    jQuery("#wpeae-aliexpress-variation-loader-dialog").remove();
                }
            });
        }

        this.update_query_dialog();
    },
    update_query_dialog: function () {
        var warning_message = '';
        if (this.has_warning()) {
            warning_message = '<div class="warn">Warning! Please check you are <a target="_blank" href="' + this.warnings[0].url + '">logged</a> in on Aliexpress and update/load the product again.</div>';
        }

        var info_message = '';
        if (this.get_count() > 0) {
            info_message = '<div class="message">Variations loading for <b>' + this.get_count() + '</b> products. Please wait and don`t close a window</div>';
        }
        jQuery('#wpeae-aliexpress-variation-loader-dialog').html(info_message + warning_message);
    },
    remove_query_dialog: function () {
        jQuery("#wpeae-aliexpress-variation-loader-dialog").remove();
    }
};
function wpeae_get_ali_page_html(params) {
    if (wpeae_aliexpress_variation_chrome_extension) {
        wpeae_ali_page_query.add_page(params);
    }
}