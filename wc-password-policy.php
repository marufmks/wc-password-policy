<?php
/**
 * Plugin Name: WooCommerce Password Policy
 * Plugin URI: https://github.com/marufmks/wc-password-policy
 * Description: A configurable password policy for WooCommerce which can be used to enforce password policy for WooCommerce to improve security.
 * Version: 1.0.0
 * Author: Maruf Khan
 * Author URI: https://github.com/marufmks
 * Text Domain: wc-password-policy
 * Domain Path: /languages
 * License: GPL-2.0+
 *
 * @package WC_Password_Policy
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


//define constant
define( 'WC_PASSWORD_POLICY_VERSION', '1.0' );
define( 'WC_PASSWORD_POLICY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_PASSWORD_POLICY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check if WooCommerce is active.
 *
 * @since 1.0.0
 * @return bool True if WooCommerce is active, false otherwise.
 */
function wc_password_policy_check_woocommerce() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'wc_password_policy_missing_wc_notice' );
        return false;
    }
    return true;
}

/**
 * Display admin notice for missing WooCommerce.
 *
 * @since 1.0.0
 * @return void
 */
function wc_password_policy_missing_wc_notice() {
    ?>
    <div class="error">
        <p><?php 
            printf( 
                __( 'WooCommerce Password Policy requires WooCommerce to be installed and active. You can download %s here.', 'wc-password-policy' ), 
                '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' 
            ); 
        ?></p>
    </div>
    <?php
}

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 * @return void
 */
