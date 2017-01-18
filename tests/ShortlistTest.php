<?php

class ShortlistTest extends FunctionalTest
{
    protected static $fixture_file = 'fixtures.yml';
    protected $fixtureFactory = null;

    public function setUpOnce()
    {
        parent::setUpOnce();

        $this->fixtureFactory = Injector::inst()->create('FixtureFactory');
    }

    public function setup()
    {
        parent::setup();

        SecurityToken::enable();
    }

    public function teardown()
    {
        parent::teardown();

        SecurityToken::disable();
    }

    public function testShortlistCreation()
    {
        $shortlists = ShortList::get();

        $this->assertNotEquals($shortlists->Count(), 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Add item to short list DBO.
    |--------------------------------------------------------------------------
    */
    public function testAddItemtoShortList()
    {
        $shortlist = $this->fixtureFactory->get('ShortList', 'test');
        $item = $this->fixtureFactory->get('ShortListItem', 'item1');

        $this->assertEquals($shortlist->ShortListItems()->Count(), 0);

        // add to the shortlist.
        $shortlist->ShortListItems()->add($item);

        $retrievedItem = $shortlist->ShortListItems()->filter('ID', $item->ID)->first()->getActualRecord();

        // Check the item has been saved correctly
        $this->assertEquals($retrievedItem->ClassName, 'Page');
        $this->assertEquals($retrievedItem->ID, $item->ID);

        // do we have an item added?
        $this->assertNotEquals($shortlist->ShortListItems()->Count(), 0);
        // what about the reverse side?
        $this->assertEquals($item->ShortList()->ID, $shortlist->ID);
    }

    /*
    |--------------------------------------------------------------------------
    | Remove item from shortlist DBO.
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | Check to see that the generated URL contains the config URL segment
    | and the genertated random URL.
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | Test opening the shortlist controller url.
    |--------------------------------------------------------------------------
    */
    public function testShortListController()
    {
        $url = Config::inst()->get('ShortList', 'URLSegment');
        $response = $this->get($url);

        // Legit response?
        $this->assertEquals($response->getStatusCode(), 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Add via the controller.
    |--------------------------------------------------------------------------
    */
    public function testShortListControllerAdd()
    {
        $url = Config::inst()->get('ShortList', 'URLSegment');
        $list = ShortListController::getShortListSession();

        /*
        |--------------------------------------------------------------------------
        | There should not be anything in the shortlist
        |--------------------------------------------------------------------------
        */
        $this->assertEmpty($list);

        $id = $this->fixtureFactory->getId('Page', 'page1');
        $response = $this->get($url.'add/?id='.$id.'&type=Page&s='.Utilities::getSecurityToken());

        $list = ShortListController::getShortListSession();

        $this->assertNotEmpty($list);
        $this->assertEquals($list->ShortListItems()->count(), 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Remove an item from the list.
    |--------------------------------------------------------------------------
    */
    public function testShortListControllerRemove()
    {
        /*
        |--------------------------------------------------------------------------
        | First add to the shortlist.
        |--------------------------------------------------------------------------
        */
        $url = Config::inst()->get('ShortList', 'URLSegment');
        $list = ShortListController::getShortListSession();

        $this->assertEmpty($list);

        $id = $this->fixtureFactory->getId('Page', 'page1');
        $response = $this->get($url.'add/?id='.$id.'&type=Page&s='.Utilities::getSecurityToken());

        $list = ShortListController::getShortListSession();

        $this->assertNotEmpty($list);
        $this->assertEquals($list->ShortListItems()->count(), 1);

        $this->get($url.'remove/?id='.$id.'&type=Page&s='.Utilities::getSecurityToken());
        $this->assertEquals($list->ShortListItems()->count(), 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Paginate the page.
    |--------------------------------------------------------------------------
    */
    public function testShortListControllerPagination()
    {
        $url = Config::inst()->get('ShortList', 'URLSegment');
        $response = $this->get($url.'?page=1');

        $this->assertEquals($response->getStatusCode(), 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Visit the index after the shortlist has been created. We should be
    | redirected to the appropriate url - e.g. /shortlist/<uniqueurl>
    |--------------------------------------------------------------------------
    */
    public function testShortListControllerReIndex()
    {
        /*
        |--------------------------------------------------------------------------
        | First add to the shortlist.
        |--------------------------------------------------------------------------
        */
        $url = Config::inst()->get('ShortList', 'URLSegment');
        $list = ShortListController::getShortListSession();

        $this->assertEmpty($list);

        $id = $this->fixtureFactory->getId('Page', 'page1');
        $response = $this->get($url.'add/?id='.$id.'&type=Page&s='.Utilities::getSecurityToken());

        $response = $this->get($url);
    }

    public function testShortListControllerInvalidAdd()
    {
        $url = Config::inst()->get('ShortList', 'URLSegment');
        $list = ShortListController::getShortListSession();

        /*
        |--------------------------------------------------------------------------
        | There should not be anything in the shortlist
        |--------------------------------------------------------------------------
        */
        $this->assertEmpty($list);

        $id = $this->fixtureFactory->getId('Page', 'page1');
        $response = $this->get($url.'add/?id='.$id.'&type=Page');

        $this->assertEquals($response->getStatusCode(), 404);
    }
}
