<?php
/**
 * @file AbstractShortlistAction.php
 *
 * An abstract for creating a factory method to perform actions on a shortlist
 * - ideally this would be for actions such as adding to or removing from a shortlist.
 * */

abstract class AbstractShortlistAction
{

    /**
     * Perform the intended action.
     *
     * @param shortlist shortlist to perform action on.
     * @param ID id of the object to add.
     * @param type classname of the item to remove.
     * @param session session id.
     *
     * */
    public abstract function performAction(
        $shortlist = null,
        $ID = false,
        $type = null,
        $session = false
    );
}
