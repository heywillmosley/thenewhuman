<?php

class FrmRegEntry{
	// put member variables here

	/**
	* Update the user ID for an entry
	*
	* @since 1.11.05
	*
	* @param int $form_id
	* @param int $entry_id
	* @param int $user_id
	*/
	public static function update_user_id_for_entry( $form_id, $entry_id, $user_id ){
		global $wpdb;

		// Get all the user ID fields in this form (and in child forms)
		$user_id_fields = FrmField::get_all_types_in_form( $form_id, 'user_id', 999, 'include' );
		$form_to_field_id = array();
		foreach ( $user_id_fields as $u_field ) {
			$form_to_field_id[ $u_field->form_id ] = $u_field->id;
		}

		// Get all entry IDs (for parent and child entries)
		$query = $wpdb->prepare( "SELECT id, form_id FROM " . $wpdb->prefix . "frm_items WHERE parent_item_id=%d OR id=%d", $entry_id, $entry_id );
		$entry_ids = $wpdb->get_results( $query );

		foreach ( $entry_ids as $e ) {
			// Update frm_items for parent and child entries
			self::update_user_id_frm_items( $e->id, $user_id );
			if ( isset( $form_to_field_id[ $e->form_id ] ) ) {
				self::update_user_id_frm_item_metas( $e->id, $form_to_field_id[ $e->form_id ], $user_id );
			}
		}
	}

	/**
	* Update the frm_items table with new userID
	*
	* @since 1.11.05
	*
	* @param int $entry_id
	* @param int $user_id
	*/
	private static function update_user_id_frm_items( $entry_id, $user_id ) {
        global $wpdb;

        $wpdb->update( $wpdb->prefix .'frm_items', array('user_id' => $user_id, 'updated_by' => $user_id), array('id' => $entry_id) );
        wp_cache_delete( $entry_id, 'frm_entry' );
	}

	/**
	* Update the frm_item_metas table with new userID
	*
	* @since 1.11.05
	*
	* @param int $entry_id
	* @param int $user_field
	* @param int $user_id
	*/
	private static function update_user_id_frm_item_metas( $entry_id, $user_field, $user_id ) {
		FrmEntryMeta::delete_entry_meta( $entry_id, $user_field );
		FrmEntryMeta::add_entry_meta( $entry_id, $user_field, '', $user_id );
	}
}