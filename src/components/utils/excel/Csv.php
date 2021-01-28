<?php

namespace SwFwLess\components\utils\excel;

class Csv
{
    const BOM = "\u{FEFF}";

    /** @var \SplTempFileObject */
    protected $readBuffer;

    /** @var \SplTempFileObject */
    protected $writeBuffer;

    protected $writeBufferSize = 0;

    protected $maxWriteBufferSize = 20;

    protected $filePath;

    protected $writeFp;

    protected $withBom = false;

    /**
     * @param $filePath
     * @return Csv
     * @throws \Exception
     */
    public static function createFromFilePath($filePath)
    {
        return (new static())->setFile($filePath);
    }

    /**
     * @param int $maxWriteBufferSize
     * @return $this
     */
    public function setMaxWriteBufferSize(int $maxWriteBufferSize)
    {
        $this->maxWriteBufferSize = $maxWriteBufferSize;
        return $this;
    }

    /**
     * @param bool $withBom
     * @return $this
     */
    public function withBom(bool $withBom)
    {
        $this->withBom = $withBom;
        return $this;
    }

    /**
     * @param $filePath
     * @return $this
     * @throws \Exception
     */
    public function setFile($filePath)
    {
        $this->writeBuffer = new \SplTempFileObject();
        $this->filePath = $filePath;
        $this->writeFp = fopen($filePath, 'w');
        if ($this->writeFp === false) {
            throw new \Exception('Failed to open file [' . $filePath . ']');
        }
        if ($this->withBom) {
            $writeBomRes = fwrite($this->writeFp, static::BOM);
            if ($writeBomRes === false) {
                throw new \Exception('Failed to write bom header');
            }
        }
        return $this;
    }

    /**
     * @param array $fields
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return false|int
     * @throws \Exception
     */
    public function putCsv(array $fields, $delimiter = ',' , $enclosure = '"', $escape = "\\")
    {
        $putRes = $this->writeBuffer->fputcsv(
            $fields, $delimiter, $enclosure, $escape
        );
        if ($putRes === false) {
            return $putRes;
        }

        ++$this->writeBufferSize;

        if ($this->writeBufferSize >= $this->maxWriteBufferSize) {
            $this->flush();
        }

        return $putRes;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function flush()
    {
        $bufferContent = '';
        $this->writeBuffer->rewind();
        while (!$this->writeBuffer->eof()) {
            $line = $this->writeBuffer->fgets();
            if ($line === false) {
                throw new \Exception('Failed to get buffer line');
            }
            $bufferContent .= $line;
        }
        if ($bufferContent) {
            $flushRes = fwrite($this->writeFp, $bufferContent);
            if ($flushRes === false) {
                throw new \Exception('Failed to write buffer');
            }
            $this->writeBuffer = new \SplTempFileObject();
            $this->writeBufferSize = 0;
        }
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function closeFile()
    {
        $res = fclose($this->writeFp);
        if ($res === false) {
            throw new \Exception('Failed to close file');
        }
        $this->writeFp = null;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        if (is_null($this->writeFp)) {
            return;
        }

        $this->flush()->closeFile();
    }
}
