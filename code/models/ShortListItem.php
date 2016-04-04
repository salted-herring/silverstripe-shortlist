<?php
/*
 * @file ShortListItem.php
 *
 * An item that gets added to a shortlist.
 *
 * Any DataObject that is attached to a ShortListItem **MUST**:
 *
 * * Provide Link() & AbsoluteLink() functions.
 * * Provide a preview for the item with forTemplate()
 *
 **/
class ShortListItem extends DataObject {
	private static $has_one = array(
		'ShortList'	=> 'ShortList',
		'Item'		=> 'DataObject',
		'ItemType'	=> 'Varchar(128)'
	);
}
