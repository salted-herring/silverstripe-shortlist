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
        $testpage = $this->objFromFixture('ShortListItem', 'item1');
        $testpage->write();

        // create the shortlist
        $shortlist = new ShortList();
        $shortlist->write();

        $this->assertEquals($shortlist->ShortListItems()->Count(), 0);

        // add to the shortlist.
        $shortlist->ShortListItems()->add($testpage);
        $shortlist->write();

        $retrievedItem = $shortlist->ShortListItems()->filter('ID', $testpage->ID)->first()->getActualRecord();

        // Check the item has been saved correctly
        $this->assertEquals($retrievedItem->ClassName, 'Page');
        $this->assertEquals($retrievedItem->ID, $testpage->ID);

        // do we have an item added?
        $this->assertNotEquals($shortlist->ShortListItems()->Count(), 0);
        // what about the reverse side?
        $this->assertNotEquals($testpage->ShortList()->ID, 0);
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

    /*
    |--------------------------------------------------------------------------
    | Check whether we are or are not a browser.
    |--------------------------------------------------------------------------
    */
    public function testisBrowser()
    {
        $list = $this->objFromFixture('ShortList', 'test');
        $ua = Controller::curr()->getRequest()->getHeader('User-Agent');

        if (empty(strtolower($ua))) {
            $this->assertFalse($list->isBrowser());
        } else {
            $this->assertTrue($list->isBrowser());
        }
    }

    public function testShortListLink()
    {
        $list = $this->objFromFixture('ShortList', 'test');
        $list->write();

        $configURL = Config::inst()->get('ShortList', 'URLSegment');
        $link = $list->Link();

        $configPos = strpos($link, $configURL);
        $linkPos = strpos($link, $list->URL);

        $this->assertGreaterThan($configPos, $linkPos);
    }
}
