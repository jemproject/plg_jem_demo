<?php
/**
 * @version 2.1.2
 * @package JEM
 * @subpackage JEM Demo Plugin
 * @copyright (C) 2013-2015 joomlaeventmanager.net
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

include_once(JPATH_SITE.'/components/com_jem/helpers/helper.php');

class plgJemDemo extends JPlugin {

	protected $_datapath = '';
	protected $_db = null;

	/**
	 * Constructor
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 *
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();

		$this->_datapath = __DIR__.'/data/';
		$this->_db = JFactory::getDBO();
	}


	/**
	 * This method handles the (re)creation of demo data.
	 * It's triggered at begin of component's cleanup function.
	 *
	 * @access	public
	 *
	 * @param   object  JEM configuration settings
	 * @param   int     0: dayly, 1: forced, 2: forced on write
	 * @return	boolean
	 *
	 */
	public function onJemBeforeCleanup($settings, $reason)
	{
		$res = false;

		// we must be activated and also we need jem, but we shouldn't touch anything while saving ;-)
		if (($reason == 2) OR !$this->params->get('reset', '0') OR !class_exists('JemHelper')) {
			return $res;
		}

		// clear current data
		$query = 'TRUNCATE TABLE #__jem_categories;';
		$this->_db->setQuery($query);
		$this->_db->execute();
		$query = "INSERT IGNORE INTO `#__jem_categories` (`id`, `parent_id`, `lft`, `rgt`, `level`, `catname`, `alias`, `access`, `published`) VALUES (1, 0, 0, 1, 0, 'root', 'root', 1, 1);";
		$this->_db->setQuery($query);
		$this->_db->execute();
		$query = 'TRUNCATE TABLE #__jem_venues;';
		$this->_db->setQuery($query);
		$this->_db->execute();
		$query = 'TRUNCATE TABLE #__jem_events;';
		$this->_db->setQuery($query);
		$this->_db->execute();
		$query = 'TRUNCATE TABLE #__jem_cats_event_relations;';
		$this->_db->setQuery($query);
		$this->_db->execute();
		$query = 'TRUNCATE TABLE #__jem_attachments;';
		$this->_db->setQuery($query);
		$this->_db->execute();
		$query = 'TRUNCATE TABLE #__jem_register;';
		$this->_db->setQuery($query);
		$this->_db->execute();

		// delete images and attachments
		JemHelper::delete_unused_image_files('events');
		JemHelper::delete_unused_image_files('venues');
		JemHelper::delete_unused_image_files('categories');
		JemHelper::delete_unused_attachment_files();

		// copy images
		$sourcePath = JPath::clean(__DIR__ . '/data');
		$imagePath  = JPath::clean(JPATH_SITE.'/images/jem');
		$mediaPath  = JPath::clean(JPATH_SITE.'/media/com_jem');
		$subdirs = array('/categories', '/events', '/venues');
		foreach ($subdirs as $subdir) {
			$fileList = JFolder::files($sourcePath.$subdir);
			if ($fileList !== false) {
				foreach ($fileList as $file)
				{
					if (is_file($sourcePath.$subdir.'/'.$file) && substr($file, 0, 1) != '.') {
						JFile::copy($sourcePath.$subdir.'/'.$file, $imagePath.$subdir.'/'.$file);
					}
				}
			}
		}

		// copy attachments
		$subdir = '/attachments';
		JFolder::copy($sourcePath.$subdir, $mediaPath.$subdir, '', true);

		// execute sql file to create demo data
		$queries = $this->readSqlQueries('demo.sql');
		foreach ($queries as $query) {
			$this->_db->setQuery($query);
			$this->_db->execute();

		}

		return $res;
	}

	/**
	 * Reads given file and returns array of lines.
	 *
	 * @access	protected
	 * @return	array  Queries read from file
	 *
	 */
	protected function readSqlQueries($filename)
	{
		$res = array();

		$lines = file($this->_datapath . $filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if (!empty($lines)) {
			$tmp = '';
			foreach ($lines as $line) {
				// collect all non-comment lines, add collected lines if terminated with ';'
				if (strncmp($line, '--', 2) != 0) {
					$tmp .= $line;
					if (strrchr($line, ';') == ';') {
						$res[] = $tmp;
						$tmp = '';
					} else {
						$tmp .= ' ';
					}
				}
			}
		}

		return $res;
	}
}
?>