<?php
/**
 *  @author abu shoaib
 *  @since 1.3.0
 */
class WP_E_Signer extends WP_E_Model
{
    
    private $table;
    

	public function __construct(){
		parent::__construct();
 
		$this->table = $this->table_prefix . "document_users";
	}
        
    public function get_document_signer_info($user_id,$document_id)
    {
        $signers = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM " . $this->table . " WHERE user_id=%d and document_id=%d LIMIT 1", $user_id,$document_id
			)
		);
        
        if(!empty($signers[0])) return $signers[0];
                else return false;
         
    }
    
    
    public function insert($signers)
    {
		
			if($this->exists($signers['user_id'],$signers['document_id']))
			{
				$this->update($signers);
				return ; 
			}
		
            
			$this->wpdb->query(
				$this->wpdb->prepare(
					"INSERT INTO " . $this->table . " VALUES(null, %d, %d, %s,%s,%s)", 
					$signers['user_id'], 
					$signers['document_id'], 
					wp_unslash($signers['signer_name']),
                                        $signers['signer_email'],
                                        wp_unslash($signers['company_name'])
				)
			);
		
		  return $this->wpdb->insert_id;
    }
    
   
    
    public function exists($user_id,$document_id){
		
		return $this->wpdb->query(
			$this->wpdb->prepare(
				"SELECT id FROM " . $this->table . " WHERE user_id=%d and document_id=%d",$user_id,$document_id
			)
		);
		
	}
	
    public function update($signers)
    {
			
        	$this->wpdb->query(
				$this->wpdb->prepare(
					"UPDATE " . $this->table . " SET signer_name=%s,signer_email=%s,company_name=%s WHERE user_id=%d and document_id=%d", 
					$signers['signer_name'], 
					$signers['signer_email'], 
					$signers['company_name'],
                                        $signers['user_id'],
                                        $signers['document_id']
				)
			);
           
            return $this->wpdb->insert_id;
    }
    
    public function updateField($user_id,$document_id, $field, $value){
		return $this->wpdb->query(
			$this->wpdb->prepare(
				"UPDATE $this->table SET $field='%s' WHERE user_id=%d and document_id=%d", $value, $user_id,$document_id
			)
		);
    }
    
     public function delete($document_id)
    {
		
        	return	$this->wpdb->query(
				$this->wpdb->prepare(
					"DELETE from " . $this->table . " WHERE document_id=%d", 
					$document_id
				)
			);
		
    }
    
    public function get_all_signers($document_id)
	{

		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM " . $this->table . " WHERE document_id = %d", $document_id
				)
			);
                
                
                
	}
    
    
}