<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Document url sending module
 *
 * @arg		check "use_sender_name" "Felado nevenek hasznalata" 0 "1" 0
 * @arg		check "use_friend_name" "Cimzett nevenek hasznalata" 0 "1" 0
 * @arg		check "use_sender_email" "Felado email cimenek hasznalata" 0 "1" 0
 * @arg		check "use_message" "Uzenet hasznalata" 0 "0" 0
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_box_DocSend extends sys_BoxModule {

	function get_doc_data() {
		if (preg_match ('@^/?([a-zA-Z]{2,7})((?:/[^/]+)+)$@', $_POST ['url'], $regex)) {
			$lang = $regex [1];
			$docpath = substr ($regex [2], 1);
			$doc = sys_DocFactory::getDocByDocPath ($this->argArr ['locale_id'], $docpath);
			return $doc;
		}
		return false;
	}

	function verify_inputs(&$result_arr) {
		$errors = array ();
		if (!(checkEmailAddressValid (trim ($_POST ['friendEmail'])))) {
			$errors ['bad_friend_email_format'] = 1;
		} else {
			$result_arr ['friend_email'] = $_POST ['friendEmail'];
		}
		if ($this->argArr ['use_sender_name']) {
			if ('' == $_POST ['senderName']) {
				$errors ['no_sender_name'] = 1;
			} else {
				$result_arr ['sender_name'] = $_POST ['senderName'];
			}
		}
		if ($this->argArr ['use_sender_email']) {
			if ('' == $_POST ['senderEmail']) {
				$errors ['no_sender_email'] = 1;
			} elseif (!checkEmailAddressValid ($_POST ['senderEmail'])) {
				$errors ['bad_sender_email_format'] = 1;
			} else {
				$result_arr ['sender_email'] = $_POST ['senderEmail'];
			}
		}
		if ($this->argArr ['use_friend_name']) {
			if ('' == $_POST ['friendName']) {
				$errors ['no_friend_name'] = 1;
			} else {
				$result_arr ['friend_name'] = $_POST ['friendName'];
			}
		}
		if ($this->argArr ['use_message']) {
			if ('' == $_POST ['message']) {
				$errors ['no_message'] = 1;
			} else {
				$result_arr ['message'] = $_POST ['message'];
			}
		}
		$this->smarty->assign ('errors', $errors);
		return !(bool) count ($errors);
	}

	function add_plaintext_fields(&$result_arr) {
		$result_arr ['FullObject'] ['lead_plain'] = html_entity_decode (strip_tags (preg_replace ('@<[bB][rR]\s*/?>@', "\r\n", $result_arr ['FullObject'] ['lead'])));
		$result_arr ['FullObject'] ['doc_plain'] = html_entity_decode (strip_tags (preg_replace ('@<[bB][rR]\s*/?>@', "\r\n", $result_arr ['FullObject'] ['content'])));
	}

	function main() {
		header ('Content-Type: text/xml');
		$result_arr = array ();
		if ($this->verify_inputs ($result_arr)) {
			$doc = $this->get_doc_data ();
			if ($doc) {
				$result_arr += $doc->getDocData ();
				$this->add_plaintext_fields ($result_arr);
				$result_arr ['http_host'] = $_SERVER ['HTTP_HOST'];
				$this->smarty->assign ('resultArr', $result_arr);
				$mailer = sys_LibFactory::getMailer ();
				$mailer->IsHTML (true);
				$mailer->From = MAIL_SEND_ADDRESS;
				$mailer->FromName = MAIL_SEND_NAME;
				$mailer->AddAddress ($result_arr ['friend_email']);
				$mailer->Subject = $this->smarty->fetch ('misc/DocSend/subject.tpl');
				$mailer->Body = $this->smarty->fetch ('misc/DocSend/body.tpl');
				$mailer->AltBody = $this->smarty->fetch ('misc/DocSend/plain_body.tpl');
				$mailer->Send ();
				$this->smarty->assign ('sendSuccess', true);
			} else {
				$this->smarty->append ('errors', 'docNotFound');
			}
		}
		return $this->smartyFetch ();
	}
}
?>