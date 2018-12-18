<?php
namespace App\components;

/**
 * Class Pagination
 *
 * {@inheritdoc}
 *
 * Lightweight Pagination
 *
 * @package App\components
 */
class Pagination
{
    private $total;
    private $per_page;
    private $page_num = 1;
    private $offset;
    private $page_total;

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
        $this->page_total = intval(ceil($this->total / $this->per_page));
        $this->offset = ($this->page_num - 1) * $this->per_page;
        return $this;
    }

    /**
     * Fix pagination params
     *
     * @param int $current_page_count
     * @return $this
     */
    public function fix($current_page_count)
    {
        $current_total = ($this->page_num - 1) * $this->per_page + $current_page_count;
        if ($current_total > $this->total) {
            if ($current_page_count > 0) {
                $this->total = $current_total;
                $this->calculate();
            }
        } elseif ($current_total < $this->total) {
            if ($current_page_count < $this->per_page) {
                $this->total = $current_total;
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
     * @param int $per_page
     * @return $this
     */
    public function setPerPage($per_page)
    {
        $this->per_page = $per_page;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPerPage()
    {
        return $this->per_page;
    }

    /**
     * @param $page_num
     * @return $this
     */
    public function setPageNum($page_num)
    {
        $this->page_num = $page_num;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageNum()
    {
        return $this->page_num;
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
        return $this->page_total;
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
                'per_page' => $this->per_page,
                'page_num' => $this->page_num,
                'page_total' => $this->page_total,
                'next_page' => $this->page_num < $this->page_total ? $this->page_num + 1 : $this->page_total,
                'prev_page' => $this->page_num > 1 ? $this->page_num - 1 : 1
            ]);
        }
        return null;
    }
}
