<?php
/*
Plugin Name: WP VIP Seat Booking
Description: Provides VIP seat booking form and admin management.
Version: 0.1
Author: AI
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WP_VIP_Seat_Booking {
    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Hooks
        add_action( 'init', [ $this, 'register_scripts' ] );
        add_shortcode( 'vip_seat_booking', [ $this, 'render_booking_form' ] );
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

        if ( is_admin() ) {
            add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        }
    }

    public function register_scripts() {
        // React and dependencies from CDN
        wp_register_script( 'react', 'https://unpkg.com/react@17/umd/react.production.min.js', [], null, true );
        wp_register_script( 'react-dom', 'https://unpkg.com/react-dom@17/umd/react-dom.production.min.js', ['react'], null, true );
        wp_register_script( 'react-date-picker', 'https://unpkg.com/react-multi-date-picker@latest/umd/react-multi-date-picker.min.js', ['react','react-dom'], null, true );
        wp_register_script( 'wp-vip-seat-booking', plugin_dir_url( __FILE__ ) . 'js/booking-form.js', ['react','react-dom','react-date-picker','wp-api'], '0.1', true );
        wp_localize_script( 'wp-vip-seat-booking', 'VIPBooking', [
            'apiUrl' => rest_url( 'vip-booking/v1' ),
            'nonce'  => wp_create_nonce( 'wp_rest' ),
        ] );
    }

    public function render_booking_form() {
        wp_enqueue_script( 'wp-vip-seat-booking' );
        wp_enqueue_script( 'wp-api' );
        wp_enqueue_style( 'wp-vip-seat-booking-style', plugin_dir_url( __FILE__ ) . 'css/style.css', [], '0.1' );
        return '<div id="vip-booking-app"></div>';
    }

    public function admin_menu() {
        add_menu_page( 'VIP Bookings', 'VIP Bookings', 'manage_options', 'vip-bookings', [ $this, 'admin_page' ] );
    }

    public function admin_page() {
        echo '<div class="wrap"><h1>VIP Seat Booking</h1><div id="vip-booking-admin"></div></div>';
        wp_enqueue_script( 'wp-vip-seat-booking-admin', plugin_dir_url( __FILE__ ) . 'js/admin.js', ['wp-api','react','react-dom'], '0.1', true );
        wp_localize_script( 'wp-vip-seat-booking-admin', 'VIPBooking', [
            'apiUrl' => rest_url( 'vip-booking/v1' ),
            'nonce'  => wp_create_nonce( 'wp_rest' ),
        ] );
    }

    public function register_rest_routes() {
        register_rest_route( 'vip-booking/v1', '/bookings', [
            'methods' => 'POST',
            'callback' => [ $this, 'handle_booking' ],
            'permission_callback' => '__return_true',
        ] );
    }

    public function handle_booking( $request ) {
        $data = $request->get_json_params();
        $name = sanitize_text_field( $data['name'] ?? '' );
        $phone = sanitize_text_field( $data['phone'] ?? '' );
        $dates = array_map( 'sanitize_text_field', $data['dates'] ?? [] );
        if ( empty( $name ) || empty( $phone ) || empty( $dates ) ) {
            return new WP_Error( 'invalid_data', 'Invalid booking data', [ 'status' => 400 ] );
        }
        $booking = [
            'name' => $name,
            'phone' => $phone,
            'dates' => $dates,
            'created' => current_time( 'mysql' ),
        ];
        $bookings = get_option( 'vip_seat_bookings', [] );
        $bookings[] = $booking;
        update_option( 'vip_seat_bookings', $bookings );
        return [ 'success' => true ];
    }
}

WP_VIP_Seat_Booking::instance();

?>
