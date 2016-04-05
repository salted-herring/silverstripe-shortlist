<?php

class ShortListController extends Page_Controller
{
    private static $allowed_actions = array(
        'renderList',
        'addOrRemove'
    );

    private static $url_handlers = array(
        'add'       => 'addOrRemove',
        'remove'    => 'addOrRemove',
        '$URL!'     => 'renderList',
    );

    private static $extensions = array(
        'ShortListPaginationExtension'
    );

    public function init()
    {
        parent::init();

        Session::start();

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
        if (($shortlist = $this->getSessionShortList())) {
            return $this->redirect(Config::inst()->get('ShortList', 'URLSegment') . $shortlist->URL);
        } else {
            /*
if (!ShortList::isBrowser()) {
                return $this->httpError(404);
            }
*/

            $shortlist = $this->getSessionShortList();

            if (!$shortlist || !$shortlist->exists()) {
                $shortlist = new ShortList();
                $shortlist->write();
            }
        }

        // render with empty template.
        return $this->renderWith(array('Page', 'ShortList_empty'));
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

    public function renderList($request)
    {
        $shortlist = DataObject::get_one('ShortList', $filter = array('URL' => $request->param('URL')));

        if (is_null(session_id()) ||
            !$request->param('URL') ||
            !$shortlist ||
            !$shortlist->exists()
        ) {
            return $this->httpError(404);
        }

        $link = false;
        $count = 0;

        if ($shortlist && $shortlist->exists()) {
            $link = $shortlist->Link();
            $count = $shortlist->ShortListItems()->Count();
        }

        return $this->customise(array(
            'ShortlistURL' => $link,
            'ShortlistCount' => $count
        ))->renderWith(
            array('ShortList', 'Page')
        );
    }

    /**
     * Add an item to the shortlist.
     *
     * @param ID id of the object to add.
     * @param type classname of the item to remove
     * @param session session id.
     *
     * */
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

        // check whether the itme is already in the list
        // before attempting to add it.
        $existing = $shortlist->ShortListItems()->filterAny(
            array('ItemID' => $ID, 'ItemType' => $type)
        );

        if ($existing->count() == 1) {
            return true;
        }

        $shortlistItem = new ShortListItem();
        $shortlistItem->ShortListID = $shortlist->ID;
        $shortlistItem->ItemID = $ID;
        $shortlistItem->ItemType = $type;

        $shortlist->ShortListItems()->add($shortlistItem);
        $shortlist->write();

        return true;
    }

    public function addOrRemove($request)
    {
        if (is_null(session_id()) ||
            !$request->getVar('id') ||
            !$request->getVar('type') ||
            !$request->getVar('s') ||
            $request->getVar('s') != session_id()
        ) {
            return $this->httpError(404);
        }

        if (strpos($request->getURL(), 'remove') !== false) {
            $status = $this->removeFromShortList(
                $ID = $request->getVar('id'),
                $type = $request->getVar('type'),
                $session = $request->getVar('s')
            );
        } else {
            $status = $this->addToShortList(
                $ID = $request->getVar('id'),
                $type = $request->getVar('type'),
                $session = $request->getVar('s')
            );
        }


        if ($request->isAjax()) {
            $shortlist = $this->getSessionShortList();
            $url = false;

            if ($shortlist && $shortlist->exists()) {
                $url = $shortlist->Link();
            }

            return json_encode(array(
                'status' => $status,
                'count' => $this->shortListCount($session),
                'url' => $url
            ));
        }

        if (array_key_exists('output', $request->getVars())) {
            return true;
        }

        return $this->redirectBack();
    }

    /**
     * Remove an item from the shortlist.
     *
     * @param ID id of the object to remove.
     * @param type classname of the item to remove
     * @param session session id.
     *
     * */
    private function removeFromShortList($ID = false, $type = null, $session = false)
    {
        $shortlist = $this->getSessionShortList();

        if (!$shortlist || !$shortlist->exists()) {
            return true;
        }

        $item = DataObject::get_one('ShortListItem', $filter = "ItemType = '" . $type . "' AND ItemID = " . $ID);

        if ($item && $item->exists()) {
            $item->delete();
        } else {
            return false;
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
        if (is_null(session_id()) || !$session || $session != session_id()) {
            return false;
        }

        $shortlist = $this->getSessionShortList();

        if (!$shortlist || !$shortlist->exists()) {
            return 0;
        }

        return $shortlist->Items()->count();
    }

    private function getSessionShortList()
    {
        return DataObject::get_one('ShortList', $filter = array('SessionID' => session_id()));
    }
}
