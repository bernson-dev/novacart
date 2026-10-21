<?php
class ControllerStartupSass extends Controller {
	public function index() {
		$files = glob(DIR_APPLICATION . 'view/stylesheet/*.scss');

		if ($files) {
			foreach ($files as $file) {
				// Get the filename
				$filename = basename($file, '.scss');

				$stylesheet = DIR_APPLICATION . 'view/stylesheet/' . $filename . '.css';

				// Compile only when CSS is missing. Developer mode also refreshes changed SCSS.
				$needs_compile = !is_file($stylesheet);
				if (!$needs_compile && $this->config->get('developer_sass')) {
					$needs_compile = filemtime($file) > filemtime($stylesheet);
				}

				if ($needs_compile) {
					$scss = new \ScssPhp\ScssPhp\Compiler();
					$scss->setImportPaths(DIR_APPLICATION . 'view/stylesheet/');

					$output = $scss->compileString('@import "' . $filename . '.scss"')->getCss();

					$handle = fopen($stylesheet, 'w');

					flock($handle, LOCK_EX);

					fwrite($handle, $output);

					fflush($handle);

					flock($handle, LOCK_UN);

					fclose($handle);
				}
			}
		}
	}
}
