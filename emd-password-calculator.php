<?php
/**
 * Plugin Name: EMD Password Calculator
 * Plugin URI: https://github.com/jcjason12108-alt/EMD-Password-Calculator-Plugin/
 * Description: Displays today’s and yesterday’s EMD password (UTC). Includes an accessible calculation breakdown.
 * Version: 2.2.4
 * Requires at least: 5.2
 * Tested up to: 6.9.4
 * Requires PHP: 7.4
 * Author: Jason Cox
 * Author URI: https://iamll706.org
 * License: GPLv2 or later
 * Text Domain: emd-password-calculator
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'EMD_PWC_VERSION', '2.2.4' );
define( 'EMD_PWC_URL', plugin_dir_url( __FILE__ ) );
define( 'EMD_PWC_PATH', plugin_dir_path( __FILE__ ) );

require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';

$emd_pwc_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
    'https://github.com/jcjason12108-alt/EMD-Password-Calculator-Plugin/',
    __FILE__,
    'emd-password-calculator'
);
$emd_pwc_update_checker->setBranch( 'main' );

add_filter(
    $emd_pwc_update_checker->getUniqueName( 'vcs_update_detection_strategies' ),
    static function ( array $strategies ): array {
        return isset( $strategies['branch'] ) ? array( 'branch' => $strategies['branch'] ) : $strategies;
    }
);

$emd_pwc_github_token = defined( 'PLUGIN_UPDATE_GITHUB_TOKEN' )
    ? PLUGIN_UPDATE_GITHUB_TOKEN
    : getenv( 'PLUGIN_UPDATE_GITHUB_TOKEN' );

if ( ! empty( $emd_pwc_github_token ) ) {
    $emd_pwc_update_checker->setAuthentication( $emd_pwc_github_token );
}

/**
 * Load textdomain for translations.
 */
