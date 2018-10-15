<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Pimwick_License' ) ) :

final class Pimwick_License {

	public $error = '';

	private $_license_url = 'https://www.pimwick.com';
	private $_license_secret = '588ba467a728d3.17738635';
	private $_license_product = '';
	private $_license_option_name = '';
	private $_premium;

	function __construct( $product, $option ) {
		$this->_license_product = $product;
		$this->_license_option_name = $option;
	}

	public function premium() {
		if ( !isset( $this->_premium ) ) {
			$this->_premium = $this->check_license( get_option( $this->_license_option_name, '' ) );
		}

		return $this->_premium;
	}

	public function activate_license( $license_key ) {
		if ( $this->license_action( $license_key, 'slm_activate' ) ) {
			$this->_premium = true;
			update_option( $this->_license_option_name, $license_key, false );
			return true;
		} else {
			return false;
		}
	}

	public function deactivate_license() {
		$license_key = get_option( $this->_license_option_name, '' );
		if ( $this->license_action( $license_key, 'slm_deactivate' ) ) {
			$this->_premium = false;
			update_option( $this->_license_option_name, '', false );
			return true;
		} else {
			return false;
		}
	}

	private function check_license( $license_key ) {
		if ( empty( $license_key ) ) {
			return false;
		} else {
			return $this->license_action( $license_key, 'slm_check' );
		}
	}

	private function license_action( $license_key, $action ) {
        if ( empty( $license_key ) ) {
            return false;
        }

        $api_params = array(
            'slm_action' => $action,
            'secret_key' => $this->_license_secret,
            'license_key' => $license_key,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' => urlencode( $this->_license_product ),
        );

        $query = esc_url_raw( add_query_arg( $api_params, $this->_license_url ) );
        $response = wp_remote_get( $query, array( 'timeout' => 20000, 'sslverify' => true ) );
        $this->error = '';

        if ( !is_wp_error( $response ) ) {
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );

            if ( $license_data->result == 'success' ) {
                if ( property_exists( $license_data, 'status' ) ) {
                    if ( $license_data->status != 'expired' && $license_data->status != 'blocked' ) {
                        return true;
                    } else {
                        $this->error = 'License is ' . $license_data->status;
                    }
                } else {
                    return true;
                }
            } else if ( false !== strpos( $license_data->message, 'License key already in use on' ) ) {
                return true;
            } else {
                if ( empty( $license_data->message ) ) {
                    $this->error = 'Unexpected error accessing license url ' . $this->_license_url . '<br><pre>' . print_r( $response, true ) . '</pre>';
                } else {
                    $this->error = 'Error: ' . $license_data->message;
                }
            }
        } else {
            $error_message = $response->get_error_message();
            if ( false !== stripos( $error_message, 'curl error 28: connection timed out after' ) ) {
                // For server timeouts, just assume the license is legit.
                return true;
            }
            $this->error = 'Error while validating license: ' . $error_message;
        }

        return false;
    }
}

endif;

?>