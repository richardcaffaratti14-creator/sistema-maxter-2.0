<?php

class SendMail {

	private $_from_alias = 'Contact Form';
	private $_sendmail_from = '';
	private $_smtp = 'Localhost';
	private $_smtp_port = 25;
	private $_headers = '';

	function __construct($sendmail_from, $from_alias = 'Contact Form') {
		
		$this->_from_alias = $from_alias;
		$this->_sendmail_from = $sendmail_from;
		
		$this->_headers  = 'MIME-Version: 1.0' . "\r\n";
		$this->_headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$this->_headers .= 'From: ' . $this->_from_alias . ' <' . $this->_sendmail_from . '> ' . "\r\n";
	}

	public function send($to, $subject, $msg, $reply_name = "", $reply_mail = "") {

		if ($reply_name != '')
			$headers .= 'Reply-To: ' . $reply_name . ' <' . $reply_mail . '> ' . "\r\n";

		ini_set("SMTP", $this->_smtp);
		ini_set("sendmail_from", $this->_sendmail_from);
		ini_set("smtp_port", $this->_smtp_port);

		return mail($to, $subject, $msg, $this->_headers);
	}

	//	GETTERS / SETTERS	-------------------------------------------------------
	public function getSmtp() {
		return $this->_smtp;
	}

	public function setSmtp($_smtp) {
		$this->_smtp = $_smtp;
	}

	public function getSmtpPort() {
		return $this->_smtp_port;
	}

	public function setSmtpPort($_smtp_port) {
		$this->_smtp_port = $_smtp_port;
	}

	public function getHeaders() {
		return $this->_headers;
	}

	public function setHeaders($_headers) {
		$this->_headers = $_headers;
	}

}