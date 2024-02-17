<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2022, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/
namespace Opencart\System\Library;
/**
 * Class Language
 */
class Language {
	/**
	 * @var string
	 */
	protected string $code;
	/**
	 * @var string
	 */
	protected string $directory;
	/**
	 * @var array
	 */
	protected array $path = [];
	/**
	 * @var array
	 */
	protected array $data = [];
	/**
	 * @var array
	 */
	protected array $cache = [];

	/**
	 * Constructor
	 *
	 * @param    string  $code
	 *
	 */
	public function __construct(string $code, \Opencart\System\Engine\Config $config) {
		$this->code = $code;
        $this->config=$config;
	}

	/**
	 * addPath
	 *
	 * @param    string  $namespace
	 * @param    string  $directory
	 *
	 * @return   void
	 */
	public function addPath(string $namespace, string $directory = ''): void {
		if (!$directory) {
			$this->directory = $namespace;
		} else {
			$this->path[$namespace] = $directory;
		}
	}

	/**
     * Get language text string
     *
     * @param	string	$key
	 * 
	 * @return	string
     */
	public function get(string $key) {
		return isset($this->data[$key]) ? $this->data[$key] : $key;
	}

	/**
     *  Set language text string
     *
     * @param	string	$key
	 * @param	string	$value
     */	
	public function set(string $key, string $value) {
		$this->data[$key] = $value;
	}
	
	/**
     * All
     *
	 * @return	array
     */
	public function all(string $prefix = ''): array {
		if (!$prefix) {
			return $this->data;
		}

		$_ = [];

		$length = strlen($prefix);

		foreach ($this->data as $key => $value) {
			if (substr($key, 0, $length) == $prefix) {
				$_[substr($key, $length + 1)] = $value;
			}
		}

		return $_;
	}

	/**
	 * Clear
	 *
	 * @return	void
	 */
	public function clear(): void {
		$this->data = [];
	}

	/**
     * Load
     *
     * @param	string	$filename
	 * @param	string	$code 		Language code
	 * 
	 * @return	array
     */
	public function load(string $filename, string $prefix = '', string $code = ''): array {

		if (!$code) {
			$code = $this->code;
		}

        if(!isset($_GET['language'])){
            $acepted_languajes = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $array_languajes=explode(";", $acepted_languajes);

            if (preg_match("/es/i", $array_languajes[0])){
                // $code = "es-es";
                $this->config->set('config_language_id', 2);
                $this->config->set('config_language','es-es');
            }else{
                //$code = "en-gb";
                $this->config->set('config_language_id', 1);
                $this->config->set('config_language','en-gb');
            }
        }

		if (!isset($this->cache[$code][$filename])) {
			$_ = [];

			// Load selected language file to overwrite the default language keys
			$file = $this->directory . $code . '/' . $filename . '.php';

			$namespace = '';

			$parts = explode('/', $filename);

			foreach ($parts as $part) {
				if (!$namespace) {
					$namespace .= $part;
				} else {
					$namespace .= '/' . $part;
				}

				if (isset($this->path[$namespace])) {
					$file = $this->path[$namespace] . $code . substr($filename, strlen($namespace)) . '.php';
				}
			}

			if (is_file($file)) {
				require($file);
			}

			$this->cache[$code][$filename] = $_;
		} else {
			$_ = $this->cache[$code][$filename];
		}

		if ($prefix) {
			foreach ($_ as $key => $value) {
				$_[$prefix . '_' . $key] = $value;

				unset($_[$key]);
			}
		}

		$this->data = array_merge($this->data, $_);

		return $this->data;
	}
}
