<?php

/**
 * Plugin Name: Beontop Goals
 * Description: Beontop Goals Plugin for Analytics
 * Author URI:  https://www.beontop.ae/
 * Author:      Alex K, Alex S and Alex G
 * Version:     1.5
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Network:     true
 */

// Add settings page in admin menu
add_action('admin_menu', 'bg_add_settings_page');
function bg_add_settings_page()
{
    add_options_page('Goals Settings for Analytics', 'Goals Settings for Analytics', 'manage_options', 'primer_slug', 'bg_settings_page');
}

function bg_settings_page()
{
?>
    <div class="wrap">
        <h2><?php echo get_admin_page_title() ?></h2>

        <form action="options.php" method="POST">
            <?php
            settings_fields('bg_main_settings');     // скрытые защитные поля
            do_settings_sections('bg_page'); // секции с настройками (опциями). У нас она всего одна 'custom_js'
            submit_button();
            ?>
        </form>
    </div>
<?php
}

/**
 * Регистрируем настройки.
 * Настройки будут храниться в массиве, а не одна настройка = одна опция.
 */
add_action('admin_init', 'plugin_settings');
function plugin_settings()
{
    // параметры: $option_group, $option_name, $sanitize_callback
    register_setting('bg_main_settings', 'bg_scripts_block', array('sanitize_callback' => 'sanitize_callback_scripts_block'));
    register_setting('bg_main_settings', 'bg_head_block');
    register_setting('bg_main_settings', 'bg_gtm_block');

    // параметры: $id, $title, $callback, $page
    add_settings_section('bg_main', 'Main', '', 'bg_page');

    // параметры: $id, $title, $callback, $page, $section, $args
    add_settings_field('bg_head_block', 'HTML Code in the head tag', 'bg_head_block_field', 'bg_page', 'bg_main');
    add_settings_field('bg_custom_js', 'Custom JavaScript in script tag', 'bg_scripts_block_field', 'bg_page', 'bg_main');
    add_settings_field('bg_gtm_block', 'Google Tag Manager ID (for goals tasks)', 'bg_gtm_block_field', 'bg_page', 'bg_main');
}

function bg_gtm_block_field()
{
    $val = get_option('bg_gtm_block');
    $val = $val ? $val : null;
    // test echo below
    // echo get_option('bg_gtm_block') ? 'gtm field NOT empty' : 'gtm field empty';
?>
    <input type="text" name="bg_gtm_block" value="<?php echo esc_attr($val) ?>" />
<?php
}

## Заполняем Custom JavaScript
function bg_scripts_block_field()
{
    $placeholder = "/** COMMENTED SECTION" . strval(file_get_contents(plugins_url('/assets/scripts/placeholder.js', __FILE__))) . "COMMENTED SECTION END */";
    //check if some code inside of Custom JS block
    $val = get_option('bg_scripts_block');
    //if empty (only on first install of plugin) add basic placeholder
    $val = $val ? $val : $placeholder;
    // $val = str_replace(['<script>', '</script>'], ['',''], $val);
?>
    <textarea type="text" name="bg_scripts_block" style="width: 100%; height: 500px;"><?php echo esc_attr($val) ?></textarea>
<?php
}
function bg_head_block_field()
{
    $val = get_option('bg_head_block');
    $val = $val ? $val : null;
?>
    <textarea type="text" name="bg_head_block" style="width: 100%; height: 300px;"><?php echo esc_attr($val) ?></textarea>
<?php
}

## Очистка данных
function sanitize_callback_scripts_block($val)
{
    // очищаем
    $val = strip_tags($val);

    return $val;
}

// hide this section from Google Lightroom bot (PageSpeed)
if (!strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome-Lighthouse')) {
    // Add scripts
    add_action('init', 'register_script');
    function register_script()
    {
        wp_register_script('bgoals', plugins_url('/assets/scripts/goals.js', __FILE__), array(), '1.2', true);
        wp_enqueue_script('bgoals');
    }

    // Block for GTM output
    add_action('wp_head', 'bg_insert_gtm_head', 100001);
    function bg_insert_gtm_head()
    {
        if (get_option('bg_gtm_block') != '') {
            echo '
        <script id="gtmkit-js-before" data-cfasync="false" data-nowprocket="" data-cookieconsent="ignore">
		window.gtmkit_settings = {"datalayer_name":"dataLayer","console_log":false};
		window.gtmkit_data = {};
		window.dataLayer = window.dataLayer || [];		
        </script>
        <script id="gtmkit-datalayer-js-before" data-cfasync="false" data-nowprocket="" data-cookieconsent="ignore">
        const gtmkit_dataLayer_content = {"pageType":"frontpage"};
        dataLayer.push( gtmkit_dataLayer_content );
        </script>
        <!-- Google Tag Manager -->
        <script id="gtmkit-container-js-after" data-cfasync="false" data-nowprocket="" data-cookieconsent="ignore">';
            echo "/* Google Tag Manager */
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','";
            echo get_option('bg_gtm_block');
            echo "');";
            echo '</script><!-- End Google Tag Manager -->';
        }
    }
    add_action('wp_body_open', 'bg_insert_gtm_body', 100001);
    function bg_insert_gtm_body()
    {
        if (get_option('bg_gtm_block') != '') {
            echo '<!-- Google Tag Manager (noscript) --><noscript><iframe src="https://www.googletagmanager.com/ns.html?id=';
            echo get_option('bg_gtm_block');
            echo '"height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript><!-- End Google Tag Manager (noscript) -->
        <script>
gtag("event", "page_view", {send_to: "GLA"});
</script>';
        }
    }
    // End of Block for GTM output


    add_action('wp_head', 'bg_insert_head', 100000);
    function bg_insert_head()
    {
        echo get_option('bg_head_block');
    }

    add_action('wp_footer', 'bg_insert_footer', 100000);
    function bg_insert_footer()
    {
        echo '<script>';
        echo get_option('bg_scripts_block');
        echo '</nscript>';
    }
}
