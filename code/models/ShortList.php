<?php

/**
 * @file
 * This is the model for the short list URLs.
 */

/**
 * A model to store the short list of items and a unique URL.
 */
class ShortList extends DataObject
{

    /**
     * Fields.
     */
    private static $db = array(
        'SessionID'     => 'varchar(64)',
        'URL'           => 'Varchar(255)',
        'UserAgent'     => 'Varchar(512)'
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
        'UniqueURL'     => array(
            'type'      => 'unique',
            'value'     => 'URL'
        )
    );

    /**
     * Ensure we populate these fields before a save.
     */
    public function onBeforeWrite()
    {

        // Run other beforewrites first.
        parent::onBeforeWrite();

        if (!$this->isBrowser()) {
            return false;
        }

        // If this is the first save...
        if (!$this->ID) {
            // Ensure the session exists before querying it.
            if (!Session::request_contains_session_id()) {
                Session::start();
            }

            // Store the sesion and has information in the database.
            $this->SessionID = SecurityToken::getSecurityID();

            if (is_null($this->SessionID)) {
                return false;
            }

            $gen = new RandomGenerator();
            $uniqueurl = substr($gen->randomToken(), 0, 32);

            while (ShortList::get()->filter('URL', $uniqueurl)->count() > 0) {
                $uniqueurl = substr($gen->randomToken(), 0, 32);
            }

            $this->URL = $uniqueurl;
            $this->UserAgent = Controller::curr()->getRequest()->getHeader('User-Agent');
        }
    }

    /**
     * All URLs are prefixed with URLSegment from config.
     */
    public function Link($action = null)
    {
        return Config::inst()->get('ShortList', 'URLSegment') . $this->URL;
    }

    /**
     * A basic browser detection.
     * * if a browser is not here - assume it's a crawler or a bot. In which case, it shouldn't create a shortlist.
     *
     * @see http://stackoverflow.com/a/1537636
     * */
    public static function isBrowser()
    {
        // Regular expression to match common browsers
        $browserlist = '/(opera|aol|msie|firefox|chrome|konqueror|safari|netscape|navigator|mosaic|lynx|amaya|omniweb|avant|camino|flock|seamonkey|mozilla|gecko)+/i';

        $userAgent = strtolower(Controller::curr()->getRequest()->getHeader('User-Agent'));

        return preg_match($browserlist, $userAgent) === 1;
    }
}
