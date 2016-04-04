<?php

/**
 * @file
 * This is the model for the short list URLs.
 */

/**
 * A model to store the short list of items and a unique URL.
 */
class ShortList extends DataObject {

	/**
	 * Fields.
	 */
	private static $db = array(
		'SessionID' 	=> 'varchar(32)',
		'URL' 			=> 'Varchar(100)',
		'UserAgent'		=> 'Varchar(512)'
	);

	/**
	 * Attaches to many items.
	 */
	private static $has_many = array(
		'ShortListItems' => 'ShortListItem'
	);

	/**
	 * Where the URL is unique.
	 */
	private static $indexes = array(
		'UniqueURL'		=> array(
			'type' 		=> 'unique',
			'value' 	=> 'URL'
		)
	);

	/**
	 * Ensure we populate these fields before a save.
	 */
	public function onBeforeWrite() {

		// Run other beforewrites first.
		parent::onBeforeWrite();

		if (!$this->isBrowser()) {
			return false;
		}

		// If this is the first save...
		if(!$this->ID) {

			// Ensure the session exists before querying it.
			if (!Session::request_contains_session_id()) {
				Session::start();
			}

			// Store the sesion and has information in the database.
			$this->SessionID = session_id();

			if (is_null($this->SessionID)) {
				return false;
			}

			// Go through 10 attempts at making a unique URL.
			for ($i=0; $i<10; $i++) {

				// Generate a hash.
				$gen = new RandomGenerator();
				$uniqueurl = substr($gen->randomToken(), 0, 100);

				// Is it unique?
				if (ShortList::get()->filter('URL', $uniqueurl)->count() > 0) {

					// Not unique? Try again.
					continue;
				}

				if (is_null($uniqueurl)) {
					break;
				}

				// Save the unique URL.
				$this->URL = $uniqueurl;
				$this->UserAgent = $_SERVER['HTTP_USER_AGENT'];

				// Done.
				break;
			}
		}
	}

	/**
	 * All URLs are prefixed with URLSegment from config.
	 */
	public function Link() {
		return Config::inst()->get('ShortList', 'URLSegment') . $this->URL;
	}

	/**
	 * A basic browser detection.
	 * * if a browser is not here - assume it's a crawler or a bot. In which case, it shouldn't create a shortlist.
	 *
	 * @see http://stackoverflow.com/a/1537636
	 * */
	public static function isBrowser() {
		// Regular expression to match common browsers
		$browserlist = '/(opera|aol|msie|firefox|chrome|konqueror|safari|netscape|navigator|mosaic|lynx|amaya|omniweb|avant|camino|flock|seamonkey|mozilla|gecko)+/i';

		$validBrowser = preg_match($browserlist, $_SERVER['HTTP_USER_AGENT']) === 1;

		return $validBrowser;// && !empty($_SERVER['HTTP_REFERER']);
	}
}
