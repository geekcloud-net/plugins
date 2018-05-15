/* Modifided script from the simple-page-ordering plugin */
jQuery(function ($) {

    $('#wc_pos_tiles_table.menu_order table.widefat.wp-list-table tbody th, #wc_pos_tiles_table.menu_order table.widefat tbody td').css('cursor', 'move');

    $("#wc_pos_tiles_table.menu_order table.widefat.wp-list-table").sortable({
        items: 'tbody tr:not(.inline-edit-row)',
        cursor: 'move',
        axis: 'y',
        forcePlaceholderSize: true,
        helper: 'clone',
        opacity: 0.65,
        placeholder: 'product-cat-placeholder',
        scrollSensitivity: 40,
        start: function (event, ui) {
            if (!ui.item.hasClass('alternate')) ui.item.css('background-color', '#ffffff');
            ui.item.children('td,th').css('border-bottom-width', '0');
            ui.item.css('outline', '1px solid #aaa');
        },
        stop: function (event, ui) {
            ui.item.removeAttr('style');
            ui.item.children('td,th').css('border-bottom-width', '1px');
        },
        update: function (event, ui) {
            var grid_id = $('#grid_id').val();	// this post id
            var tileid = ui.item.find('.check-column input').val();	// this post id
            var termparent = ui.item.find('.parent').html(); 	// post parent

            var prevtileid = ui.item.prev().find('.check-column input').val();
            var nexttileid = ui.item.next().find('.check-column input').val();

            // can only sort in same tree
            var prevtermparent = undefined;
            if (prevtileid != undefined) {
                var prevtermparent = ui.item.prev().find('.parent').html();
                if (prevtermparent != termparent) prevtileid = undefined;
            }

            var nexttermparent = undefined;
            if (nexttileid != undefined) {
                nexttermparent = ui.item.next().find('.parent').html();
                if (nexttermparent != termparent) nexttileid = undefined;
            }

            // if previous and next not at same tree level, or next not at same tree level and the previous is the parent of the next, or just moved item beneath its own children
            if (( prevtileid == undefined && nexttileid == undefined ) || ( nexttileid == undefined && nexttermparent == prevtileid ) || ( nexttileid != undefined && prevtermparent == tileid )) {
                $("table.widefat.wp-list-table").sortable('cancel');
                return;
            }

            // show spinner
            ui.item.find('.check-column input').hide().after('<img alt="processing" src="images/wpspin_light.gif" class="waiting" style="margin-left: 6px;" />');
            // go do the sorting stuff via ajax
            $.post(ajaxurl, {
                action: 'wc_pos_tile_ordering',
                id: tileid,
                nextid: nexttileid,
                grid_id: grid_id
            }, function (response) {
                ui.item.find('.check-column input').show().siblings('img').remove();
            });

            // fix cell colors
            var d = new Array;
            var ii = 0;
            $('table.widefat tbody tr').each(function () {
                d[ii] = 'title_' + $(this).find('.check-column input').val();
                ii++;
                var i = jQuery('table.widefat tbody tr').index(this);
                if (i % 2 == 0) jQuery(this).addClass('alternate');
                else jQuery(this).removeClass('alternate');
            });
            var new_html = '';
            var j = 0;
            var t = 0;
            $.each(d, function (index, value) {
                j++;
                t++;
                if (t == 1) {
                    new_html += '<div><table><tbody>';
                }
                if (j == 1) new_html += '<tr>';
                var css_ = $('#wc-pos-register-grids tbody td#' + value).attr('style');
                new_html += '<td id="' + value + '" style="' + css_ + '" class="title_product">' + $('#wc-pos-register-grids tbody td#' + value).html() + '</td>';
                if (j == 5) {
                    new_html += '</tr>';
                    j = 0;
                    if (t == 25) {
                        t = 0;
                        new_html += '</tbody></table></div>';
                    }
                }
                ;
            });
            if (j != 0) {
                var a = j + 1;
                for (a; a <= 5; a++) {
                    new_html += '<td></td>';
                    if (a == 5) new_html += '</tr>';
                }
                new_html += '</tbody></table></div>';
            } else {
                if (t != 25) {
                    t = 0;
                    new_html += '</tbody></table></div>';
                }
            }
            console.log(new_html);
            $('#grid_layout_cycle').html(new_html);
            $('.previous-next-toggles #nav_layout_cycle').html('');
            $('#grid_layout_cycle').cycle({

                speed: 'fast',
                timeout: 0,
                pager: '.previous-next-toggles #nav_layout_cycle',
                next: '.previous-next-toggles .next-grid-layout',
                prev: '.previous-next-toggles .previous-grid-layout'
            });
        }
    });
});
