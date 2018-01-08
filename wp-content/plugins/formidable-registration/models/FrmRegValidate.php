<?php

class FrmRegValidate{
	// Put member variables here

	public static function validate_user_data_fields( $field, $value, $update, &$errors ) {
		$old_values = self::get_old_values( $field );

		self::validate_password_field( $field, $value, $update, $errors );
		self::validate_username_field( $field, $value, $old_values['username'], $errors );
		self::validate_email_field( $field, $value, $old_values['email'], $errors );
	}

	/**
	* Get the old values for the username and email
	*/
	private static function get_old_values( $field ) {
		$user_ID = get_current_user_id();
		$required_role = apply_filters('frmreg_required_role', 'create_users');
		if( $user_ID && (!is_admin() || defined('DOING_AJAX')) && !current_user_can($required_role) ){
			//return $errors; //don't check if user is logged in because a new user won't be created anyway
		}
		$posted_id = self::get_posted_user_id( $field );

        if ( $posted_id ) {
            $user_data = get_userdata( $posted_id );
            $old_values['username'] = $user_data->user_login;
            $old_values['email'] = $user_data->user_email;
        } else if ( $user_ID ) {
            $user_data = get_userdata( $user_ID );
            $old_values['username'] = $user_data->user_login;
            $old_values['email'] = $user_data->user_email;
        } else {
            $old_values['username'] = $old_values['email'] = false;
        }

		return $old_values;
	}

	private static function get_posted_user_id( $field ) {
        $posted_id = isset( $_POST['frm_user_id'] ) ? $_POST['frm_user_id'] : 0;
        if ( ! $posted_id ) {
            global $wpdb;
            $user_id_field = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}frm_fields WHERE type=%s AND form_id=%d", 'user_id', $field->form_id));
            if ( $user_id_field ) {
                $posted_id = $_POST['frm_user_id'] = $_POST['item_meta'][ $user_id_field ];
            }
        }
		return $posted_id;
	}

	private static function validate_password_field( $field, $value, $update, &$errors ) {
		if ( $field->type != 'password' ) {
			return;
		}

        if ( self::errors_already_set( $field->id, $errors ) ) {

	        //Don't require password if updating
	        if ( $update && empty( $value ) ) {
	            unset( $errors['field'. $field->id] );
	        } else {
           		// if there is already an error on this field, no need to check for another
            	return $errors;
			}
        }

		if ( ! $_POST['frm_user_id'] && empty( $value ) ) {
			// If user is being created and the password field is empty
			$errors['field'. $field->id] = __('Please enter a valid password.', 'frmreg');

		} else if ( false !== strpos( wp_unslash( $value ), "\\" ) ) {
			// match WordPress password checking
			$errors['field'. $field->id] = __('Passwords may not contain the character "\\".', 'frmreg' );
		}
	}

	private static function validate_username_field( $field, $value, $old_value, &$errors ) {
		if ( self::errors_already_set( $field->id, $errors ) ) {
			return;
		}

		// If the current field is mapped to the Username setting
        if ( isset( $_POST['frm_register']['username'] ) && $field->id == $_POST['frm_register']['username'] ) {

			if ( ! $_POST['frm_user_id'] && empty( $value ) ) {
				// If user is being created and the username field is empty
				$errors['field'. $field->id] = __('Please enter a valid username.', 'frmreg');

			} else if ( self::new_value_entered( $value, $old_value ) && FrmRegAppHelper::username_exists( $value ) ) {
				// Check if username already exists
				$errors['field'. $field->id] = __('This username is already registered.', 'frmreg');

			} else if ( ! validate_username( $value ) ) {
				// Check for invalid characters in new username
				$errors['field'. $field->id] = __( 'This username is invalid because it uses illegal characters. Please enter a valid username.', 'frmreg' );

			}
		}
	}

	private static function validate_email_field( $field, $value, $old_value, &$errors ) {
		if ( self::errors_already_set( $field->id, $errors ) ) {
			return;
		}

		// If the current field is mapped to the Email setting
		if ( isset( $_POST['frm_register']['email']) && $field->id == $_POST['frm_register']['email'] ) {

			if ( ! $_POST['frm_user_id'] && empty( $value ) ) {
				// If user is being created and the email field is empty
				$errors['field'. $field->id] = __('Please enter a valid email address.', 'frmreg');

			} else if ( self::new_value_entered( $value, $old_value ) && self::email_exists( $value ) ) {
				// If a new email address was entered, but it already exists
				$errors['field'. $field->id] = __('This email address is already registered.', 'frmreg');
			}
		}
	}

	/**
	* Check if a new value is entered
	* This checks if the new value is different from the old value OR if this is a completely new value
	*/
	private static function new_value_entered( $new_val, $old_val ) {
		return ( ( $old_val && strtolower( $new_val ) != strtolower( $old_val ) ) || ! $old_val );
	}

	/**
	* If no value entered or errors are already set, no need to validate field yet
	*/
	private static function errors_already_set( $id, $errors ) {
		return isset( $errors['field'. $id] );
	}

	/**
	* Check if email already exists
	*/
	private static function email_exists( $email ) {
		if ( ! function_exists('email_exists') ) {
			require_once(ABSPATH . WPINC . '/registration.php');
		}

		return email_exists( $email );
	}
}