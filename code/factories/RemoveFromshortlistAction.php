<?php
/**
 * @file RemoveFromshortlistAction.php
 *
 *
 * */

class RemoveFromshortlistAction extends AbstractShortlistAction
{
    /**
     * Remove item from shortlist
     *
     * @param shortlist shortlist to perform action on.
     * @param ID id of the object to add.
     * @param type classname of the item to remove.
     * @param session session id.
     *
     * */
    public function performAction(ShortList $shortlist = null, $ID = false, $type = null, $session = false)
    {
        if (!$ID || is_null($type) || is_null($shortlist)) {
            return false;
        }

        $query = "ItemType = '".$type."' AND ItemID = ".$ID.' AND ShortListID = '.$shortlist->ID;

        $item = DataObject::get_one('ShortListItem', $filter = $query);

        if ($item && $item->exists()) {
            $item->delete();
        }

        return $shortlist;
    }
}
