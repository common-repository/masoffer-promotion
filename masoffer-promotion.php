<?php
/**
 * Plugin Name: MasOffer Promotion
 * Plugin URI: https://masoffer.com
 * Description: Plugin hỗ trợ hiển thị mã giảm giá
 * Version: 2.1.4
 * Author: MasOffer
 * License: GPLv2 or later
 */
?>
<?php
if (!class_exists('MasOfferPromotionAPI')) {
    class MasOfferPromotionAPI
    {
        const PLUGIN_VERSION = '2.1.4';

        public function __construct()
        {
            // Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
            register_activation_hook(__FILE__, array($this, 'activate'));

            //Shortocde button init
            add_action('init', array($this, 'mo_shortcode_button_init'));

            //Add shortcode
            add_shortcode('mopromo', array($this, 'shortcode_mo_promotion'));

            // Add extra submenu to the admin panel
            add_action('admin_menu', array($this, 'create_menu_admin_panel'));

            // Handle POST request, admin_action_($action)
            add_action('admin_action_masoffer_promotion_action', array($this, 'masoffer_promotion_admin_action'));

            //Add API get data
            add_action('rest_api_init', function () {
                register_rest_route('mo_get_promo/v1', '/get', array(
                    'methods'  => 'GET',
                    'callback' => array($this, 'ajaxAPIGetData'),
                    'permission_callback' => '__return_true'
                ));
            });

            //Add API get filter for short-code editor
            add_action('rest_api_init', function () {
                register_rest_route('mo_get_promo/v1', '/getFilter', array(
                    'methods'  => 'GET',
                    'callback' => array($this, 'ajaxAPIGetFilter'),
                    'permission_callback' => '__return_true'
                ));
            });
        }

        public function activate($network_wide)
        {
            // if the WordPress version is older than 2.6, deactivate this plugin
            // admin_action_ hook appearance 2.6
            if (version_compare(get_bloginfo('version'), '2.6', '<')) {
                deactivate_plugins(basename(__FILE__));
            } else {
                $data     = array(
                    'publisher_id' => '',
                    'token'        => '',
                    'color'        => '',
                    'display'      => '',
                    'domain'       => '',
                    'protocol'     => '',
                    'offers'       => array(),
                    'updated_at'   => current_time('d-m-Y H:i:s')
                );
                add_option('masoffer_promo', $data, '', 'no');
            }
        }

        //Get filter
        public function ajaxAPIGetFilter()
        {
            $data = get_option('masoffer_promo');

            $result = [];
            if (isset($data['offers'])) {
                $result = $data['offers'];
            } else {
                $offers = $this->getAllOfferIDFromAPI($data);
                foreach ($offers as $id => $avatar) {
                    $offers[sanitize_text_field($id)] = sanitize_text_field($avatar);
                }
                $data['offers']   = $offers;
                $data['updated_at'] = current_time('d-m-Y H:i:s');
                update_option('masoffer_promo', $data);
                $result = $offers;
            }
            $output = [];
            foreach (array_keys($result) as $offerid) {
                $output[] = [
                    'text'  => $offerid,
                    'value' => $offerid
                ];
            }
            return json_encode($output);
        }

        function getAllOfferIDFromAPI($option)
        {
            $url    = "https://publisher-api.masoffer.net/offer/all?pub_id={$option['publisher_id']}&token={$option['token']}";
            $result = wp_remote_get($url);
            if (is_wp_error($result)) {
                return [];
            }
            $result = json_decode($result['body'], true)['data'];
            if (empty($result)) {
                return [];
            }
            foreach ($result as $row) {
                $offers[$row['offer_id']] = $row['avatar'];
            }
            return $offers;
        }

        //Shortcode button
        public function mo_shortcode_button_init()
        {
            //Abort early if the user will never see TinyMCE
            if (!current_user_can('edit_posts') && !current_user_can('edit_pages') && get_user_option('rich_editing') == 'true')
                return;

            //Add a callback to regiser our tinymce plugin
            add_filter("mce_external_plugins", array($this, "mo_register_tinymce_plugin"));

            // Add a callback to add our button to the TinyMCE toolbar
            add_filter('mce_buttons', array($this, 'mo_add_tinymce_button'));
        }

        //Add shortcode
        public function shortcode_mo_promotion($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'offerid'  => 'shopee',
                'take'     => 5,
                'coupon'   => 'all',
                'category' => '',
                'orderby'   => 'created_at',
                'ordertype' => 'desc',
            ), $atts));

            $category = urlencode($category);

            ob_start();
            include 'views/promotions.php';

            $cssSrc           = plugins_url('css/view.css', __FILE__);
            $scriptPromotions = plugins_url('js/promotions.js', __FILE__);
            wp_enqueue_style('view-css', $cssSrc, [], self::PLUGIN_VERSION, false);
            if (!wp_script_is('jquery', 'enqueued')) {
                wp_enqueue_script('jquery-3.4.1', 'https://code.jquery.com/jquery-3.4.1.min.js', array(), null, true);
            }
            wp_enqueue_script('script-promotions', $scriptPromotions, array(), self::PLUGIN_VERSION, true);

            $content = ob_get_clean();
            return $content;
        }

        function ajaxAPIGetData(WP_REST_Request $request)
        {
            $data       = get_option('masoffer_promo');
            $parameters = $request->get_query_params();
            $category   = urlencode(htmlspecialchars_decode($parameters['category']));
            $url        = "http://publisher-api.masoffer.net/v1/promotions?token={$data['token']}&publisher_id={$data['publisher_id']}&offer_id={$parameters['offer_id']}&category={$category}&limit={$parameters['take']}&type={$parameters['coupon']}&order_by={$parameters['order_by']}&order={$parameters['order_type']}";
            $display    = $data['display'];
            $logo = $data['offers'][$parameters['offer_id']] ? $data['offers'][$parameters['offer_id']] : '';
            $result = wp_remote_get($url, ['timeout' => 10]);
            if (is_wp_error($result)) {
                return ['status' => false, 'data' => $result, 'publisher_id' => $data['publisher_id']];
            }
            $result = json_decode($result['body'], true);

            if (empty($data['protocol'])) {
                $data['protocol'] = 'https';
            }

            if (empty($data['domain'])) {
                $data['domain'] = 'rutgon.me';
            }

            foreach ($result['data'] as $index => $item) {
                $expertDate                             = DateTime::createFromFormat('d-m-Y H:i:s', $item['expired_date']);
                $result['data'][$index]['expired_date'] = $expertDate->format('d-m-Y');
                $result['data'][$index]['days_left']    = $expertDate->diff(new DateTime())->days;
                $parsed_url                             = parse_url($item['aff_link']);
                $result['data'][$index]['aff_link']     = "$data[protocol]://$data[domain]$parsed_url[path]" .
                    (isset($parsed_url["query"]) ? "?$parsed_url[query]" : "");
            }
            return ['status' => true, 'data' => $result, 'publisher_id' => $data['publisher_id'], 'display' => $display, 'logo' => $logo, 'promo_primary_color' => $data['color']];
        }

        //This callback registers our plug-in
        public function mo_register_tinymce_plugin($plugin_array)
        {
            $plugin_array['mo_promo_button'] = plugins_url('js/shortcode.js?v='.self::PLUGIN_VERSION, __FILE__);
            return $plugin_array;
        }

        //This callback adds our button to the toolbar
        public function mo_add_tinymce_button($buttons)
        {
            //Add the button ID to the $button array
            $buttons[] = "mo_promo_button";
            return $buttons;
        }

        public function create_menu_admin_panel()
        {
            add_options_page('MasOffer Options', 'MasOffer Promotion',
                'manage_options', 'masoffer-promotion-official', array($this, 'masoffer_promotion_plugin_form'));
        }

        public function masoffer_promotion_plugin_form()
        {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permission to access this page.'));
            }
            $data = get_option('masoffer_promo');
            $publisher_id = isset($data['publisher_id']) ? $data['publisher_id'] : '';
            $token        = isset($data['token']) ? $data['token'] : '';
            $color        = isset($data['color']) ? $data['color'] : '';
            $display      = isset($data['display']) ? $data['display'] : '';
            $domain       = isset($data['domain']) ? $data['domain'] : '';
            $protocol     = isset($data['protocol']) ? $data['protocol'] : '';
            $offers       = isset($data['offers']) ? $data['offers'] : [];
            $updated_at   = isset($data['updated_at']) ? $data['updated_at'] : '';
            esc_attr($publisher_id);
            esc_attr($token);
            esc_attr($color);
            esc_attr($display);
            esc_attr($domain);
            esc_attr($protocol);
            esc_attr($updated_at);
            $packing_domains = $this->get_parking_domains($publisher_id, $token);
            include 'views/admin.php';
        }

        public function masoffer_promotion_admin_action()
        {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'update-info-mo_')) {
                wp_die(__('You do not have sufficient permission to save this form.'));
            }
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permission to save this form.'));
            }

            $data = get_option('masoffer_promo');

            if (!isset($_POST['update-offer']) && isset($_POST['publisher_id'])) {
                $data['publisher_id'] = sanitize_text_field($_POST['publisher_id']);
                $data['token']        = $_POST['token'];
                $data['color']        = sanitize_text_field($_POST['color']);
                $data['display']      = sanitize_text_field($_POST['display']);
                $data['domain']       = sanitize_text_field($_POST['domain']);
                $data['protocol']     = sanitize_text_field($_POST['protocol']);
            }

            $offers = $this->getAllOfferIDFromAPI($data);
            foreach ($offers as $id => $avatar) {
                $offers[sanitize_text_field($id)] = sanitize_text_field($avatar);
            }

            $data['offers']     = $offers;
            $data['updated_at'] = current_time('d-m-Y H:i:s');

            update_option('masoffer_promo', $data);

            wp_safe_redirect('/wp-admin/options-general.php?page=masoffer-promotion-official');
            exit();
        }

        public function get_parking_domains($publisher_id, $token) {
            $parking_domains = array(
                'gotrackecom.info',
                'gotrackecom.asia',
                'gotrackecom.biz',
                'gotrackecom.xyz',
                'rutgon.me',
            );

            if (empty($publisher_id) || empty($token)) {
                return $parking_domains;
            }

            $publisher_id = urlencode($publisher_id);
            $url = "http://publisher-api.masoffer.net/v1/domains?publisher_id=" . $publisher_id . '&token=' . $token;
            $response = wp_remote_get($url, ['timeout' => 10]);
            if (is_wp_error($response)) {
                return $parking_domains;
            }

            if (200 != wp_remote_retrieve_response_code($response)) {
                return $parking_domains;
            }

            if (!empty(json_decode($response['body'], true)['data'])) {
                foreach(json_decode($response['body'], true)['data'] as $parking_domain) {
                    $parking_domains[] = $parking_domain;
                }
            }

            return $parking_domains;
        }
    }

    $plugin_name = new MasOfferPromotionAPI();
}
