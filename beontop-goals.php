<?php

/**
 * Plugin Name: Beontop Goals
 * Description: BeOnTop Goals Plugin
 * Author URI:  https://www.beontop.ae/
 * Author:      Alex K and Alex S
 * Version:     1.1
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
    add_options_page('Goals Settings', 'Goals Settings', 'manage_options', 'primer_slug', 'bg_settings_page');
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

    // параметры: $id, $title, $callback, $page
    add_settings_section('bg_main', 'Main', '', 'bg_page');

    // параметры: $id, $title, $callback, $page, $section, $args
    add_settings_field('bg_head_block', 'Code in the head tag', 'bg_head_block_field', 'bg_page', 'bg_main');
    add_settings_field('bg_custom_js', 'Custom JavaScript', 'bg_scripts_block_field', 'bg_page', 'bg_main');
}

## Заполняем опцию 1
function bg_scripts_block_field()
{
    $placeholder = "/**
    Example for Contact Form 7

    document.addEventListener('wpcf7mailsent', function sendMail(event) {
        if ('form_id' == event.detail.contactFormId) {
            goalsModule.trigger('goalName', 'goalCategory');
        }
    }, false);

    Example for WPForms

    (function repeat(){
        var element = document.getElementById('wpforms-confirmation-57');
        if(!element) return setTimeout(repeat, 1000);
        goalDone('Email Feedback','Email');
    }());

    Example for direct link event

    var body = document.querySelector('body');
    body.addEventListener('click', function (event) {
        var target = event.target;
        if (target.tagName !== 'a') {
            target = target.closest('a');
            if (target == null) return;
        }

    if (target.href.includes('/contacts/')) goalsModule.trigger('Send Request', 'Clicks');

    }, { passive: true });

*/

      ";
    $val = get_option('bg_scripts_block');
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


// Add scripts
add_action('init', 'register_script');
function register_script()
{
    wp_register_script('bgoals', plugins_url('/assets/scripts/goals.js', __FILE__), array(), '1.0.0', true);
    wp_enqueue_script('bgoals');
}

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
    echo '</script>';
}
