<?php

class ShortlistTest extends FunctionalTest
{
    protected static $fixture_file = 'fixtures.yml';

    public function setUp()
    {
        parent::setUp();
        SS_Datetime::set_mock_now('2013-10-10 20:00:00');
    }

    public function tearDown()
    {
        SS_Datetime::clear_mock_now();
        parent::tearDown();
    }

	public function testShortlistCreation()
	{
        $shortlists = ShortList::get();

        $this->get('shortlist');

        $this->assertNotEquals($shortlists->Count(), 0);
    }

    public function testAddPageToShortlist() {
        $testpage = $this->objFromFixture('Page', 'page1');

        $this->get('shortlist/add?id=' . $testpage->ID . '&type=Page&s=' . session_id());

        $shortlist = DataObject::get_one('ShortList', array('SessionID' => session_id()));

        $this->assertNotEquals($shortlist->ShortListItems()->Count(), 0);
    }

    public function testRemovePageFromShortlist() {
        $shortlist = $this->objFromFixture('ShortList', 'test');
        $testpage = $this->objFromFixture('Page', 'page1');

        $this->get('shortlist/add?id=' . $testpage->ID . '&type=Page&s=' . session_id());

        $shortlist = DataObject::get_one('ShortList', array('SessionID' => session_id()));

        $this->get('shortlist/remove?id=' . $testpage->ID . '&type=Page&s=' . session_id());

        $this->assertEquals($shortlist->ShortListItems()->Count(), 0);
    }
}
