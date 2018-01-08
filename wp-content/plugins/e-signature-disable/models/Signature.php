<?php

class WP_E_Signature extends WP_E_Model {

    private $table;

    public function __construct() {

        parent::__construct();

        $this->table = $this->table_prefix . "signatures";

        $this->joinTable = $this->table_prefix . "documents_signatures";
    }

    public function esign_set_json($user_id, $csum_id, $owner_id = false) {
        $document = new WP_E_Document;
        // getting document id from csum id .
        $doc_id = $document->document_id_by_csum($csum_id);
        if ($owner_id) {
            $json = $this->getUserSignature($user_id);
        } else {
            $json = $this->getDocumentSignature($user_id, $doc_id);
        }

        $file_name = ESIGN_PLUGIN_PATH . '/assets/' . $user_id . '-' . $csum_id . '.txt';

        file_put_contents($file_name, $json);

        return false;
    }

    public function esig_get_contents($url) {

        if (ini_get('allow_url_fopen')) {
            return file_get_contents($url);
        } else {
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $contents = curl_exec($ch);
            if (curl_errno($ch)) {
               return curl_error($ch);
                //$contents = '';
            } else {
                curl_close($ch);
            }

            if (!is_string($contents) || !strlen($contents)) {
                return "Failed to get contents.";
                
            }
            
            return $contents;
        }
    }

    public function display_signature($user_id, $check_sum_id, $nonce, $owner_id = false) {


        $this->esign_set_json($user_id, $check_sum_id, $owner_id);

        $image_url = (ESIGN_DIRECTORY_URI . 'lib/sigtoimage.php?uid=' . $user_id . '&doc_id=' . $check_sum_id . '&esig_verify=' . $nonce);
        $image_content = $this->esig_get_contents($image_url);
        return "data:image/png;base64," . base64_encode($image_content);
    }

