<?php

class ShortlistTest extends FunctionalTest
{
    protected static $fixture_file = 'fixtures.yml';

    public function testShortlistCreation()
    {
        $shortlists = ShortList::get();

        $this->get('shortlist');

        $this->assertNotEquals($shortlists->Count(), 0);
    }

    public function testAddItemtoShortList()
    {
        $testpage = $this->objFromFixture('Page', 'page1');

        // create the shortlist
        $shortlist = new ShortList();
        $shortlist->write();

        $this->assertEquals($shortlist->ShortListItems()->Count(), 0);

        // create the item.
        $item = new ShortListItem();
        $item->ItemType = $testpage->ClassName;
        $item->ItemID = $testpage->ID;
        $item->write();

        // add to the shortlist.
        $shortlist->ShortListItems()->add($item);
        $shortlist->write();

        // do we have an item added?
        $this->assertNotEquals($shortlist->ShortListItems()->Count(), 0);
        // what about the reverse side?
        $this->assertNotEquals($item->ShortList()->ID, 0);
    }

    public function testRemoveItemFromShortList()
    {
        $testpage = $this->objFromFixture('Page', 'page1');

        // create the shortlist
        $shortlist = new ShortList();
        $shortlist->write();

        $this->assertEquals($shortlist->ShortListItems()->Count(), 0);

        // create the item.
        $item = new ShortListItem();
        $item->ItemType = $testpage->ClassName;
        $item->ItemID = $testpage->ID;
        $item->write();

        // add to the shortlist.
        $shortlist->ShortListItems()->add($item);
        $shortlist->write();

        // do we have an item added?
        $this->assertNotEquals($shortlist->ShortListItems()->Count(), 0);
        // what about the reverse side?
        $this->assertNotEquals($item->ShortList()->ID, 0);

        $shortlist->ShortListItems()->remove($item);

            // do we have an item removed?
        $this->assertEquals($shortlist->ShortListItems()->Count(), 0);
    }
}
