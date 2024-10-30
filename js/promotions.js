var thisUrl = window.location.href;
var $ = jQuery;

function redirectAff(e) {
    let code = $(e).data("code");

    $(e).find('.mopromo-ip-code').prop('disabled',false).select();
    document.execCommand("copy");
    $(e).find('.mopromo-get').text('Đã copy!').parent().css({'font-weight':'bold','border-bottom-color':'#006EC8'});
    $(e).find('.mopromo-get').parent().css({'border-bottom-color':'rgb(255, 132, 114)'});
    $(e).find('.mopromo-ip-code').prop('disabled',true);

    //Show code after click button
    setTimeout(function() {
        let html = '';
        html += '<input style="background-color: #FFFFD7;" class="mopromo-ip-code" type="text" value="' + code + '" readonly>';
        $(e).html(html);
        $(e).attr('onclick', 'return false;');
    },500);
}

$(document).ready(function () {
    $('head').append('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">');
    function moPromotionOnload() {
        function isEmpty(str) {
            return (!str || 0 === str.length || $.trim(str) === "");
        }

        function isBlank(str) {
            return (!str || /^\s*$/.test(str));
        }

        String.prototype.isEmpty = function () {
            return (this.length === 0 || !this.trim());
        };

        function loadDataMO() {
            let sections = $(".mopromo-wrapper");
            $.each(sections, function (index, elem) {
                let apiGetPromo = $(elem).find(".mopromo-api-get-promo");
                $.get(apiGetPromo.val(), function (response) {
                    let html = "";
                    if (response.status == true) {
                        let promotions = response.data.data;
                        let thisUrl = window.location.href;
                        let url = new URL(thisUrl);
                        let couponCode = url.searchParams.get("couponCode");
                        $.each(promotions, function (index, elem) {
                            if (response.display !== 'logo') {
                                var mo_promo_value = '';
                                if (elem.type === 'promotion') {
                                    mo_promo_value = 'KM';
                                }
                                if (elem.type === 'coupon') {
                                    if (typeof elem.discount === 'undefined' || elem.discount.isNaN || elem.discount == 0) {
                                        mo_promo_value = 'KM';
                                    }
                                    else {
                                        if (elem.discount_type === 'rate') {
                                            mo_promo_value = Math.round(elem.discount * 10000)/100 + '%';
                                        } else if (elem.discount_type === 'fixed') {
                                            mo_promo_value = Math.round(elem.discount / 1000) + 'K';
                                        } else {
                                            mo_promo_value = 'KM';
                                        }
                                    }
                                }
                            }

                            if (elem.days_left === 0) {
                                elem.days_left = "Hết hạn hôm nay";
                            } else {
                                elem.days_left = "Còn " + elem.days_left + " ngày";
                            }
                            let cateName = isEmpty(elem.category_name) ? "" : '                    <div class="mopromo-info"><strong>Ngành hàng: </strong><span>' + elem.category_name + '</span></div>';
                            let description = isEmpty(elem.description) ? "" : '                    <div class="mopromo-info"><strong>Lưu ý: </strong><span>' + elem.description + '</span></div>';

                            html += '    <div class="promo-row">';
                            html += '        <div class="mopromo-col-pre mopromo-inline-div">';
                            html += '            <div class="thumbnail-div">';
                            html += '                <span class="thumbnail">';
                            html += '                    <a href="' + elem.aff_link + '" data-url=""  target="_blank">';
                            html += '                        <div class="mopromo-image" style="border-color: '+ response.promo_primary_color +'">';
                            html += '                            <div class="mopromo-type" style="color: '+ response.promo_primary_color +'">' + elem.type + '</div>';

                            if (response.display === 'logo') {
                                html += '                        <div class="mopromo-offer-logo">'
                                html += '                           <img src="' + response.logo + '" />'
                                html += '                        </div>'
                            }
                            else {
                                html += '                        <div class="mopromo-value" style="color: '+ response.promo_primary_color +'">' + mo_promo_value + '</div>';
                            }

                            html += '                        </div>';
                            html += '                    </a>';
                            html += '                </span>';
                            html += '            </div>';
                            html += '        </div>';

                            html += '        <div class="col-mid mopromo-inline-div">';
                            html += '            <div class="detail-div">';
                            html += '                <span class="detail">';
                            html += '                    <a class="mopromo-title" href="' + elem.aff_link + '" data-url=""  target="_blank">' + elem.title + '</a>';
                            html += cateName;
                            html += description;
                            html += '                </span>';
                            html += '            </div>';
                            html += '        </div>';

                            html += '        <div class="mopromo-col-oth mopromo-inline-div">';
                            html += '            <div class="mopromo-expired">' + elem.days_left + '</div>';

                            if (!isEmpty(elem.coupon_code) && couponCode !== elem.coupon_code) {
                                html += '            <a class="mopromo-get-code" onclick="redirectAff(this)" href="' + elem.aff_link + '" data-code="' + elem.coupon_code + '" data-url=""  target="_blank">';
                                html += '                <div class="mopromo-sub-wrapper">';
                                html += '                    <div class="mopromo-get">Lấy mã</div>';
                                html += '                </div>';
                                html += '                <input class="mopromo-ip-code" type="text" value="' + elem.coupon_code + '" disabled>';
                                html += '            </a>';
                            } else if (couponCode === elem.coupon_code) {
                                html += '            <div class="mopromo-get-code" onclick="JavaScript:void(0);" href="#">';
                                html += '                <input style="background-color: #FFFFD7;" class="mopromo-ip-code" type="text" value="' + elem.coupon_code + '" readonly>';
                                html += '            </div>';
                            } else {
                                html += '            <a class="mopromo-get-code mopromo-get-promo" href="' + elem.aff_link + '" data-url="" target="_blank">nhận ưu đãi</a>';
                            }
                            html += '        </div>';
                            html += '    </div>';
                        });
                    } else {
                        moPromotionOnload();
                    }
                    $(elem).html('');
                    $(elem).append(html);
                    let getpromo = $('.mopromo-wrapper .mopromo-get-promo');
                    getpromo.css("background-color", response.promo_primary_color);
                    getpromo.hover(function () {
                        $(this).css("color", response.promo_primary_color + 'BF');
                        $(this).css("border-color", response.promo_primary_color + 'BF');
                        $(this).css("background-color", 'white');
                    }, function () {
                        $(this).css("background-color", response.promo_primary_color);
                        $(this).css("border-color", 'transparent');
                        $(this).css("color", 'white');
                    });

                    $('.mopromo-wrapper .mopromo-get-code .mopromo-sub-wrapper').css("border-bottom-color", response.promo_primary_color);
                });
            });
        }
        loadDataMO();
    }
    moPromotionOnload();
});
