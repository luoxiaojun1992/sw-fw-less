<?php

namespace App\facades;

/**
 * Class Pagination
 *
 * @method static \App\components\Pagination calculate()
 * @method static \App\components\Pagination fix(int $current_page_count)
 * @method static \App\components\Pagination setTotal(int $total)
 * @method static int getTotal()
 * @method static \App\components\Pagination setPerPage(int $per_page)
 * @method static int getPerPage()
 * @method static \App\components\Pagination setPageNum(int $page_num)
 * @method static int getPageNum()
 * @method static int getOffset()
 * @method static int getPageTotal()
 * @method static \App\components\Pagination setRender(Callable $render)
 * @method static mixed render()
 * @package App\facades
 */
class Pagination extends AbstractFacade
{
    protected static function getAccessor()
    {
        return new \App\components\Pagination();
    }
}