    /**

     * Asserts whether or not a user has signed a particular document

     *

     * Note: This is an endpoint method when called by User::hasSignedDocument acting as a passtrhu method

     * 

     * @param $user_id [Integer]

     * @param $document_id [Integer] 

     * @return Boolean

     * @since 0.1.0

     */
    public function userHasSignedDocument($user_id, $document_id) {



        $result = $this->wpdb->get_var(
                $this->wpdb->prepare("SELECT count(*) FROM {$this->table} sigs

				INNER JOIN {$this->joinTable} docs_sigs

				ON sigs.signature_id = docs_sigs.signature_id

				WHERE docs_sigs.document_id = %d AND sigs.user_id = %d", $document_id, $user_id)
        );



        if ($result > 0) {

            return true;
        } else {

            return false;
        }
    }

    public function GetSignatureDate($user_id, $document_id) {



        /* $signature_id = $this->wpdb->get_var(

          $this->wpdb->prepare("SELECT max(signature_id) FROM {$this->table} WHERE user_id = %d", $user_id)

          ); */

        $signature_id = $this->GetSignatureId($user_id, $document_id);

        return $this->wpdb->get_var($this->wpdb->prepare("SELECT sign_date FROM {$this->joinTable} WHERE document_id=%d AND signature_id=%d", $document_id, $signature_id));
    }

    public function GetSignatureId($user_id, $document_id) {


        $signature_details = $this->getDocumentSignatureData($user_id, $document_id);

        if ($signature_details) {
            return $signature_details->signature_id;
        } else {
            return FALSE;
        }
    }

    public function add($signatureJSON, $user_id, $signature_type = false) {



        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {

            //check ip from share internet

            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

            //to check ip is pass from proxy

            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {

            $ip_address = $_SERVER['REMOTE_ADDR'];
        }

        //$ip_address = $_SERVER['REMOTE_ADDR'];



        if (!$signature_type) {

            $signature_type = 'full';
        }



        //$timestamp = time();
        $newdoc = new WP_E_Document();
        $date = $newdoc->esig_date();



        $salt = hash('sha1', mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)); // 40 chars

        $signature_hash = hash('sha256', $signatureJSON);



        $encrypted_signature = $this->encrypt($salt, $signatureJSON);

        // echo '<h1>'.$signatureJSON.'</h1>';

        $data = array(
            "user_id" => $user_id,
            "signature_hash" => $signature_hash,
            "signature_salt" => $salt,
            "encrypted_signature" => $encrypted_signature,
            "signature_added" => $date
        );



        $format = array('%d', '%s', '%s', '%s', '%s');



        $this->wpdb->query(
                $this->wpdb->prepare(
                        "INSERT INTO $this->table (user_id,signature_type,signature_hash,signature_salt, signature_data, signature_added) 

				 VALUES(%d,'%s','%s','%s','%s','%s')", $user_id, $signature_type, $signature_hash, $salt, $encrypted_signature, $date
                )
        );



        return $this->wpdb->insert_id;
    }

    public function join($document_id, $signature_id) {

        $newdoc = new WP_E_Document();

        $data = array(
            "document_id" => $document_id,
            "signature_id" => $signature_id,
            "ip_address" => $_SERVER['REMOTE_ADDR'],
            "sign_date" => $newdoc->esig_date($document_id)
        );

        $this->wpdb->insert($this->joinTable, $data);



        return $this->wpdb->insert_id;
    }

    public function encrypt($salt, $data) {

        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
        if (empty($iv)) {
            $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), mt_rand());
        }
        return base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $salt, true), $data, MCRYPT_MODE_CBC, $iv));
    }

    public function decrypt($salt, $encrypted) {

        $data = base64_decode($encrypted);
        $iv = substr($data, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));
        if (empty($iv)) {
            return false;
        }
        if (!defined('MCRYPT_MODE_CBC')) {
            return false;
        }
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $salt, true), substr($data, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)), MCRYPT_MODE_CBC, $iv
                ), "\0"
        );
    }

    # TODO - DEPRECATE this function. Users can have more than one signature in the signatures table. Use this only for document owners

    public function getSignatureData($user_id) {

        return $this->wpdb->get_row(
                        $this->wpdb->prepare(
                                "SELECT * FROM " . $this->table . " WHERE user_id=%d ORDER BY signature_id DESC", $user_id
                        )
        );
    }

    public function getSignatureData_by_type($user_id, $signature_type) {

        return $this->wpdb->get_row(
                        $this->wpdb->prepare(
                                "SELECT * FROM " . $this->table . " WHERE user_id=%d and signature_type=%s ORDER BY signature_id DESC", $user_id, $signature_type
                        )
        );
    }

    public function getSig_by_type_signatureid($signature_id, $signature_type) {

        return $this->wpdb->get_row(
                        $this->wpdb->prepare(
                                "SELECT * FROM " . $this->table . " WHERE signature_id=%d and signature_type=%s ORDER BY signature_id DESC", $signature_id, $signature_type
                        )
        );
    }

    /**

     * Given a document_id and user_id, returns that user's signatures for that document.

     * 

     * @param $user_id [Integer]

     * @param $document_id [Integer] 

     */
    public function getDocumentSignature($user_id, $document_id) {

        $sig = $this->getDocumentSignatureData($user_id, $document_id);

        if (!empty($sig)) {
            //echo '<h1>,'.stripslashes($this->decrypt($sig->signature_salt, $sig->signature_data)).'</h1>';
            return stripslashes($this->decrypt($sig->signature_salt, $sig->signature_data));
        }
    }

    public function getDocumentSignatures($documentID) {



        return $this->wpdb->get_results(
                        $this->wpdb->prepare(
                                "SELECT * FROM " . $this->table . " s JOIN " . $this->joinTable . " j ON s.signature_id = j.signature_id AND document_id=%d", $documentID
                        )
        );
    }

    public function getDocumentSignatureData($user_id, $document_id) {

        $result = $this->wpdb->get_row(
                $this->wpdb->prepare("SELECT * FROM {$this->table} sigs

				INNER JOIN {$this->joinTable} docs_sigs

				ON sigs.signature_id = docs_sigs.signature_id

				WHERE docs_sigs.document_id = %d AND sigs.user_id = %d

				ORDER BY docs_sigs.sign_date DESC", $document_id, $user_id)
        );

        return $result;
    }

    public function getDocumentSignature_Type($user_id, $document_id) {

        $result = $this->wpdb->get_var(
                $this->wpdb->prepare("SELECT signature_type FROM {$this->table} sigs

				INNER JOIN {$this->joinTable} docs_sigs

				ON sigs.signature_id = docs_sigs.signature_id

				WHERE docs_sigs.document_id = %d AND sigs.user_id = %d

				ORDER BY docs_sigs.sign_date DESC", $document_id, $user_id)
        );

        return $result;
    }

    // Gets the signature for a user. Should only be used for document owner. Signers can have more than one signature. For signers, use getDocumentSignature instead.

    public function getUserSignature($user_id) {

        $sig = $this->getSignatureData($user_id);

        if (!empty($sig)) {

            return stripslashes($this->decrypt($sig->signature_salt, $sig->signature_data));
        }
    }

    public function getUserSignature_by_type($user_id, $signature_type) {

        $sig = $this->getSignatureData_by_type($user_id, $signature_type);

        if (!empty($sig)) {

            return stripslashes($this->decrypt($sig->signature_salt, $sig->signature_data));
        }
    }

    public function getSignature_by_type_sigid($signature_id, $signature_type) {

        $sig = $this->getSig_by_type_signatureid($signature_id, $signature_type);

        if (!empty($sig)) {

            return stripslashes($this->decrypt($sig->signature_salt, $sig->signature_data));
        }
    }

    // Given a row in the signature table, returns signature data for use in an input field.

    public function getSignature($sig) {

        return stripslashes($this->decrypt($sig->signature_salt, $sig->signature_data));
    }

    // return signature by type 



    public function getSignature_by_type($sig) {



        $signature_type = $sig->signature_type;

        if ($signature_type != 'typed') {

            return false;
        }

        return esc_html(stripslashes($this->decrypt($sig->signature_salt, $sig->signature_data)));
    }

    /**

     * Return a signature type

     *

     * @since 1.1.6

     * @param Int ($id) 

     * @return Array

     */
    public function getSignature_type($user_id) {
        return $this->wpdb->get_var(
                        $this->wpdb->prepare(
                                "SELECT signature_type FROM " . $this->table . " WHERE user_id=%s ORDER BY signature_id DESC", $user_id
                        )
        );
    }

    /**

     * Return a user id

     *

     * @since 1.1.6

     * @param Int ($id) 

     * @return Array

     */
    public function getuserid_by_signature_id($signature_id) {
        return $this->wpdb->get_var(
                        $this->wpdb->prepare(
                                "SELECT user_id FROM " . $this->table . " WHERE signature_id=%s ORDER BY signature_id DESC", $signature_id
                        )
        );
    }

    /**

     * Return a signature type

     *

     * @since 1.1.6

     * @param Int ($id) 

     * @return Array

     */
    public function getSignature_type_signature_id($signature_id) {
        return $this->wpdb->get_var(
                        $this->wpdb->prepare(
                                "SELECT signature_type FROM " . $this->table . " WHERE signature_id=%s ORDER BY signature_id DESC", $signature_id
                        )
        );
    }

    /**
     * Return a signature type
     *
     * @since 1.1.6
     * @param Int ($id) 
     * @return Array
     */
    public function getuser_Signature_type($user_id, $document_id) {
        return $this->wpdb->get_var(
                        $this->wpdb->prepare(
                                "SELECT signature_type FROM " . $this->table . " WHERE user_id=%s ORDER BY signature_id DESC", $user_id
                        )
        );
    }

    // Should only be used for document owner. Signers can have more than one signature.

    public function userHasSignature($user_id) {

        $count = $this->wpdb->get_var(
                $this->wpdb->prepare(
                        "SELECT COUNT(*) FROM " . $this->table . " WHERE user_id=%d", $user_id
                )
        );

        if ($count > 0)
            return true;
        else
            return false;
    }

    public function documentHasSignature($document_id) {

        $count = $this->wpdb->get_var(
                $this->wpdb->prepare(
                        "SELECT COUNT(*) FROM " . $this->joinTable . " WHERE document_id=%d", $document_id
                )
        );

        if ($count > 0)
            return true;
        else
            return false;
    }

}