function wc_password_policy_init() {
    if ( ! wc_password_policy_check_woocommerce() ) {
        return;
    }
    
    // Load plugin text domain
    load_plugin_textdomain( 'wc-password-policy', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'wc_password_policy_init' );

/**
 * Add Password Policy tab to WooCommerce settings.
 *
 * @since 1.0.0
 * @param array $settings_tabs Array of WooCommerce setting tabs.
 * @return array Modified array of setting tabs.
 */
function wc_password_policy_add_settings_tab( $settings_tabs ) {
    $settings_tabs['password_policy'] = __( 'Password Policy', 'wc-password-policy' );
    return $settings_tabs;
}
add_filter( 'woocommerce_settings_tabs_array', 'wc_password_policy_add_settings_tab', 50 );

/**
 * Add settings to the "Password Policy" tab.
 */
add_filter( 'woocommerce_settings_tabs_password_policy', 'wcpp_add_password_policy_settings' );
function wcpp_add_password_policy_settings() {
    woocommerce_admin_fields( wcpp_get_password_policy_settings() );
}

/**
 * Save settings from the "Password Policy" tab.
 */
add_action( 'woocommerce_update_options_password_policy', 'wcpp_save_password_policy_settings' );
function wcpp_save_password_policy_settings() {
    woocommerce_update_options( wcpp_get_password_policy_settings() );
}

/**
 * Get password policy settings array.
 */
function wcpp_get_password_policy_settings() {
    $settings = array(
        array(
            'title' => __( 'Password Policy Settings', 'wc-password-policy' ),
            'type'  => 'title',
            'desc'  => __( 'Configure the password policy for your WooCommerce store.', 'wc-password-policy' ),
            'id'    => 'password_policy_settings',
        ),
        array(
            'title'   => __( 'Enable Password Policy', 'wc-password-policy' ),
            'desc'    => __( 'Toggle to enable or disable the password policy.', 'wc-password-policy' ),
            'id'      => 'password_policy_enabled',
            'default' => 'no',
            'type'    => 'checkbox',
        ),
        array(
            'title'             => __( 'Minimum Password Length', 'wc-password-policy' ),
            'desc'              => __( 'Set the minimum number of characters required for passwords', 'wc-password-policy' ),
            'id'                => 'password_policy_min_length',
            'default'           => '8',
            'type'              => 'number',
            'custom_attributes' => array(
                'min'  => '1',
                'step' => '1'
            ),
        ),
        array(
            'title'   => __( 'Minimum Length Error Message', 'wc-password-policy' ),
            'desc'    => __( 'Error message shown when password length requirement is not met', 'wc-password-policy' ),
            'id'      => 'password_policy_min_length_message',
            'default' => __( 'Password must be at least %d characters long.', 'wc-password-policy' ),
            'type'    => 'text',
            'css'     => 'width: 400px;',
        ),
        array(
            'title'   => __( 'Require Uppercase Letters', 'wc-password-policy' ),
            'desc'    => __( 'Require at least one uppercase letter in passwords', 'wc-password-policy' ),
            'id'      => 'password_policy_require_uppercase',
            'default' => 'no',
            'type'    => 'checkbox',
        ),
        array(
            'title'   => __( 'Uppercase Error Message', 'wc-password-policy' ),
            'desc'    => __( 'Error message shown when uppercase requirement is not met', 'wc-password-policy' ),
            'id'      => 'password_policy_uppercase_message',
            'default' => __( 'Password must contain at least one uppercase letter.', 'wc-password-policy' ),
            'type'    => 'text',
            'css'     => 'width: 400px;',
        ),
        array(
            'title'   => __( 'Require Lowercase Letters', 'wc-password-policy' ),
            'desc'    => __( 'Require at least one lowercase letter in passwords', 'wc-password-policy' ),
            'id'      => 'password_policy_require_lowercase',
            'default' => 'no',
            'type'    => 'checkbox',
        ),
        array(
            'title'   => __( 'Lowercase Error Message', 'wc-password-policy' ),
            'desc'    => __( 'Error message shown when lowercase requirement is not met', 'wc-password-policy' ),
            'id'      => 'password_policy_lowercase_message',
            'default' => __( 'Password must contain at least one lowercase letter.', 'wc-password-policy' ),
            'type'    => 'text',
            'css'     => 'width: 400px;',
        ),
        array(
            'title'   => __( 'Require Numbers', 'wc-password-policy' ),
            'desc'    => __( 'Require at least one number in passwords', 'wc-password-policy' ),
            'id'      => 'password_policy_require_numbers',
            'default' => 'no',
            'type'    => 'checkbox',
        ),
        array(
            'title'   => __( 'Numbers Error Message', 'wc-password-policy' ),
            'desc'    => __( 'Error message shown when number requirement is not met', 'wc-password-policy' ),
            'id'      => 'password_policy_numbers_message',
            'default' => __( 'Password must contain at least one number.', 'wc-password-policy' ),
            'type'    => 'text',
            'css'     => 'width: 400px;',
        ),
        array(
            'title'   => __( 'Require Special Characters', 'wc-password-policy' ),
            'desc'    => __( 'Require at least one special character in passwords (e.g., @#$%^&*)', 'wc-password-policy' ),
            'id'      => 'password_policy_require_special',
            'default' => 'no',
            'type'    => 'checkbox',
        ),
        array(
            'title'   => __( 'Special Characters Error Message', 'wc-password-policy' ),
            'desc'    => __( 'Error message shown when special character requirement is not met', 'wc-password-policy' ),
            'id'      => 'password_policy_special_message',
            'default' => __( 'Password must contain at least one special character.', 'wc-password-policy' ),
            'type'    => 'text',
            'css'     => 'width: 400px;',
        ),
        array(
            'title'   => __( 'Enable Password Expiration', 'wc-password-policy' ),
            'desc'    => __( 'Force users to change their password every 90 days', 'wc-password-policy' ),
            'id'      => 'password_policy_enable_expiration',
            'default' => 'no',
            'type'    => 'checkbox',
        ),
        array(
            'title'             => __( 'Password Reuse Restriction', 'wc-password-policy' ),
            'desc'              => __( 'Prevent reuse of this many previous passwords (0 to disable)', 'wc-password-policy' ),
            'id'                => 'password_policy_reuse_limit',
            'default'           => '3',
            'type'              => 'number',
            'custom_attributes' => array(
                'min'  => '0',
                'step' => '1'
            ),
        ),
        array(
            'title'   => __( 'Password Reuse Error Message', 'wc-password-policy' ),
            'desc'    => __( 'Error message shown when password reuse is detected', 'wc-password-policy' ),
            'id'      => 'password_policy_reuse_message',
            'default' => __( 'Password has been used in the last %d passwords. Please choose a different password.', 'wc-password-policy' ),
            'type'    => 'text',
            'css'     => 'width: 400px;',
        ),
        array(
            'type' => 'sectionend',
            'id'   => 'password_policy_settings',
        ),
    );

    return $settings;
}

/**
 * Enqueue scripts and localize data for password validation
 */
add_action( 'wp_enqueue_scripts', 'wcpp_enqueue_password_policy_scripts' );
function wcpp_enqueue_password_policy_scripts() {
    if ( 'yes' !== get_option( 'password_policy_enabled' ) ) {
        return;
    }

    wp_enqueue_script( 'wc-password-policy', WC_PASSWORD_POLICY_PLUGIN_URL . 'assets/js/password-policy.js', array( 'jquery' ), WC_PASSWORD_POLICY_VERSION, true );
    
    $policy_data = array(
        'enabled'           => get_option( 'password_policy_enabled' ),
        'min_length'        => get_option( 'password_policy_min_length', 8 ),
        'require_uppercase' => get_option( 'password_policy_require_uppercase' ),
        'require_lowercase' => get_option( 'password_policy_require_lowercase' ),
        'require_numbers'   => get_option( 'password_policy_require_numbers' ),
        'require_special'   => get_option( 'password_policy_require_special' ),
        'messages'          => array(
            'min_length' => sprintf( 
                get_option( 'password_policy_min_length_message', __( 'Password must be at least %d characters long.', 'wc-password-policy' ) ),
                get_option( 'password_policy_min_length', 8 )
            ),
            'uppercase'  => get_option( 'password_policy_uppercase_message', __( 'Password must contain at least one uppercase letter.', 'wc-password-policy' ) ),
            'lowercase'  => get_option( 'password_policy_lowercase_message', __( 'Password must contain at least one lowercase letter.', 'wc-password-policy' ) ),
            'numbers'    => get_option( 'password_policy_numbers_message', __( 'Password must contain at least one number.', 'wc-password-policy' ) ),
            'special'    => get_option( 'password_policy_special_message', __( 'Password must contain at least one special character.', 'wc-password-policy' ) ),
        )
    );
    
    wp_localize_script( 'wc-password-policy', 'wcPasswordPolicy', $policy_data );
}

/**
 * Validate password against policy requirements.
 *
 * @since 1.0.0
 * @param string $password Password to validate.
 * @param WP_User|null $user Optional. User object.
 * @return true|WP_Error True if valid, WP_Error if invalid.
 */
function wc_password_policy_validate( $password, $user = null ) {
    // Check if password policy is enabled
    if ( 'yes' !== get_option( 'password_policy_enabled' ) ) {
        return true;
    }

    $errors = array();

    // Check minimum length
    $min_length = get_option( 'password_policy_min_length', 8 );
    if ( strlen( $password ) < $min_length ) {
        $errors[] = sprintf( 
            get_option( 'password_policy_min_length_message', __( 'Password must be at least %d characters long.', 'wc-password-policy' ) ),
            $min_length
        );
    }

    // Check for uppercase letters
    if ( 'yes' === get_option( 'password_policy_require_uppercase' ) && !preg_match('/[A-Z]/', $password) ) {
        $errors[] = get_option( 'password_policy_uppercase_message', __( 'Password must contain at least one uppercase letter.', 'wc-password-policy' ) );
    }

    // Check for lowercase letters
    if ( 'yes' === get_option( 'password_policy_require_lowercase' ) && !preg_match('/[a-z]/', $password) ) {
        $errors[] = get_option( 'password_policy_lowercase_message', __( 'Password must contain at least one lowercase letter.', 'wc-password-policy' ) );
    }

    // Check for numbers
    if ( 'yes' === get_option( 'password_policy_require_numbers' ) && !preg_match('/[0-9]/', $password) ) {
        $errors[] = get_option( 'password_policy_numbers_message', __( 'Password must contain at least one number.', 'wc-password-policy' ) );
    }

    // Check for special characters
    if ( 'yes' === get_option( 'password_policy_require_special' ) && !preg_match('/[^A-Za-z0-9]/', $password) ) {
        $errors[] = get_option( 'password_policy_special_message', __( 'Password must contain at least one special character.', 'wc-password-policy' ) );
    }

    // Check password history if user exists
    if ( $user && is_object( $user ) ) {
        $reuse_limit = intval( get_option( 'password_policy_reuse_limit', 3 ) );
        if ( $reuse_limit > 0 ) {
            $password_history = get_user_meta( $user->ID, 'password_history', true );
            if ( !empty( $password_history ) ) {
                $password_history = maybe_unserialize( $password_history );
                foreach ( array_slice( $password_history, 0, $reuse_limit ) as $old_password_hash ) {
                    if ( wp_check_password( $password, $old_password_hash ) ) {
                        $errors[] = sprintf( 
                            get_option( 'password_policy_reuse_message', __( 'Password has been used in the last %d passwords. Please choose a different password.', 'wc-password-policy' ) ),
                            $reuse_limit
                        );
                        break;
                    }
                }
            }
        }
    }

    if ( !empty( $errors ) ) {
        return new WP_Error( 'password_policy_error', implode( ' ', $errors ) );
    }

    return true;
}

/**
 * Store password in history when changed
 */
function wcpp_store_password_history( $user_id, $new_pass ) {
    if ( 'yes' !== get_option( 'password_policy_enabled' ) ) {
        return;
    }

    $reuse_limit = intval( get_option( 'password_policy_reuse_limit', 3 ) );
    if ( $reuse_limit > 0 ) {
        $password_history = get_user_meta( $user_id, 'password_history', true );
        $password_history = empty( $password_history ) ? array() : maybe_unserialize( $password_history );
        
        // Add new password hash to the beginning
        array_unshift( $password_history, wp_hash_password( $new_pass ) );
        
        // Keep only the required number of passwords
        $password_history = array_slice( $password_history, 0, $reuse_limit );
        
        update_user_meta( $user_id, 'password_history', $password_history );
    }
}

/**
 * Validate password on registration
 */
add_filter( 'woocommerce_registration_errors', 'wcpp_validate_registration_password', 10, 3 );
function wcpp_validate_registration_password( $errors, $username, $password ) {
    // Skip validation if WooCommerce is set to generate passwords
    if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) ) {
        return $errors;
    }
    
    $validation_result = wc_password_policy_validate( $password );
    
    if ( is_wp_error( $validation_result ) ) {
        $errors->add( 'password_policy_error', $validation_result->get_error_message() );
    }
    
    return $errors;
}

