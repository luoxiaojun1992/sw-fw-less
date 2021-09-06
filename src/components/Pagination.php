<?php
namespace SwFwLess\components;

/**
 * Class Pagination
 *
 * {@inheritdoc}
 *
 * Lightweight Pagination
 *
 * @package SwFwLess\components
 */
class Pagination
{
    private $total;
    private $perPageTotal;
    private $pageNum = 1;
    private $offset;
    private $currentPageTotal;
    private $pageTotal;

    /**
     * @var callable|null $render
     */
    private $render;

    /**
     * Calculate pagination params
     *
     * @return $this
     */
    public function calculate()
    {
        $this->pageTotal = intval(ceil($this->total / $this->perPageTotal));
        $this->offset = ($this->pageNum - 1) * $this->perPageTotal;
        if ($this->pageNum < $this->pageTotal) {
            $this->currentPageTotal = $this->perPageTotal;
        } elseif ($this->pageNum === $this->pageTotal) {
            $this->currentPageTotal = ($this->total % $this->perPageTotal) ?: $this->perPageTotal;
        } else {
            $this->currentPageTotal = 0;
        }
        return $this;
    }

    /**
     * Fix pagination params
     *
     * @param int $currentPageTotal
     * @return $this
     */
    public function fix($currentPageTotal)
    {
        $currentTotal = ($this->pageNum - 1) * $this->perPageTotal + $currentPageTotal;
        if ($currentTotal > $this->total) {
            if ($currentPageTotal > 0) {
                $this->total = $currentTotal;
                $this->calculate();
            }
        } elseif ($currentTotal < $this->total) {
            if ($currentPageTotal < $this->perPageTotal) {
                $this->total = $currentTotal;
                $this->calculate();
            }
        }
        return $this;
    }

    /**
     * @param int $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param int $perPageTotal
     * @return $this
     */
    public function setPerPage($perPageTotal)
    {
        $this->perPageTotal = $perPageTotal;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPerPage()
    {
        return $this->perPageTotal;
    }

    /**
     * @param $pageNum
     * @return $this
     */
    public function setPageNum($pageNum)
    {
        $this->pageNum = $pageNum;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageNum()
    {
        return $this->pageNum;
    }

    /**
     * @return int|null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int|null
     */
    public function getPageTotal()
    {
        return $this->pageTotal;
    }

    /**
     * @return mixed
     */
    public function getCurrentPageTotal()
    {
        return $this->currentPageTotal;
    }

    /**
     * @param $render
     * @return $this
     */
    public function setRender($render)
    {
        $this->render = $render;
        return $this;
    }

    /**
     * Render pagination ui
     *
     * @return mixed|null
     */
    public function render()
    {
        if (is_callable($this->render)) {
            return call_user_func_array($this->render, [
                'total' => $this->total,
                'per_page' => $this->perPageTotal,
                'page_num' => $this->pageNum,
                'page_total' => $this->pageTotal,
                'current_page_total' => $this->currentPageTotal,
                'next_page' => $this->pageNum < $this->pageTotal ? $this->pageNum + 1 : $this->pageTotal,
                'prev_page' => $this->pageNum > 1 ? $this->pageNum - 1 : 1
            ]);
        }
        return null;
    }
}
