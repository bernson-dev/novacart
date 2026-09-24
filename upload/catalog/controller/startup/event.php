<?php
class ControllerStartupEvent extends Controller {
	public function index() {

		// NovaCart built-in storefront events.
		// Registered in code so core features do not depend on DB migrations or OCMOD refresh.
		$this->event->register('view/common/header/after', new Action('event/favicon'), 0);
		// Add events from the DB
		$this->load->model('setting/event');

		$results = $this->model_setting_event->getEvents();

		foreach ($results as $result) {
			$this->event->register(substr($result['trigger'], strpos($result['trigger'], '/') + 1), new Action($result['action']), $result['sort_order']);
		}
	}
}
