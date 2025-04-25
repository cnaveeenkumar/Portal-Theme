<?php
/**
 * Redirect non-logged-in users to ADFS login page
 */
// Define ADFS login URLs for different environments
define('ADFS_LOGIN_URL_LOCAL', 'https://adfs-apps-local.php');
define('ADFS_LOGIN_URL_STAGE', 'https://adfs-apps-local-staging.php');

// Define hostnames for different environments
define('HTTP_HOST_LOCAL','localhost');
define('HTTP_HOST_STAGE','stage.domain.com');

// Redirect to appropriate ADFS login if the user is not logged in
function adfs_redirect_if_not_logged_in() {
    // Allow ADFS login response if 'data' parameter is present
    if (isset($_REQUEST['data'])) {
        return;
    }

    // Proceed only if user is not logged in and not in the admin dashboard
    if (!is_user_logged_in() && !is_admin()) {
        $current_host = $_SERVER['HTTP_HOST'];

        switch ($current_host) {
            case HTTP_HOST_LOCAL:
                wp_redirect(ADFS_LOGIN_URL_LOCAL);
                break;
            case HTTP_HOST_STAGE:
                wp_redirect(ADFS_LOGIN_URL_STAGE);
                break;
            default:
                wp_redirect(ADFS_LOGIN_URL_LOCAL); // Default redirect
                break;
        }

        exit;
    }
}
add_action('template_redirect', 'adfs_redirect_if_not_logged_in');


/**
 * Handle ADFS login response and register or login the user
 * Also stores custom meta like employee ID and department
 */
function adfs_register_or_login_user() {
    if (is_user_logged_in()) {
        return;
    }

    if (isset($_REQUEST['data'])) {
        $decoded_data = base64_decode(base64_decode($_REQUEST['data']));
        parse_str($decoded_data, $user_data);

        if (!empty($user_data['Mail']) && !empty($user_data['emp_name'])) {
            $email       = sanitize_email($user_data['Mail']);
            $name        = sanitize_text_field($user_data['emp_name']);
            $employee_id = isset($user_data['emp_id']) ? sanitize_text_field($user_data['emp_id']) : '';
            $designation = isset($user_data['Designation']) ? sanitize_text_field($user_data['Designation']) : '';
            $location    = isset($user_data['Location']) ? sanitize_text_field($user_data['Location']) : '';
            $sbu         = isset($user_data['SBU']) ? sanitize_text_field($user_data['SBU']) : '';
            $password    = 'Sify#123';

            $user = get_user_by('email', $email);

            if (!$user) {
                // Create new user
                $user_id = wp_create_user($email, $password, $email);

                if (!is_wp_error($user_id)) {
                    wp_update_user([
                        'ID' => $user_id,
                        'display_name' => $name,
                        'role' => 'subscriber',
                    ]);
                } else {
                    wp_die('User creation failed: ' . $user_id->get_error_message());
                }
            } else {
                // Existing user
                $user_id = $user->ID;

                // Optionally update display name
                wp_update_user([
                    'ID' => $user_id,
                    'display_name' => $name,
                ]);
            }

            // Store or update custom user meta
            update_user_meta($user_id, 'employee_id', $employee_id);
            update_user_meta($user_id, 'designation', $designation);
            update_user_meta($user_id, 'location', $location);
            update_user_meta($user_id, 'sbu', $sbu);

            // Log the user in
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            do_action('wp_login', $email, get_user_by('id', $user_id));

            wp_redirect(home_url('/'));
            exit;
        }
    }
}
add_action('init', 'adfs_register_or_login_user');

/**
 * Restrict access to admin dashboard for non-admin users
 */
function restrict_admin_dashboard_access() {
    if (is_admin() && !current_user_can('administrator') && !(defined('DOING_AJAX') && DOING_AJAX)) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('admin_init', 'restrict_admin_dashboard_access');

/**
 * Hide admin bar for all users except administrators
 * Hide it only on the front-end and not affect the back-end
 */
function hide_admin_bar_frontend_only() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'hide_admin_bar_frontend_only');
