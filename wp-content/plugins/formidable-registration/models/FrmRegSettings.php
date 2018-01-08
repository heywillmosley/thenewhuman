<?php
class FrmRegSettings{

    var $settings;

    function __construct(){
        $this->set_default_options();
    }
    
    function default_options(){
        return array(
            'login'     => '',
            'lostpass'  => '',
        );
    }

    function set_default_options( $settings=false ) {
        $default_settings = $this->default_options();
        
        if ( $settings === true ) {
            $settings = new stdClass();
        } else if ( !$settings ) {
            $settings = $this->get_options();
        }
            
        if ( !isset($this->settings) ) {
            $this->settings = new stdClass();
        }
        
        foreach ( $default_settings as $setting => $default ) {
            if ( is_object($settings) && isset($settings->{$setting}) ) {
                $this->settings->{$setting} = $settings->{$setting};
            }
                
            if ( !isset($this->settings->{$setting}) ) {
                $this->settings->{$setting} = $default;
            }
        }
    }
    
    function get_options(){
        $settings = get_option('frm_reg_options');

        if ( !is_object($settings) ) {
            if ( $settings ) { //workaround for W3 total cache conflict
                $settings = unserialize( serialize( $settings ) );
            }else{
                // If unserializing didn't work
                if ( !is_object( $settings ) ) {
                    if ( $settings ) { //workaround for W3 total cache conflict
                        $settings = unserialize(serialize($settings));
                    } else {
                        $settings = $this->set_default_options(true);
                    }
                    $this->store();
                }
            }
        }else{
            $this->set_default_options($settings); 
        }
        
        return $this->settings;
    }

    function validate($params,$errors){
       // if ( empty($params[ 'frm_pay_business_email' ] ) or !is_email($params[ 'frm_pay_business_email' ]))
            //$errors[] = __('Please enter a valid email address', 'frmreg');
        return $errors;
    }

    function update( $params ) {
        $settings = $this->default_options();
        
        foreach ( $settings as $setting => $default ) {
            if ( isset($params['frm_reg_'. $setting] ) ) {
                $this->settings->{$setting} = $params['frm_reg_'. $setting];
            }
        }
    }

    function store(){
        // Save the posted value in the database
        update_option( 'frm_reg_options', $this->settings );
    }

	/**
	* Format the usermeta settings to check for needed updates
	*
	* @since 1.11.06
	*
	* @param array $settings
	* @return array $settings['reg_usermeta']
	*/
	public static function format_usermeta_settings( $settings ) {
        if ( ! isset( $settings['reg_usermeta'] ) || empty( $settings['reg_usermeta'] ) ) {
            $settings['reg_usermeta'] = array();
        }

        $settings['reg_usermeta']['username'] = $settings['reg_username'];
        $settings['reg_usermeta']['user_email'] = $settings['reg_email'];
        if ( $settings['reg_username'] == '-1' ) {
            $settings['reg_username'] = $settings['reg_email'];
        }

        $settings['reg_usermeta']['first_name'] = $settings['reg_first_name'];
        $settings['reg_usermeta']['last_name'] = $settings['reg_last_name'];

        if ( isset( $settings['reg_display_name'] ) && is_numeric( $settings['reg_display_name'] ) ) {
            $settings['reg_usermeta']['display_name'] = $settings['reg_display_name'];
        }

		return $settings['reg_usermeta'];
	}

	/**
	* Get the user meta key for a given field ID
	*
	* @since 1.11.06
	*
	* @param int $field_id
	* @param array $user_meta_settings
	* @return string $user_meta_key
	*/
	public static function get_usermeta_key_for_field( $field_id, $user_meta_settings ) {
		$user_meta_key = '';

		foreach ( $user_meta_settings as $key => $val ) {
			if ( is_array( $val ) ) {
				$key = $val['meta_name'];
				$val = $val['field_id'];
			}

			if ( $val == $field_id ) {
				$user_meta_key = $key;
			}
		}

		return $user_meta_key;
	}
  
}
