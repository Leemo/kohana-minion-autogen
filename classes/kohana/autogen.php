<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package    Kohana/Minion
 * @author     Alexey Popov
 * @author     Leemo studio
 * @link       http://leemo-studio.net
 * @copyright  (c) 2010-2012 Leemo studio
 * @license    http://kohanaframework.org/license
 */
class Kohana_Autogen {

	/**
	 * Puts contents into file
	 *
	 * @param   string  filename
	 * @param   string  file contents
	 * @return  void
	 */
	public static function write($filename, $contents)
	{
		$filename .= EXT;

		if (is_file($filename))
		{
			throw new Kohana_Exception(__('File :filename already exist', array(
				':filename' => $filename
				)));
		}

		$dir = pathinfo($filename, PATHINFO_DIRNAME);

		if ( ! is_dir($dir))
		{
			mkdir($dir, NULL, TRUE);
		}

		file_put_contents($filename, $contents);
	}

} // End Kohana_Autogen
