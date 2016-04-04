<?php

class ShortListController extends Page_Controller
{
    private static $allowed_actions = array(
        'renderList',
        'add',
        'remove'
    );

    private static $url_handlers = array(
        ''          => 'home',
        'add'       => 'add',
        'remove'    => 'remove',
        '$URL!'     => 'renderList',
    );

    public function init()
    {
        if ($this->request->getVar('page')) {
            $this->currentPage = $this->request->getVar('page');
        }

        parent::init();
    }

    public function home($request)
    {
        $this->httpError(404);
    }

    /**
     * Create a new list if necessary. If a bot, do nothing!
     * */
    public function initList()
    {
        if (!ShortList::isBrowser()) {
            $this->httpError(404);
        }
        if ($this->request->getVar('page')) {
            $this->currentPage = $this->request->getVar('page');
        }

        $shortlist = $this->getSessionShortList();

        if (!$shortlist || !$shortlist->exists()) {
            $shortlist = new ShortList();
            $shortlist->write();
        }
    }

    public function renderList($request)
    {
        if (is_null(session_id()) ||
            !$request->param('URL') ||
            DataObject::get_one('ShortList', $filter = array('URL' => $request->param('URL')))->empty() ||
            !ShortList::isBrowser()
        ) {
            $this->httpError(404);
        }

        $short = DataObject::get_one('ShortList', $filter = array('URL' => $request->param('URL')));

        return $this->customise(array(
            'ShortlistURL' => $short && $short->exists() ? $short->Link() : false,
            'ShortlistCount' => $short && $short->exists() ? $short->Items()->Count() : 0
        ))->renderWith(
            array('ShortList', 'Page')
        );
    }

    /**
     * Add an item to the shortlist.
     *
     * $request params:
     *
     * * type   - classname of the
     * * id     - id of the object to add.
     * * s      - session id.
     *
     * */
    public function add($request)
    {
        if (is_null(session_id()) ||
            !$request->getVar('id') ||
            !$request->getVar('type') ||
            !$request->getVar('s') ||
            $request->getVar('s') != session_id() ||
            !ShortList::isBrowser()) {
            $this->httpError(404);
        }

        $add = $this->addToShortList(
            $ID = $request->getVar('id'),
            $type = $request->getVar('type'),
            $session = $request->getVar('s')
        );

        if ($request->isAjax()) {
            $shortlist = $this->getSessionShortList();
	        $url = false;

	        if ($shortlist && $shortlist->exists()) {
	            $url = $shortlist->Link();
	        }

	        return json_encode(array(
	            'status' => $added,
	            'count' => $this->ShortListCount($session),
	            'url' => $url
	        ));
        }

        return $add;
    }

    public function addToShortList($ID = false, $type = null, $session = false)
    {
        if (!$ID || is_null($type) || !$session) {
            return false;
        }

        $shortlist = $this->getSessionShortList();

        if (!$shortlist || !$shortlist->exists()) {
            $shortlist = new ShortList();
            $shortlist->write();
        }

        $item = DataObject::get_by_id($type, $ID);

        if ($item && $item->exists()) {
            $shortlistItem = new ShortListItem();
            $shortlistItem->ShortListID = $shortlist->ID;
            $shortlistItem->ItemID = $item->ID;
            $shortlistItem->Type = $type;

            $shortlist->ShortListItems()->add($shortlistItem);
            $shortlist->write();
        }

        return true;
    }

    public function remove($request) {
        if (is_null(session_id()) ||
            !$request->getVar('id') ||
            !$request->getVar('type') ||
            !$request->getVar('s') ||
            $request->getVar('s') != session_id() ||
            !ShortList::isBrowser()
        ) {
            $this->httpError(404);
        }

        $remove = $this->removeFromShortList(
            $ID = $request->getVar('id'),
            $type = $request->getVar('type'),
            $session = $request->getVar('s')
        );

        if ($request->isAjax()) {
            $shortlist = $this->getSessionShortList();
	        $url = '#';

	        if ($shortlist && $shortlist->exists()) {
	            $url = $shortlist->Link();
	        }

	        return json_encode(array(
	            'status' => $remove,
	            'count' => $this->shortListCount($session),
	            'url' => $url
	        ));
        }

        return $remove;
    }

    private function removeFromShortList($ID = false, $type = null, $session = false)
    {
        $shortlist = $this->getSessionShortList();

        if (!$shortlist || !$shortlist->exists()) {
            return true;
        }

		$item = DataObject::get_one('ShortListItem', $filter = "Type = '" . $type . "' AND ItemID = " . $ID);

        if ($item && $item->exists()) {
	        $item->delete();
        }

        return true;
    }

    /**
     * Get the number of items in the current short list.
     *
     * @param session The session to check & find a shortlist for.
     * @return mixed false if no session exists - else the number of items in the shortlist.
     * */
    public function shortListCount($session = false)
    {
        if (is_null(session_id()) || !$session || $session != session_id() || !ShortList::isBrowser()) {
            return false;
        }

        $shortlist = $this->getSessionShortList();

        if (!$shortlist || !$shortlist->exists()) {
            return 0;
        }

        return $shortlist->Items()->count();
    }


    public function paginatedPages()
    {
        if (!$this->request->param('URL') || !ShortList::isBrowser()) {
            return false;
        }

        $events = false;
        $list = ShortList::get()->filter(array(
            'URL' => $this->request->param('URL')
        ));

        if ($list->first()) {
            $events = $list->first()->Events();
        }

        $this->list = new PaginatedList($events, $this->request);
        $this->list->setPageLength(SiteConfig::current_site_config()->PaginationCount);
        $this->list->setPaginationGetVar('page');

        if ($this->currentPage) {
            $this->list->setCurrentPage($this->currentPage);
        }

        return $this->list;
    }

    public function nextPage()
    {
        if ($this->currentPage && $this->list->CurrentPage() < $this->list->TotalPages()) {
            return '?page=' . ($this->currentPage + 1);
        }

        return '?page=2';
    }

    public function prevPage()
    {
        if ($this->currentPage && $this->list->CurrentPage() > 0) {
            return '?page=' . ($this->currentPage - 1);
        }

        return '?page=1';
    }


    private function getSessionShortList() {
	    return DataObject::get_one('ShortList', $filter = array('SessionID' => session_id()));
    }
}
