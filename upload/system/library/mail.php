<?php
/**
 * Mail class
 */
class Mail extends \stdClass {
	protected $to;
	protected $from;
	protected $sender;
	protected $reply_to;
	protected $subject;
	protected $text;
	protected $html;
	protected $attachments = array();
	protected $data = array();
	public $parameter;

	public function __construct($adaptor = 'mail') {
		$class = 'Mail\\' . $adaptor;

		if (class_exists($class)) {
			$this->adaptor = new $class();
		} else {
			throw new \Exception('Error: Could not load mail adaptor ' . $adaptor . '!');
		}
	}

	public function __set($key, $value) {
		$this->data[$key] = $value;
	}

	public function __get($key) {
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}

	public function setTo($to) {
		$this->to = $to;
	}

	public function setFrom($from) {
		$this->from = $from;
	}

	public function setSender($sender) {
		$this->sender = $sender;
	}

	public function setReplyTo($reply_to) {
		$this->reply_to = $reply_to;
	}

	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function setText($text) {
		$this->text = $text;
	}

	public function setHtml($html) {
		$this->html = $html;
	}

	public function addAttachment($filename) {
		$this->attachments[] = $filename;
	}

	public function send() {
		if (!$this->to) {
			throw new \Exception('Error: E-Mail to required!');
		}

		if (!$this->from) {
			throw new \Exception('Error: E-Mail from required!');
		}

		if (!$this->sender) {
			throw new \Exception('Error: E-Mail sender required!');
		}

		if (!$this->subject) {
			throw new \Exception('Error: E-Mail subject required!');
		}

		if ((!$this->text) && (!$this->html)) {
			throw new \Exception('Error: E-Mail message required!');
		}

		$this->syncAdaptor();

		return $this->adaptor->send();
	}

	/**
	 * Run adaptor-specific diagnostics.
	 *
	 * Currently implemented by the SMTP adaptor. The caller may set a recipient
	 * and pass true to also send a real test message after connection/auth checks.
	 *
	 * @param bool $send_test_email
	 * @return array
	 * @throws \Exception
	 */
	public function test($send_test_email = false) {
		if (!method_exists($this->adaptor, 'test')) {
			throw new \Exception('Error: Mail adaptor does not support diagnostics!');
		}

		$this->syncAdaptor();

		return $this->adaptor->test((bool)$send_test_email);
	}

	private function syncAdaptor() {
		$this->adaptor->to = $this->to;
		$this->adaptor->from = $this->from;
		$this->adaptor->sender = $this->sender;
		$this->adaptor->reply_to = $this->reply_to;
		$this->adaptor->subject = $this->subject;
		$this->adaptor->text = $this->text;
		$this->adaptor->html = $this->html;
		$this->adaptor->attachments = $this->attachments;
		$this->adaptor->parameter = $this->parameter;

		foreach ($this->data as $key => $value) {
			$this->adaptor->{$key} = $value;
		}
	}
}
