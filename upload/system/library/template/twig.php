<?php
namespace Opencart\System\Library\Template;
class Twig {
	protected $root;
	protected $loader;
	protected $directory;
	protected $path = [];

	/**
	 * Constructor
	 *
	 * @param    string $adaptor
	 *
	 */
	public function __construct() {
		// Unfortunately we have to set the web root directory as the base since Twig confuses which template cache to use.
		$this->root = substr(DIR_OPENCART, 0, -1);

		// We have to add the C directory as the base directory because twig can only accept the fist namespace/
		// rather than a multiple namespace system which took me less than a minute to write. If symphony is like
		// this then I have nopt idea why people use the framework.
		$this->loader = new \Twig\Loader\FilesystemLoader('/', $this->root);
	}

	/**
	 * addPath
	 *
	 * @param    string $namespace
	 * @param    string $directory
	 */
	public function addPath($namespace, $directory = '') {
		if (!$directory) {
			$this->directory = $namespace;
		} else {
			$this->path[$namespace] = $directory;
		}
	}

	/**
	 * Render
	 *
	 * @param	string	$filename
	 * @param	array	$data
	 * @param	string	$code
	 *
	 * @return	array
	 */
	public function render($filename, $data = [], $code = '') {
		$file = $this->directory . $filename . '.twig';

		/*
		 * FYI all the Twig lovers out there!
		 * The Twig syntax is good, but the implementation and the available methods is a joke!
		 *
		 * All the Symfony developer has done is create a garbage frame work putting 3rd party scripts into DI containers.
		 * The Twig syntax he ripped off from Jinja and Django templates then did a garbage implementation!
		 *
		 * The fact that this system cache is just compiling php into more php code instead of html is a disgrace!
		 */

		$path = '';

		$namespace = '';

		$parts = explode('/', $filename);

		foreach ($parts as $part) {
			if (!$namespace) {
				$namespace .= $part;
			} else {
				$namespace .= '/' . $part;
			}

			if (isset($this->path[$namespace])) {
				$file = $this->path[$namespace] . substr($filename, strlen($namespace) + 1) . '.twig';
			}
		}

		// We have to remove the root web directory.
		$file = substr($file, strlen($this->root) + 1);

		if ($code) {
			// render from modified template code
			$loader = new \Twig\Loader\ArrayLoader([$file . '.twig' => $code]);
		} else {
			$loader = $this->loader;
		}

		try {
			// Initialize Twig environment
			$config = [
				'charset'     => 'utf-8',
				'autoescape'  => false,
				'debug'       => false,
				'auto_reload' => true,
				'cache'       => DIR_CACHE . 'template/'
			];

			$twig = new \Twig\Environment($loader, $config);

			return $twig->render($file, $data);
		} catch (Twig_Error_Syntax $e) {
			error_log('Error: Could not load template ' . $filename . '!');
			exit();
		}
	}
}