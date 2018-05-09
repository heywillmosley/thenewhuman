<?php

if(!defined( 'ABSPATH' )){
	exit;
}

class USIN_BuddyPress extends USIN_Plugin_Module{
	
	protected $module_name = 'buddypress';
	protected $plugin_path = 'buddypress/bp-loader.php';
	public $xprofile;
	

	protected function apply_module_actions(){
		add_action('usin_module_activated', array($this, 'do_on_module_activated'));
	}

	public function init(){
		$this->xprofile = new USIN_BuddyPress_XProfile();
		
		$bp_query = new USIN_BuddyPress_Query($this->xprofile);
		$bp_query->init();

		$bp_user_activity = new USIN_BuddyPress_User_Activity();
		$bp_user_activity->init();

		add_filter('usin_user_db_data', array($this , 'filter_user_data'));
	}

	protected function init_reports(){
		new USIN_BuddyPress_Reports($this);
	}

	public function do_on_module_activated($module){
		if($module == $this->module_name){
			$this->save_last_seen();
		}
	}

	public static function is_bp_feature_active($feature){
		if(function_exists('bp_is_active')){
			return bp_is_active($feature);
		}
		return true;
	}

	public function register_module(){
		return array(
			'id' => $this->module_name,
			'name' => 'BuddyPress',
			'desc' => __('Retrieves and displays data about the users activity in the BuddyPress social network.', 'usin'),
			'allow_deactivate' => true,
			'buttons' => array(
				array('text'=> __('Learn More', 'usin'), 'link'=>'https://usersinsights.com/buddypress-users-data/', 'target'=>'_blank')
			),
			'active' => false
		);
	}

	public function register_fields(){
		$fields = array();

		if($this->is_bp_feature_active('groups')){
			$fields[]=array(
				'name' => __('Group Number', 'usin'),
				'id' => 'groups',
				'order' => 'ASC',
				'show' => true,
				'fieldType' => 'buddypress',
				'filter' => array(
					'type' => 'number',
					'disallow_null' => true
				),
				'module' => 'buddypress'
			);

			$fields[]=array(
				'name' => __('Groups Created', 'usin'),
				'id' => 'groups_created',
				'order' => 'ASC',
				'show' => true,
				'fieldType' => 'buddypress',
				'filter' => array(
					'type' => 'number',
					'disallow_null' => true
				),
				'module' => 'buddypress'
			);
			
			$fields[]=array(
				'name' => __('Group', 'usin'),
				'id' => 'bp_group',
				'order' => false,
				'show' => false,
				'hideOnTable' => true,
				'fieldType' => 'none',
				'filter' => array(
					'type' => 'include_exclude_with_nulls',
					'options' => self::get_groups(),
					'disallow_null' => true
				),
				'module' => 'buddypress'
			);
		}

		if($this->is_bp_feature_active('friends')){
			$fields[]=array(
				'name' => __('Friends', 'usin'),
				'id' => 'friends',
				'order' => 'ASC',
				'show' => true,
				'fieldType' => 'buddypress',
				'filter' => array(
					'type' => 'number',
					'disallow_null' => true
				),
				'module' => 'buddypress'
			);
		}

		if($this->is_bp_feature_active('activity')){
			$fields[]=array(
				'name' => __('Activity Updates', 'usin'),
				'id' => 'activity_updates',
				'order' => 'ASC',
				'show' => true,
				'fieldType' => 'buddypress',
				'filter' => array(
					'type' => 'number',
					'disallow_null' => true
				),
				'module' => 'buddypress'
			);
		}

		if(!empty($this->xprofile)){
			$xprof_fields = $this->xprofile->get_fields();
			if(!empty($xprof_fields)){
				$fields = array_merge($fields, $xprof_fields);
			}
		}


		return $fields;
	}
	

	/**
	 * Loads the already saved "last activity" from buddy press and saves it into
	 * the "Last seen" field of the user
	 */
	protected function save_last_seen(){
		global $wpdb, $usin;

		$umeta_res = $wpdb->get_results("SELECT * FROM $wpdb->usermeta WHERE meta_key = 'last_activity'");
		
		//get the users that already have a last seen saved
		//so that the "last activity" value is not saved for them
		$users_last_seen_ids = array();
		$users_last_seen = USIN_User_Data::get_users('last_seen IS NOT NULL');
		if(!empty($users_last_seen) && isset($users_last_seen[0]->user_id)){
			$users_last_seen_ids = wp_list_pluck($users_last_seen, 'user_id');
		}


		if(!empty($umeta_res)){
			foreach ($umeta_res as $umeta) {
				if(!in_array($umeta->user_id, $users_last_seen_ids)){
					//there is no last seen date saved for this user, save the value
					//from "last activity"
					$user_data = new USIN_User_Data($umeta->user_id);
					$user_data->save('last_seen', $umeta->meta_value);
				}
			}
		}

	}
	
	public function filter_user_data($data){
		foreach ($this->xprofile->multi_option_fields as $key ) {
			if(isset($data->$key)){
				$data->$key = implode(', ', unserialize($data->$key));
			}
		}
		
		return $data;
	}
	
	public static function get_groups($return_associative = false){
		$groups = array();
		if(method_exists('BP_Groups_Group', 'get')){
			$bp_groups = BP_Groups_Group::get(array(
				'type'=>'alphabetical',
				'per_page'=>-1,
				'show_hidden' => true
			));
			
			if(!empty($bp_groups['groups']) && is_array($bp_groups['groups'])){
				foreach ($bp_groups['groups'] as $bp_group ) {
					if($return_associative){
						$groups[$bp_group->id] = $bp_group->name;
					}else{
						$groups[]= array('key'=> $bp_group->id, 'val'=>$bp_group->name);
					}
				}
			}
		}
		
		return $groups;
	}

}

new USIN_BuddyPress();