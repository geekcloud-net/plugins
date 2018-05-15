! function (a) {
    a(document).ready(function () {
        if(a("#custom-background-tiles-color").length > 0){
            var b, c = a("#custom-background-image1");
    		var bb, cc = a("#custom-background-tiles-color");
            a("#background_color").wpColorPicker({

                change: function (a, b) {
    				c.css("background-color", b.color.toString())
                },
                clear: function () {
                    c.css("background-color", "")
                }
            }),

    		a("#text-color").wpColorPicker({
                change: function (a, bb) {
    				cc.css("color", bb.color.toString())
                },
                clear: function () {
                    cc.css("color", "")
                }
            })

    		a('input[name="background-position-x"]').change(function () {
                c.css("background-position", a(this).val() + " top")
            }), a('input[name="background-repeat"]').change(function () {
                c.css("background-repeat", a(this).val())
            }), a("#choose-from-library-link").click(function (c) {
                var d = a(this);
                return c.preventDefault(), b ? void b.open() : (b = wp.media.frames.customBackground = wp.media({
                    title: d.data("choose"),
                    library: {
                        type: "image"
                    },
                    button: {
                        text: d.data("update"),
                        close: !1
                    }
                }), b.on("select", function () {
                    var c = b.state().get("selection").first();
                    a.post(ajaxurl, {
                        action: "set-background-image",
                        attachment_id: c.id,
                        size: "full"
                    }).done(function () {
                        window.location.reload()
                    })
                }), void b.open())
            })
        }
    })
}(jQuery);