/**
 * Validate password on reset
 */
add_filter( 'woocommerce_reset_password_validation', 'wcpp_validate_password_reset', 10, 3 );
function wcpp_validate_password_reset( $errors, $user ) {
    if ( isset( $_POST['password_1'] ) && !empty( $_POST['password_1'] ) ) {
        $validation_result = wc_password_policy_validate( $_POST['password_1'], $user );
        
        if ( is_wp_error( $validation_result ) ) {
            $errors->add( 'password_policy_error', $validation_result->get_error_message() );
        }
    }
    
    return $errors;
}

/**
 * Store password history when password is changed
 */
add_action( 'password_reset', 'wcpp_password_reset_handler', 10, 2 );
function wcpp_password_reset_handler( $user, $new_pass ) {
    wcpp_store_password_history( $user->ID, $new_pass );
}

add_action( 'woocommerce_created_customer', 'wcpp_registration_password_handler', 10, 3 );
function wcpp_registration_password_handler( $customer_id, $new_customer_data, $password_generated ) {
    if ( !$password_generated && isset( $new_customer_data['user_pass'] ) ) {
        wcpp_store_password_history( $customer_id, $new_customer_data['user_pass'] );
    }
}

/**
 * Add password expiration check
 */
add_action( 'wp_login', 'wcpp_check_password_expiration', 10, 2 );
function wcpp_check_password_expiration( $user_login, $user ) {
    if ( 'yes' !== get_option( 'password_policy_enabled' ) || 'yes' !== get_option( 'password_policy_enable_expiration' ) ) {
        return;
    }

    $last_password_change = get_user_meta( $user->ID, 'last_password_change', true );
    if ( empty( $last_password_change ) ) {
        update_user_meta( $user->ID, 'last_password_change', time() );
        return;
    }

    $days_since_change = ( time() - $last_password_change ) / DAY_IN_SECONDS;
    if ( $days_since_change >= 90 ) {
        // Set a user meta flag that will be checked to redirect to password change page
        update_user_meta( $user->ID, 'password_expired', '1' );
    }
}

