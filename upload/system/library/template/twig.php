<?php
namespace Template;

use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\ChainLoader;
use Twig\Extension\DebugExtension;
use Twig\Error\SyntaxError;
use Twig\Error\RuntimeError;

final class Twig {
	private $data = [];
	private $debug;
	private $twig;

	public function __construct($debug = false) {
		$this->debug = (bool)$debug;

		$config = [
			'charset'     => 'utf-8',
			'autoescape'  => false,
			'debug'       => $this->debug,
			'auto_reload' => true,
			'cache'       => DIR_CACHE . 'template/',
		];

		$loader = new FilesystemLoader([DIR_TEMPLATE]);
		$this->twig = new Environment($loader, $config);

		if ($this->debug && class_exists(DebugExtension::class)) {
			$this->twig->addExtension(new DebugExtension());
		}
	}

	public function set($key, $value) {
		$this->data[$key] = $value;
		return $this; // fluent интерфейс
	}

	public function render($filename, $code = '') {
		if (!$code) {
			$file = DIR_TEMPLATE . $filename . '.twig';
			$template_file = '';

			// --- поддержка OCMOD Встроена проверка - работает без модификатора и fallback ---
			if (defined('DIR_CATALOG') && is_file(DIR_MODIFICATION . 'admin/view/template/' . $filename . '.twig')) {
				$template_file = DIR_MODIFICATION . 'admin/view/template/' . $filename . '.twig';
			} elseif (is_file(DIR_MODIFICATION . 'catalog/view/theme/' . $filename . '.twig')) {
				$template_file = DIR_MODIFICATION . 'catalog/view/theme/' . $filename . '.twig';
			} elseif (is_file($file)) {
				// !!! эта строка нужна для OCMOD поиска. С установленным modification.xml — OCMOD перехватывает и расширяет
				$template_file = $file;
			} else {
				throw new \Exception('Error: Could not load template file: ' . $file . '!');
			}

			$code = file_get_contents($template_file);

			if ($code === false) {
				throw new \Exception('Error: Could not read template file: ' . $template_file . '!');
			}
		}

		try {
			// Основной шаблон может передаваться как динамический код.
			// Вложенные include/extends сначала ищем среди OCMOD-файлов,
			// затем в оригинальном DIR_TEMPLATE.
			if ($code !== '') {
				$loaders = [
					new ArrayLoader([$filename . '.twig' => $code])
				];

				if (defined('DIR_CATALOG')) {
					$modified_template_dir = DIR_MODIFICATION . 'admin/view/template/';
				} else {
					$modified_template_dir = DIR_MODIFICATION . 'catalog/view/theme/';
				}

				if (is_dir($modified_template_dir)) {
					$loaders[] = new FilesystemLoader([$modified_template_dir]);
				}

				$loaders[] = new FilesystemLoader([DIR_TEMPLATE]);

				$this->twig->setLoader(new ChainLoader($loaders));
			}

			return $this->twig->render($filename . '.twig', $this->data);
		} catch (SyntaxError $e) {
			$error_message = sprintf(
				'Syntax Error in template "%s" at line %d: %s',
				$e->getSourceContext()->getName(),
				$e->getTemplateLine(),
				$e->getMessage()
			);

			throw new \Exception($error_message, 0, $e);
		} catch (RuntimeError $e) {
			$error_message = sprintf(
				'Runtime Error in template "%s" at line %d: %s',
				$e->getSourceContext()->getName(),
				$e->getTemplateLine(),
				$e->getMessage()
			);

			throw new \Exception($error_message, 0, $e);
		} catch (\Exception $e) {
			$error_message = sprintf(
				'Could not render template "%s". Original message: %s',
				$filename . '.twig',
				$e->getMessage()
			);

			throw new \Exception($error_message, 0, $e);
		}
	}
}
