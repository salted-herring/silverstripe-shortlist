<?php
/**
 * @file AddToshortlistAction.php
 *
 *
 * */

class AddToshortlistAction extends AbstractShortlistAction
{
    /**
     * Add item to shortlist
     *
     * @param shortlist shortlist to perform action on.
     * @param ID id of the object to add.
     * @param type classname of the item to remove.
     * @param session session id.
     *
     * */
    public function performAction(
        $shortlist = null,
        $ID = false,
        $type = null,
        $session = false)
    {

        if (!$ID || is_null($type) || !$session) {
            return false;
        }

        if (!$shortlist || !$shortlist->exists()) {
            $shortlist = new ShortList();
            $shortlist->SessionID = $session;
            $shortlist->write();
        }

        // check whether the itme is already in the list
        // before attempting to add it.
        $existing = $shortlist->ShortListItems()->filterAny(
            array('ItemID' => $ID, 'ItemType' => $type)
        );

        // Item already exists, we're not going to add it again.
        if ($existing->count() == 1) {
            return $shortlist;
        }

        $shortlistItem = new ShortListItem();
        $shortlistItem->ShortListID = $shortlist->ID;
        $shortlistItem->ItemID = $ID;
        $shortlistItem->ItemType = $type;

        $shortlist->ShortListItems()->add($shortlistItem);
        $shortlist->write();

        return $shortlist;
    }
}
