<?php
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class ShortListController extends Page_Controller
{
    private static $allowed_actions = array(
        'renderList',
        'performAction'
    );

    private static $url_handlers = array(
        'add'       => 'performAction',
        'remove'    => 'performAction',
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
            return $this->redirect(Config::inst()->get('ShortList', 'URLSegment').$shortlist->URL);
        } else {
            $CrawlerDetect = new CrawlerDetect;

            // Check the user agent of the current 'visitor'
            if ($CrawlerDetect->isCrawler()) {
                return $this->httpError(403);
            }

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

        if (is_null(self::getSecurityToken()) ||
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

    public function performAction($request)
    {
        if (is_null(self::getSecurityToken()) ||
            !$request->getVar('id') ||
            !$request->getVar('type') ||
            !$request->getVar('s') ||
            $request->getVar('s') != self::getSecurityToken()
        ) {
            return $this->httpError(404);
        }

        $matches = array();
        preg_match('/remove|add/', $request->getURL(), $matches);

        switch ($matches[0]) {
            case 'add':
                $action = new AddToshortlistAction();
                $status = $action->performAction(
                    $shortlist = $this->getSessionShortList(),
                    $ID = $request->getVar('id'),
                    $type = $request->getVar('type'),
                    $session = $request->getVar('s')
                );
                break;
            case 'remove':
                $action = new RemoveFromshortlistAction();
                $status = $action->performAction(
                    $shortlist = $this->getSessionShortList(),
                    $ID = $request->getVar('id'),
                    $type = $request->getVar('type'),
                    $session = $request->getVar('s')
                );
                break;
        }

        if ($request->isAjax()) {
            $shortlist = $this->getSessionShortList();
            $url = false;

            if ($shortlist && $shortlist->exists()) {
                $url = $shortlist->Link();
            }

            return json_encode(array(
                'count' => $this->shortListCount($session),
                'url' => $url
            ));
        }

        if (array_key_exists('output', $request->getVars())) {
            return $status;
        }

        return $this->redirectBack();
    }


    /**
     * Get the number of items in the current short list.
     *
     * @param session The session to check & find a shortlist for.
     * @return mixed false if no session exists - else the number of items in the shortlist.
     * */
    public function shortListCount($session = false)
    {
        if (is_null(self::getSecurityToken()) || !$session || $session != self::getSecurityToken()) {
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
        return DataObject::get_one('ShortList',
            $filter = array('SessionID' => self::getSecurityToken()),
            $cache = false
        );
    }

    public static function getShortListSession()
    {
        return DataObject::get_one('ShortList', $filter = array('SessionID' => self::getSecurityToken()));
    }

    /**
     * Get the token to use to add/remove from shortlist.
     * */
    public static function getSecurityToken()
    {
        return Utilities::getSecurityToken();
    }
}
