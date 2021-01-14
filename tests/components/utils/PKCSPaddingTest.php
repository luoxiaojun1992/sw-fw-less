<?php

class PKCSPaddingTest extends \PHPUnit\Framework\TestCase
{
    public function testCodec()
    {
        $blockSize = 16;
        $rawData = 'hello world';
        $padLength = $blockSize - strlen($rawData);
        $padChar = chr($padLength);
        $encodedData = $rawData . str_repeat($padChar, $padLength);

        $this->assertEquals(
            $encodedData,
            \SwFwLess\components\utils\PKCSPadding::encode($rawData, 16)
        );

        $this->assertEquals(
            $rawData,
            \SwFwLess\components\utils\PKCSPadding::decode($encodedData)
        );
    }
}