function emd_pwc_load_textdomain() {
    load_plugin_textdomain( 'emd-password-calculator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'emd_pwc_load_textdomain' );

/**
 * Shortcode: [emd_password_calc]
 */
function emd_pwc_shortcode( $atts ) {
    // Enqueue assets only when shortcode is used
    wp_enqueue_style(
        'emd-pwc-style',
        EMD_PWC_URL . 'assets/css/emd-password-calculator.css',
        array(),
        EMD_PWC_VERSION
    );

    wp_enqueue_script(
        'emd-pwc-script',
        EMD_PWC_URL . 'assets/js/emd-password-calculator.js',
        array(),
        EMD_PWC_VERSION,
        true
    );

    // Localized/i18n strings
    wp_localize_script( 'emd-pwc-script', 'EMD_PWC_I18N', array(
        'today'            => __( "Today’s Password (UTC)", 'emd-password-calculator' ),
        'yesterday'        => __( "Yesterday’s Password (UTC)", 'emd-password-calculator' ),
        'showCalc'         => __( 'Show Calculation', 'emd-password-calculator' ),
        'hideCalc'         => __( 'Hide Calculation', 'emd-password-calculator' ),
        'loading'          => __( 'Loading…', 'emd-password-calculator' ),
        'digitSum'         => __( 'Digit sum', 'emd-password-calculator' ),
        'lastDigit'        => __( 'Last digit', 'emd-password-calculator' ),
        'yearLastDigit'    => __( 'Last digit of year', 'emd-password-calculator' ),
        'dayReversed'      => __( 'Day, reversed', 'emd-password-calculator' ),
        'password'         => __( 'Password', 'emd-password-calculator' ),
        'month'            => __( 'Month', 'emd-password-calculator' ),
        'day'              => __( 'Day', 'emd-password-calculator' ),
        'year'             => __( 'Year', 'emd-password-calculator' ),
        'example'          => __( 'Calculation', 'emd-password-calculator' ),
        'todaysDate'      => __( "Today's Date (UTC)", 'emd-password-calculator' ),
        'writeDown'       => __( 'Write down the red numbers right to left', 'emd-password-calculator' ),
        'passwordEq'      => __( 'Password', 'emd-password-calculator' ),
        'legend'          => __( 'Last digit of the sum, last digit of the year, day reversed', 'emd-password-calculator' ),
    ) );

    // Server-side UTC timestamps for consistent date/password across clients
    $now_utc = time();
    wp_localize_script( 'emd-pwc-script', 'EMD_PWC_DATA', array(
        'now'       => $now_utc,
        'yesterday' => $now_utc - DAY_IN_SECONDS,
    ) );

    ob_start();
    ?>
    <div class="emd-pwc-card" role="region" aria-label="<?php echo esc_attr__( 'EMD Password Calculator', 'emd-password-calculator' ); ?>">
        <h3 class="emd-pwc-title"><?php echo esc_html__( "EMD Password Calculator", 'emd-password-calculator' ); ?></h3>

        <div class="emd-pwc-row">
            <div class="emd-pwc-col">
                <div class="emd-pwc-label" id="emd-pwc-today-label"></div>
                <div class="emd-pwc-pass" id="emd-pwc-today-pass"><?php echo esc_html__( 'Loading…', 'emd-password-calculator' ); ?></div>
                <button class="emd-pwc-toggle" aria-expanded="false" aria-controls="emd-pwc-today-calc" id="emd-pwc-today-toggle"></button>
                <div class="emd-pwc-calc" id="emd-pwc-today-calc" hidden></div>
            </div>

            <div class="emd-pwc-col">
                <div class="emd-pwc-label" id="emd-pwc-yest-label"></div>
                <div class="emd-pwc-pass" id="emd-pwc-yest-pass"><?php echo esc_html__( 'Loading…', 'emd-password-calculator' ); ?></div>
                <button class="emd-pwc-toggle" aria-expanded="false" aria-controls="emd-pwc-yest-calc" id="emd-pwc-yest-toggle"></button>
                <div class="emd-pwc-calc" id="emd-pwc-yest-calc" hidden></div>
            </div>
        </div>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'emd_password_calc', 'emd_pwc_shortcode' );


/**
 * Add shortcode info to the plugin row (Plugins page).
 */
function emd_pwc_action_links( $links ) {
    $links[] = '<span style="white-space:nowrap;">' . esc_html__( 'Shortcode:', 'emd-password-calculator' ) . ' <code>[emd_password_calc]</code></span>';
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'emd_pwc_action_links' );

/**
 * Add row meta with shortcode as well (below description).
 */
function emd_pwc_row_meta( $links, $file ) {
    if ( plugin_basename( __FILE__ ) === $file ) {
        $links[] = '<span style="white-space:nowrap;">' . esc_html__( 'Shortcode:', 'emd-password-calculator' ) . ' <code>[emd_password_calc]</code></span>';
    }
    return $links;
}
add_filter( 'plugin_row_meta', 'emd_pwc_row_meta', 10, 2 );


/**
 * Admin: Settings page (Settings → EMD Password Calculator)
 */
function emd_pwc_admin_menu() {
    add_options_page(
        __('EMD Password Calculator', 'emd-password-calculator'),
        __('EMD Password Calc', 'emd-password-calculator'),
        'manage_options',
        'emd-password-calculator',
        'emd_pwc_admin_page_render'
    );
}
add_action('admin_menu', 'emd_pwc_admin_menu');

/**
 * Enqueue admin assets only on our settings page.
 */
function emd_pwc_admin_enqueue($hook_suffix) {
    if ( isset($_GET['page']) && $_GET['page'] === 'emd-password-calculator' ) {
        wp_enqueue_style('emd-pwc-admin', EMD_PWC_URL . 'assets/css/emd-pwc-admin.css', array(), EMD_PWC_VERSION);
        wp_enqueue_script('emd-pwc-admin', EMD_PWC_URL . 'assets/js/emd-pwc-admin.js', array(), EMD_PWC_VERSION, true);
    }
}
add_action('admin_enqueue_scripts', 'emd_pwc_admin_enqueue');

/**
 * Render the settings page.
 */
function emd_pwc_admin_page_render() {
    if ( ! current_user_can('manage_options') ) { return; }
    ?>
    <div class="wrap emd-pwc-admin-wrap">
        <h1><?php echo esc_html__( 'EMD Password Calculator', 'emd-password-calculator' ); ?></h1>
        <p class="description"><?php echo esc_html__( 'Use this shortcode anywhere:', 'emd-password-calculator' ); ?></p>

        <div class="emd-pwc-shortcode-row">
            <input type="text" class="regular-text code emd-pwc-shortcode-input" readonly value="[emd_password_calc]" />
            <button type="button" class="button button-primary emd-pwc-copy"><?php echo esc_html__( 'Copy', 'emd-password-calculator' ); ?></button>
            <span class="emd-pwc-copied" aria-live="polite" hidden><?php echo esc_html__( 'Copied!', 'emd-password-calculator' ); ?></span>
        </div>

        <hr />

        <h2><?php echo esc_html__( 'Preview', 'emd-password-calculator' ); ?></h2>
        <p class="description"><?php echo esc_html__( 'Calculation and preview use UTC.', 'emd-password-calculator' ); ?></p>
        <div class="emd-pwc-preview">
            <?php echo do_shortcode('[emd_password_calc]'); ?>
        </div>
    </div>
    <?php
}
