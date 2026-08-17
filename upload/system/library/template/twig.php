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

			// --- поддержка OCMOD Встроена проверка - работает без модификатора и fallback ---
			if (defined('DIR_CATALOG') && is_file(DIR_MODIFICATION . 'admin/view/template/' . $filename . '.twig')) {
				$code = file_get_contents(DIR_MODIFICATION . 'admin/view/template/' . $filename . '.twig');
			} elseif (is_file(DIR_MODIFICATION . 'catalog/view/theme/' . $filename . '.twig')) {
				$code = file_get_contents(DIR_MODIFICATION . 'catalog/view/theme/' . $filename . '.twig');
			} elseif (is_file($file)) {
				// !!! эта строка нужна для OCMOD поиска. С установленным modification.xml — OCMOD перехватывает и расширяет
				$code = file_get_contents($file);
			} else {
				// сообщение об ошибке, если файл шаблона не найден.
				throw new \Exception('Error: Could not load template file: ' . $file . '!');
			}
		}

		try {
			// если передан динамический код — подключаем ArrayLoader
			if ($code) {
				$loader = new ArrayLoader([$filename . '.twig' => $code]);
				$chain  = new ChainLoader([$loader, new FilesystemLoader([DIR_TEMPLATE])]);
				$this->twig->setLoader($chain);
			}

			return $this->twig->render($filename . '.twig', $this->data);
		} catch (SyntaxError $e) {
			$error_message = sprintf(
			'Syntax Error in template "%s" at line %d: %s',
			$e->getSourceContext()->getName(),
			$e->getTemplateLine(),
			$e->getMessage()
			);
			trigger_error('Error: ' . $error_message, E_USER_WARNING);
			throw new \Exception($error_message);
		} catch (RuntimeError $e) {
			$error_message = sprintf(
			'Runtime Error in template "%s" at line %d: %s',
			$e->getSourceContext()->getName(),
			$e->getTemplateLine(),
			$e->getMessage()
			);
			trigger_error('Error: ' . $error_message, E_USER_WARNING);
			throw new \Exception($error_message);
		} catch (\Exception $e) {
			$error_message = sprintf(
			'Could not render template "%s". Original message: %s',
			$filename . '.twig',
			$e->getMessage()
			);
			trigger_error('Error: ' . $error_message, E_USER_WARNING);
			throw new \Exception($error_message);
		}
	}
}