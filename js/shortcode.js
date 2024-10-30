(function ($) {
    let getFilter = [];
    $.get( "/wp-json/mo_get_promo/v1/getFilter", function( response ) {
        return getFilter = JSON.parse(response);
    });

    //Tinymce
    tinymce.create('tinymce.plugins.mo_promotion', {
        init : function(ed, url) {
            ed.addCommand('mo_command', function() {
                var win = ed.windowManager.open({
                    title: 'Properties',
                    body: [
                        {
                            type   : 'listbox',
                            name   : 'offerid',
                            label  : 'Offer',
                            minWidth: 300,
                            values : getFilter,
                        },
                        {
                            type   : 'listbox',
                            name   : 'category',
                            label  : 'Category',
                            minWidth: 300,
                            values : [
                                {text : 'Tất cả', value : ''},
                                {text : 'Thời trang & Phụ kiện', value : 'Thời trang & Phụ kiện'},
                                {text : 'Sức khỏe & Làm đẹp', value : 'Sức khỏe & Làm đẹp'},
                                {text : 'Điện tử & Công nghệ', value : 'Điện tử & Công nghệ'},
                                {text : 'Phụ kiện & Điện thoại', value : 'Phụ kiện & Điện thoại'},
                                {text : 'Mẹ & bé', value : 'Mẹ & bé'},
                                {text : 'Máy tính & Laptop', value : 'Máy tính & Laptop'},
                                {text : 'Nhà cửa & Đời sống', value : 'Nhà cửa & Đời sống'},
                                {text : 'Ô tô & Xe máy', value : 'Ô tô & Xe máy'},
                                {text : 'Thể thao & Dã ngoại', value : 'Thể thao & Dã ngoại'},
                                {text : 'Sách, Văng phòng phẩm & Quà tặng', value : 'Sách, Văng phòng phẩm & Quà tặng'},
                                {text : 'Hàng tiêu dùng thực phẩm', value : 'Hàng tiêu dùng thực phẩm'},
                                {text : 'Thú cưng', value : 'Thú cưng'},
                                {text : 'Khác', value : 'Khác'}
                            ],
                        },
                        {
                            type   : 'listbox',
                            name   : 'coupon',
                            label  : 'Coupon',
                            minWidth: 300,
                            values : [
                                {text : 'Tất cả', value : 'all'},
                                {text : 'Mã giảm giá', value : 'coupon'},
                                {text : 'Khuyến mại', value : 'promotion'}
                            ],
                        },
                        {
                            type   : 'textbox',
                            name   : 'take',
                            label  : 'Số lượng',
                            minWidth: 300,
                            value : 5
                        },
                        {
                            type   : 'listbox',
                            name   : 'orderby',
                            label  : 'Order by',
                            minWidth: 300,
                            values : [
                                {text : 'Thời gian tạo', value : 'created_at'},
                                {text : 'Thời gian gian hết hạn', value : 'expired_date'}
                            ],
                        },
                        {
                            type   : 'listbox',
                            name   : 'ordertype',
                            label  : 'Order type',
                            minWidth: 300,
                            values : [
                                {text : 'Tăng dần', value : 'asc'},
                                {text : 'Giảm dần', value : 'desc'}
                            ],
                        },
                    ],
                    buttons: [
                        {
                            text: "Ok",
                            subtype: "primary",
                            onclick: function() {
                                win.submit();
                            }
                        },
                        {
                            text: "Cancel",
                            onclick: function() {
                                win.close();
                            }
                        }
                    ],
                    onsubmit: function(e){
                        var shortCode = '';
                        if( e.data.offerid.length > 0 ) {
                            shortCode = '[mopromo offerid="'+ e.data.offerid + '" category="' + e.data.category + '" coupon="' + e.data.coupon + '" take="' + e.data.take + '" orderby="' + e.data.orderby + '" ordertype="' + e.data.ordertype + '"';
                            shortCode = shortCode + ']';
                            ed.execCommand('mceInsertContent', 0, shortCode);
                        } else {
                            alert('Không được bỏ trống OfferId');
                        }
                    }
                });
            });
            ed.addButton('mo_promo_button', {
                title : 'Insert promotion',
                image: url + '/../images/logo_post.png' ,
                cmd: 'mo_command'
            });
        },
    });

    tinymce.PluginManager.add('mo_promo_button', tinymce.plugins.mo_promotion);

})(jQuery);
