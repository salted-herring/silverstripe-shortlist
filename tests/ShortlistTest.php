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

    public function testAddPageToShortlist() {
        $testpage = $this->objFromFixture('Page', 'page1');

        $this->get('shortlist/add?id=' . $testpage->ID . '&type=Page&s=' . session_id() . '&output=0');

        $shortlist = DataObject::get_one('ShortList', array('SessionID' => session_id()));

        $this->assertNotEquals($shortlist->ShortListItems()->Count(), 0);
    }

    public function testRemovePageFromShortlist() {
        $shortlist = $this->objFromFixture('ShortList', 'test');
        $testpage = $this->objFromFixture('Page', 'page1');

        $this->get('shortlist/add?id=' . $testpage->ID . '&type=Page&s=' . session_id() . '&output=0');

        $shortlist = DataObject::get_one('ShortList', array('SessionID' => session_id()));

        $this->get('shortlist/remove?id=' . $testpage->ID . '&type=Page&s=' . session_id() . '&output=0');

        $this->assertEquals($shortlist->ShortListItems()->Count(), 0);
    }
}
