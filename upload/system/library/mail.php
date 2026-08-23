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
	protected $data = array(); // Массив для динамических свойств (smtp_hostname, debug и т.д.)
	public $parameter;

	public function __construct($adaptor = 'mail') {
		$class = 'Mail\\' . $adaptor;
		
		if (class_exists($class)) {
			$this->adaptor = new $class();
		} else {
			trigger_error('Error: Could not load mail adaptor ' . $adaptor . '!');
			exit();
		}
	}
	
	// Магический метод для записи динамических свойств (например, $mail->debug = true)
	public function __set($key, $value) {
		$this->data[$key] = $value;
	}

	// Магический метод для чтения
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

		// Передаем основные защищенные свойства
		$this->adaptor->to = $this->to;
		$this->adaptor->from = $this->from;
		$this->adaptor->sender = $this->sender;
		$this->adaptor->reply_to = $this->reply_to;
		$this->adaptor->subject = $this->subject;
		$this->adaptor->text = $this->text;
		$this->adaptor->html = $this->html;
		$this->adaptor->attachments = $this->attachments;
		$this->adaptor->parameter = $this->parameter;
		// Передаем все динамические свойства (smtp_hostname, debug и т.д.)
		foreach ($this->data as $key => $value) {
			$this->adaptor->{$key} = $value;
		}
		
		return $this->adaptor->send();
	}

	private function error($text) {
		return user_error($text, E_USER_WARNING);
	}
}
