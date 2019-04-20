<?php

namespace SwFwLess\facades;

/**
 * Class Pagination
 *
 * @method static \SwFwLess\components\Pagination calculate()
 * @method static \SwFwLess\components\Pagination fix(int $current_page_count)
 * @method static \SwFwLess\components\Pagination setTotal(int $total)
 * @method static int getTotal()
 * @method static \SwFwLess\components\Pagination setPerPage(int $per_page)
 * @method static int getPerPage()
 * @method static \SwFwLess\components\Pagination setPageNum(int $page_num)
 * @method static int getPageNum()
 * @method static int getOffset()
 * @method static int getPageTotal()
 * @method static \SwFwLess\components\Pagination setRender(Callable $render)
 * @method static mixed render()
 * @package SwFwLess\facades
 */
class Pagination extends AbstractFacade
{
    protected static function getAccessor()
    {
        return new \SwFwLess\components\Pagination();
    }
}
