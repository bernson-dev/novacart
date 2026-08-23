<?php
class ControllerStartupUpgrade extends Controller {
	public function index() {
		$route = $this->request->get['route'] ?? '';

		// Guard: если маршрут исключает апгрейд — сразу выходим
		if ($route === 'install/step_5')
			return;
		if ($route === 'install/step_5/deleteInstallFolder')
			return;
		if (str_starts_with($route, 'upgrade/'))
			return;

		// Guard: если config.php существует и требует апгрейда — редирект
		if (
		is_file(DIR_OPENCART . 'config.php') &&
		filesize(DIR_OPENCART . 'config.php') > 0 &&
		empty($this->session->data['install'])
		) {
			$this->response->redirect($this->url->link('upgrade/upgrade'));
		}
	}
}
