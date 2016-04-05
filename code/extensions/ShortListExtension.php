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
    public function getShortlistURL()
    {
        $shortlist = ShortList::get()->filter(array('SessionID' => session_id()));

        if ($shortlist->first() && $shortlist->first()->Events()->count() != 0) {
            return $shortlist->first()->Link();
        }

        return '#';
    }
}
