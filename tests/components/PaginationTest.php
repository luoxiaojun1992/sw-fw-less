<?php

namespace SwFwLessTest\components;

use PHPUnit\Framework\TestCase;
use SwFwLess\components\Pagination;

class PaginationTest extends TestCase
{
    public function testCalculate()
    {
        $pagination = (new Pagination())->setPageNum(1)
            ->setPerPage(10)
            ->setTotal(21)
            ->calculate();
        $this->assertEquals(3, $pagination->getPageTotal());
        $this->assertEquals(0, $pagination->getOffset());
        $this->assertEquals(10, $pagination->getCurrentPageTotal());

        $pagination = (new Pagination())->setPageNum(2)
            ->setPerPage(10)
            ->setTotal(21)
            ->calculate();
        $this->assertEquals(3, $pagination->getPageTotal());
        $this->assertEquals(10, $pagination->getOffset());
        $this->assertEquals(10, $pagination->getCurrentPageTotal());

        $pagination = (new Pagination())->setPageNum(3)
            ->setPerPage(10)
            ->setTotal(21)
            ->calculate();
        $this->assertEquals(3, $pagination->getPageTotal());
        $this->assertEquals(20, $pagination->getOffset());
        $this->assertEquals(1, $pagination->getCurrentPageTotal());

        $pagination = (new Pagination())->setPageNum(4)
            ->setPerPage(10)
            ->setTotal(21)
            ->calculate();
        $this->assertEquals(3, $pagination->getPageTotal());
        $this->assertEquals(30, $pagination->getOffset());
        $this->assertEquals(0, $pagination->getCurrentPageTotal());
    }
}
