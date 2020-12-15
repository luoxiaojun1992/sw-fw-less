<?php

class TestPDOStatement
{
    protected $mockData = [];

    /**
     * @param array $mockData
     * @return $this
     */
    public function setMockData(array $mockData)
    {
        $this->mockData = $mockData;
        return $this;
    }

    public function execute ($input_parameters = null)
    {
        return true;
    }

    public function rowCount ()
    {
        return count($this->mockData);
    }

    public function fetch ($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        return $this->mockData[0];
    }

    public function fetchAll ($fetch_style = null, $fetch_argument = null, array $ctor_args = array())
    {
        return $this->mockData;
    }
}
