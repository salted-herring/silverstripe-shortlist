<?php

/*
 * @file ShortListExtension.php
 *
 * Extensions for Shortlist
 */
class ShortListExtension extends DataExtension
{
    /**
     * Return the shortlist URL for this session.
     * */
    public function getShortlistURL($full = false)
    {
	    if ($full) {
		    $shortlist = $this->getShortList();

	        if ($shortlist && $shortlist->exists()) {
	            return $shortlist->Link();
	        }
	    } else {
		    return Config::inst()->get('ShortList', 'URLSegment');
	    }


        return false;
    }

    /**
     * Return the current session id.
     * */
    public function getSessionID()
    {
		Session::start();
		return session_id();
	}

	public function getShortList()
	{
		return DataObject::get_one('ShortList', $filter = array('SessionID' => session_id()));
	}

	public function getShortListCount()
	{
		$shortlist = $this->getShortList();

		if ($shortlist && $shortlist->exists()) {
			return $shortlist->ShortListItems()->Count();
		}

		return 0;
	}

	public function AddedToShortList($ID = null, $type = null) {
		if (is_null($ID) || is_null($type)) {
			return false;
		}

		$shortlist = $this->getShortList();

		if ($shortlist) {
			$items = $shortlist->ShortListItems()->filter(array('ItemID' => $ID, 'ItemType' => $type));

			return $items->Count() > 0;
		}

		return 0;
	}
}
