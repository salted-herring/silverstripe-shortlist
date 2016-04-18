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
        Session::start();
        $sessionID = session_id();

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

    public function testAddPageToShortlist() {
        $testpage = $this->objFromFixture('Page', 'page1');
        $shortlist = $this->objFromFixture('ShortList', 'test');
        Session::start();
        $sessionID = session_id();

        $response = $this->get('shortlist/add?id=' . $testpage->ID . '&type=Page&s=' . $sessionID . '&output=0');

        $shortlist = DataObject::get_one('ShortList', array('SessionID' => $sessionID));

        $this->assertNotEquals($shortlist->ShortListItems()->Count(), 0);
    }

    public function testRemovePageFromShortlist() {
        $shortlist = $this->objFromFixture('ShortList', 'test');
        $testpage = $this->objFromFixture('Page', 'page1');

        Session::start();
        $sessionID = session_id();

        $this->get('shortlist/add?id=' . $testpage->ID . '&type=Page&s=' . $sessionID . '&output=0');

        $shortlist = DataObject::get_one('ShortList', array('SessionID' => $sessionID));

        $this->get('shortlist/remove?id=' . $testpage->ID . '&type=Page&s=' . $sessionID . '&output=0');

        $this->assertEquals($shortlist->ShortListItems()->Count(), 0);
    }
}
