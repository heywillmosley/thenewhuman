<?php

class FrmMlcmpAction extends FrmFormAction {

	function __construct() {
		$action_ops = array(
		    'classes'   => 'frm_mailchimp_icon frm_icon_font',
            'limit'     => 99,
            'active'    => true,
            'priority'  => 25,
            'event'     => array('create', 'update'),
		);
		
	    $this->FrmFormAction('mailchimp', __('Add to MailChimp', 'formidable'), $action_ops);
	}

	function form( $form_action, $args = array() ) {
	    extract($args);
	    
	    $list_options = $form_action->post_content;
        $list_id = $list_options['list_id'];
        
        $lists = FrmMlcmpAppController::decode_call('/lists/list', array('limit' => 50));
        $groups = FrmMlcmpAppController::get_groups($list_id);
        
        if ( $list_id ) {
            $list_fields = FrmMlcmpAppController::decode_call('/lists/merge-vars', array( 'id' => array( $list_id ) ) );
            
            $frm_field = new FrmField();
            $form_fields = $frm_field->getAll("fi.form_id='". (int) $form->id ."' and fi.type not in ('break', 'divider', 'end_divider', 'html', 'captcha', 'form')", 'field_order');
        }
        $action_control = $this;
	    
	    include(FrmMlcmpAppController::path() .'/views/action-settings/mailchimp_options.php');
	    include_once(FrmMlcmpAppController::path() .'/views/action-settings/_action_scripts.php');
	}
	
	function get_defaults() {
	    return array(
	        'list_id'=> '',
	        'optin'  => false,
	        'fields' => array(),
	        'groups' => array(),
	    );
	}
	
	function get_switch_fields() {
	    return array(
            'fields' => array(),
            'groups' => array(array('id')),
        );
	}

	public function migrate_values($action, $form) {
	    if ( ! empty($form->options['hide_field']) ) {
    	    $action->post_content['conditions']['send_stop'] = 'send';
    	    foreach ( $form->options['hide_field'] as $k => $field_id ) {
                $action->post_content['conditions'][] = array(
                    'hide_field'        => $field_id,
                    'hide_field_cond'   => isset($form->options['hide_field_cond'][$k]) ? $form->options['hide_field_cond'][$k] : '==',
                    'hide_opt'          => isset($form->options['hide_opt'][$k]) ? $form->options['hide_opt'][$k] : '',
                );
    	    }
    	    unset($action->post_content['hide_field'], $action->post_content['hide_field_cond']);
    	    unset($action->post_content['hide_opt']);
        }
        $action->post_content['event'] = array('create', 'update');
        
	    return $action;
	}
}
