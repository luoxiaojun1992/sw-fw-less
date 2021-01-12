<?php

class ChineseTest extends \PHPUnit\Framework\TestCase
{
    public function testContainsChinese()
    {
        $this->assertFalse(\SwFwLess\components\utils\language\Chinese::containsChinese('abc'));
        $this->assertFalse(\SwFwLess\components\utils\language\Chinese::containsChinese('abc123'));
        $this->assertFalse(\SwFwLess\components\utils\language\Chinese::containsChinese('456abc123'));
        $this->assertFalse(\SwFwLess\components\utils\language\Chinese::containsChinese('123'));

        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('你'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('你123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('456你123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('456你123好'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('456你123好789'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc你123'));

        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('你abc'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('def你abc'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('def你abc好'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('def你abc好ghi'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('def你们abc好'));

        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc龲123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc㐊123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc𠀫123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc𪩘123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc𫞩123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc⼃123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc⺔123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc礪123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc䕫123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc䕫123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc㇏123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc⿻123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abcㄨ123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abcㄌ123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abcㆫ123'));
        $this->assertTrue(\SwFwLess\components\utils\language\Chinese::containsChinese('abc〇123'));
    }
}