/**
 * Redirect to password change page if password is expired
 */
add_action( 'template_redirect', 'wcpp_redirect_expired_password' );
function wcpp_redirect_expired_password() {
    if ( !is_user_logged_in() ) {
        return;
    }

    $user_id = get_current_user_id();
    $password_expired = get_user_meta( $user_id, 'password_expired', true );

    if ( '1' === $password_expired ) {
        // Allow access only to account pages related to password change
        $allowed_endpoints = array( 'edit-account', 'change-password' );
        $current_endpoint = WC()->query->get_current_endpoint();
        
        // If not on password change page or edit account page, redirect to password change
        if ( !is_account_page() || 
            ( !in_array( $current_endpoint, $allowed_endpoints ) && !empty( $current_endpoint ) ) || 
            ( empty( $current_endpoint ) && is_account_page() ) ) {
            
            wp_safe_redirect( wc_get_endpoint_url( 'edit-account', '', wc_get_page_permalink( 'myaccount' ) ) );
            exit;
        }
    }
}

/**
 * Add notice for expired password
 */
add_action( 'woocommerce_account_content', 'wcpp_add_password_expired_notice' );
function wcpp_add_password_expired_notice() {
    $user_id = get_current_user_id();
    $password_expired = get_user_meta( $user_id, 'password_expired', true );

    if ( '1' === $password_expired ) {
        wc_print_notice( 
            __( 'Your password has expired. Please update your password to continue accessing the store.', 'wc-password-policy' ), 
            'error' 
        );
    }
}

