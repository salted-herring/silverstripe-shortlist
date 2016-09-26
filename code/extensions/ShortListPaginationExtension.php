<?php
/*
 * @file ShortListPaginationExtension.php
 *
 * Provides Pagination for shortlist controller
 */
class ShortListPaginationExtension extends DataExtension {
    /**
     * Get a paginated list of the shortlist items.
     *
     * @return mixed the paginated list of items, or false if the list cannot be found.
     * */
    public function paginatedItems()
    {
        if (!$this->owner->getRequest()->param('URL') || !ShortList::isBrowser()) {
            return false;
        }

        $items = false;
        $list = DataObject::get_one('ShortList', $filter = array('URL' => $this->owner->getRequest()->param('URL')));

        if ($list) {
            $items = $list->ShortListItems();
        }

        $this->owner->list = new PaginatedList($items, $this->owner->getRequest());
        $this->owner->list->setPageLength(Config::inst()->get('ShortList', 'PaginationCount'));
        $this->owner->list->setPaginationGetVar('page');

        if ($this->owner->currentPage) {
            $this->owner->list->setCurrentPage($this->owner->currentPage);
        }

        return $this->owner->list;
    }

    public function nextPage()
    {
        if ($this->owner->list->CurrentPage() < $this->owner->list->TotalPages()) {
            return '?page='.($this->owner->list->CurrentPage() + 1);
        }

        return false;
    }

    public function prevPage()
    {
        if ($this->owner->list->CurrentPage() > 1) {
            return '?page='.($this->owner->list->CurrentPage() - 1);
        }

        return false;
    }
}
