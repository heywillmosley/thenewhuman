<?php

/**
 * @since 3.0
 */
class FrmProFieldAddress extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'address';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $has_for_label = false;

	protected $is_tall = true;

	protected function field_settings_for_type() {
		$settings = array(
			'clear_on_focus' => true,
			'default_blank' => true,
			'visibility'    => true,
		);
		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	protected function extra_field_opts() {
		$options = array(
			'address_type' => 'international',
		);

		$default_labels = self::default_labels();
		foreach ( $default_labels as $key => $label ) {
			$options[ $key . '_desc' ] = $label;
		}

		return $options;
	}

	private static function default_labels() {
		return array(
			'line1' => '',
			'line2' => '',
			'city'  => __( 'City', 'formidable-pro' ),
			'state' => __( 'State/Province', 'formidable-pro' ),
			'zip'   => __( 'Zip/Postal', 'formidable-pro' ),
			'country' => __( 'Country', 'formidable-pro' ),
		);
	}

	public function show_on_form_builder( $name = '' ) {
		$field = FrmFieldsHelper::setup_edit_vars( $this->field );

		$defaults = $this->empty_value_array();
		$this->fill_values( $field['default_value'], $defaults );

		$field['value'] = $field['default_value'];
		$sub_fields = FrmProAddressesController::get_sub_fields( $field );
		$field_name = $this->html_name( $name );
		$html_id = $this->html_id();

		include( FrmProAppHelper::plugin_path() .'/classes/views/combo-fields/input-form-builder.php' );
	}

	public function front_field_input( $args, $shortcode_atts ) {
		$pass_args = array( 'errors' => $args['errors'], 'html_id' => $args['html_id'] );
		ob_start();
		FrmProAddressesController::show_in_form( $this->field, $args['field_name'], $pass_args );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $input_html;
	}

	protected function prepare_display_value( $value, $atts ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}

		$new_value = '';
		if ( ! empty( $value['line1'] ) ) {
			$defaults = $this->empty_value_array();
			$this->fill_values( $value, $defaults );

			$new_value = $value['line1'] . ' <br/>';
			if ( ! empty( $value['line2'] ) ) {
				$new_value .= $value['line2'] . ' <br/>';
			}

			$address_type = FrmField::get_option( $this->field, 'address_type' );
			if ( 'europe' === $address_type ) {
				$new_value .= $value['zip'] . ' ' . $value['city'];
			} else {
				$new_value .= $value['city'] . ', ' . $value['state'] . ' ' . $value['zip'];
			}

			if ( isset( $value['country'] ) && ! empty( $value['country'] ) ) {
				$new_value .= ' <br/>' . $value['country'];
			}
		}
		return $new_value;
	}

	private function empty_value_array() {
		return array( 'line1' => '', 'line2' => '', 'city' => '', 'state' => '', 'zip' => '', 'country' => '' );
	}

	/**
	 * Convert comma-separated address values to an associative array
	 *
	 * @since 2.02.13
	 *
	 * @param string|array $value
	 *
	 * @return array
	 */
	protected function prepare_import_value( $value, $atts ) {
		if ( is_array( $value ) ) {
			return $value;
		}

		$sep = apply_filters( 'frm_csv_sep', ', ' );
		$value = explode( $sep, $value );

		$count = count( $value );

		if ( $count < 4 || $count > 6 ) {
			return $value;
		}

		$new_value = $this->empty_value_array();

		$new_value['line1'] = $value[0];

		$last_item = end( $value );

		if ( $count == 6 || ( $count == 5 && is_numeric( $last_item ) ) ) {
			$new_value['line2'] = $value[1];
			$new_value['city']  = $value[2];
			$new_value['state'] = $value[3];
			$new_value['zip']   = $value[4];

			if ( $count == 6 ) {
				$new_value['country'] = $value[ 5 ];
			}

		} else {
			$new_value['city']  = $value[1];
			$new_value['state'] = $value[2];
			$new_value['zip']   = $value[3];

			if ( $count == 5 ) {
				$new_value['country'] = $value[4];
			}
		}

		return $new_value;
	}

	public function validate( $args ) {
		$errors = array();
		self::validate_required_fields( $errors, $args );
		self::validate_zip( $errors, $args );

		return $errors;
	}

	private function validate_required_fields( &$errors, $args ) {
		if ( $this->field->required ) {

			if ( FrmProEntryMeta::skip_required_validation( $this->field ) ) {
				return;
			}

			$values = $args['value'];
			if ( $values == '' ) {
				$values = FrmProAddressesController::empty_value_array();
			}

			$blank_msg = FrmFieldsHelper::get_error_msg( $this->field, 'blank' );

			foreach ( $values as $key => $value ) {
				if ( empty( $value ) && $key != 'line2' ) {
					$errors[ 'field' . $args['id'] . '-' . $key ] = '';
					$errors[ 'field' . $args['id'] ] = $blank_msg;
				}
			}
		}
	}

	private function validate_zip( &$errors, $args ) {
		$values = $args['value'];
		if ( isset( $values['zip'] ) && ! empty( $values['zip'] ) ) {
			$address_type = FrmField::get_option( $this->field, 'address_type' );
			$format = '';
			if ( $address_type === 'us' ) {
				$format = '/^[0-9]{5}(?:-[0-9]{4})?$/';
			}
			$format = apply_filters( 'frm_zip_format', $format, array( 'field' => $this->field ) );
			if ( ! empty( $format ) && ! preg_match( $format, $values['zip'] ) ) {
				$errors[ 'field' . $args['id'] . '-zip' ] = __( 'This value is invalid', 'formidable-pro' );
			}
		}
	}
}