/**
 * Prevent access to WooCommerce features when password is expired
 */
add_action( 'init', 'wcpp_restrict_expired_password_access' );
function wcpp_restrict_expired_password_access() {
    if ( !is_user_logged_in() ) {
        return;
    }

    $user_id = get_current_user_id();
    $password_expired = get_user_meta( $user_id, 'password_expired', true );

    if ( '1' === $password_expired ) {
        // Remove access to cart and checkout
        remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination' );
        remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering' );
        remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count' );
        
        // Prevent add to cart
        add_filter( 'woocommerce_add_to_cart_validation', '__return_false' );
        
        // Prevent checkout
        add_filter( 'woocommerce_checkout_registration_enabled', '__return_false' );
        add_filter( 'woocommerce_checkout_guest_checkout_enabled', '__return_false' );
        
        // Redirect away from cart and checkout pages
        if ( is_cart() || is_checkout() ) {
            wp_safe_redirect( wc_get_endpoint_url( 'edit-account', '', wc_get_page_permalink( 'myaccount' ) ) );
            exit;
        }
    }
}

/**
 * Add admin notice for users with expired passwords
 */
add_action( 'admin_notices', 'wcpp_show_expired_password_admin_notice' );
function wcpp_show_expired_password_admin_notice() {
    $user_id = get_current_user_id();
    $password_expired = get_user_meta( $user_id, 'password_expired', true );

    if ( '1' === $password_expired && is_admin() ) {
        echo '<div class="error notice">
            <p>' . __( 'Your password has expired. Please update your password to continue using the site.', 'wc-password-policy' ) . ' 
            <a href="' . esc_url( wc_get_endpoint_url( 'edit-account', '', wc_get_page_permalink( 'myaccount' ) ) ) . '">' . 
            __( 'Update Password', 'wc-password-policy' ) . '</a></p>
        </div>';
    }
}

/**
 * Update last password change timestamp
 */
add_action( 'after_password_reset', 'wcpp_update_password_change_timestamp' );
add_action( 'woocommerce_customer_save_password', 'wcpp_update_password_change_timestamp' );
function wcpp_update_password_change_timestamp( $user ) {
    $user_id = is_object( $user ) ? $user->ID : $user;
    update_user_meta( $user_id, 'last_password_change', time() );
    delete_user_meta( $user_id, 'password_expired' );
}

/**
 * Validate password on account password change
 */
add_action( 'woocommerce_save_account_details_errors', 'wcpp_validate_account_password_change', 10, 2 );
function wcpp_validate_account_password_change( $errors, $user ) {
    if ( isset( $_POST['password_1'] ) && !empty( $_POST['password_1'] ) ) {
        $validation_result = wc_password_policy_validate( $_POST['password_1'], $user );
        
        if ( is_wp_error( $validation_result ) ) {
            $errors->add( 'password_policy_error', $validation_result->get_error_message() );
        }
    }
}

/**
 * Store password history on account password change
 */
add_action( 'woocommerce_save_account_details', 'wcpp_account_password_change_handler', 10, 1 );
function wcpp_account_password_change_handler( $user_id ) {
    if ( isset( $_POST['password_1'] ) && !empty( $_POST['password_1'] ) ) {
        wcpp_store_password_history( $user_id, $_POST['password_1'] );
        wcpp_update_password_change_timestamp( $user_id );
    }
}
