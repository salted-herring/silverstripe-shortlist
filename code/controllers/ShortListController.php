<?php
namespace SaltedHerring\ShortList;

class ShortListController extends Page_Controller
{
    private static $allowed_actions = array(
        'add',
        'remove',
        'renderList'
    );

    private static $url_handlers = array(
        'add'       => 'add',
        'remove'    => 'remove',
        '$URL!'     => 'renderList',
    );

    public function init()
    {
        parent::init();

        if ($this->request->getVar('page')) {
            $this->currentPage = $this->request->getVar('page');
        }
    }

    /**
     * When landing on the homepage, if there is a shortlist for the current
     * user, redirect to the correct URL. Otherwise, 404.
     * */
    public function index($request)
    {
        if ($this->getSessionShortList()) {
            return $this->renderWith(
                array('Page', 'ShortList')
            );
        } else {
            // render with not found template.
            return $this->renderWith(array('Page', 'ShortList_empty'));
        }
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
        $shortlist = DataObject::get_one('ShortList', $filter = array('URL' => $request->param('URL')));

        if (is_null(session_id()) ||
            !$request->param('URL') ||
            !$shortlist ||
            !$shortlist->exists() ||
            !ShortList::isBrowser()
        ) {
            $this->httpError(404);
        }

        return $this->customise(array(
            'ShortlistURL' => $shortlist && $shortlist->exists() ? $shortlist->Link() : false,
            'ShortlistCount' => $shortlist && $shortlist->exists() ? $shortlist->ShortListItems()->Count() : 0
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
                'status' => $add,
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
            $shortlistItem->ItemType = $type;

            $shortlist->ShortListItems()->add($shortlistItem);
            $shortlist->write();
        }

        return true;
    }

    public function remove($request)
    {
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

        $item = DataObject::get_one('ShortListItem', $filter = "ItemType = '" . $type . "' AND ItemID = " . $ID);

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


    public function paginatedItems()
    {
        if (!$this->getRequest()->param('URL') || !ShortList::isBrowser()) {
            return false;
        }

        $items = false;
        $list = DataObject::get_one('ShortList', $filter = array('URL' => $this->getRequest()->param('URL')));

        if ($list) {
            $items = $list->ShortListItems();
        }

        $this->list = new PaginatedList($items, $this->getRequest());
        $this->list->setPageLength(Config::inst()->get('ShortList', 'PaginationCount'));
        $this->list->setPaginationGetVar('page');

        if ($this->currentPage) {
            $this->list->setCurrentPage($this->currentPage);
        }

        return $this->list;
    }

    public function nextPage()
    {
        if ($this->list->CurrentPage() < $this->list->TotalPages()) {
            return '?page=' . ($this->list->CurrentPage() + 1);
        }

        return false;
    }

    public function prevPage()
    {
        if ($this->list->CurrentPage() > 1) {
            return '?page=' . ($this->list->CurrentPage() - 1);
        }

        return false;
    }


    private function getSessionShortList()
    {
        return DataObject::get_one('ShortList', $filter = array('SessionID' => session_id()));
    }

    /**
     * Get the absolute URL of this controller.
     * */
    public function Link($action = null)
    {
        $shortlist = $this->getSessionShortList();
        $url = Config::inst()->get('ShortList', 'URLSegment');

        if ($shortlist) {
            $url .= $shortlist->URL;
        }

        return $url;
    }
}
