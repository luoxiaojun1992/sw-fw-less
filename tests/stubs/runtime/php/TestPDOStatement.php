<?php

class TestPDOStatement
{
    public function execute ($input_parameters = null)
    {
        return true;
    }

    public function rowCount ()
    {
        return 1;
    }

    public function fetch ($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        return ['id' => 1, 'name' => 'Foo'];
    }

    public function fetchAll ($fetch_style = null, $fetch_argument = null, array $ctor_args = array())
    {
        return [
            ['id' => 1, 'name' => 'Foo'],
            ['id' => 2, 'name' => 'Bar'],
        ];
    }
}
