<?php

namespace SwFwLess\components\utils;

class PKCSPadding
{
    /**
     * @param $data
     * @param int $blockSize Example: AES-128-CBC, 128 / 8 = 16, AES-256-CBC, 256 / 8 = 32
     * @return string
     */
    public static function encode($data, $blockSize)
    {
        $dataLen = strlen($data);
        $padAmount = $blockSize - ($dataLen % $blockSize);
        return str_pad($data, $padAmount, chr($padAmount));
    }

    /**
     * @param $data
     * @return bool|string
     */
    public static function decode($data)
    {
        $padChar = substr($data, -1);
        $padAmount = ord($padChar);
        return substr($data, 0, strlen($data) - $padAmount);
    }
}
