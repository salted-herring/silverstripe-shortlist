<?php
/*
 * @file ShortListExtension.php
 *
 * Extensions for Shortlist
 */
class ShortListExtension extends DataExtension {

	/**
	 * Is the event in the shortlist?
	 * */
	public function isEventinShortlist($ID = false) {
		if(!$ID) {
			return false;
		}

		$shortlist = ShortList::get()->filter(array('SessionID' => session_id()));

		if($shortlist->first()) {
			return $shortlist->first()->Events()->filter(array('ID' => $ID))->count() == 1;
		}

		return false;
	}

	/**
	 * Return the shortlist URL for this session.
	 * */
	public function getShortlistURL() {
		$shortlist = ShortList::get()->filter(array('SessionID' => session_id()));

		if($shortlist->first() && $shortlist->first()->Events()->count() != 0) {
			return $shortlist->first()->Link();
		}

		return '#';
	}

	/**
	 * Get the number of items in the shortlist.
	 * */
	public function ShortlistCount() {
		$shortlist = ShortList::get()->filter(array('SessionID' => session_id()));

		if($shortlist->first()) {
			return $shortlist->first()->Events()->count();
		}

		return 0;
	}
}
