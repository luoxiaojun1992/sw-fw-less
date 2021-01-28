<?php

namespace SwFwLess\components\utils\excel;

class Csv
{
    const BOM = "\u{FEFF}";

    /** @var \SplTempFileObject */
    protected $buffer;

    protected $bufferSize = 0;

    protected $maxBufferSize = 20;

    protected $filePath;

    protected $fp;

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
     * @param int $maxBufferSize
     * @return $this
     */
    public function setMaxBufferSize(int $maxBufferSize)
    {
        $this->maxBufferSize = $maxBufferSize;
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
        $this->buffer = new \SplTempFileObject();
        $this->filePath = $filePath;
        $this->fp = fopen($filePath, 'w');
        if ($this->fp === false) {
            throw new \Exception('Failed to open file [' . $filePath . ']');
        }
        if ($this->withBom) {
            $writeBomRes = fwrite($this->fp, static::BOM);
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
        $putRes = $this->buffer->fputcsv(
            $fields, $delimiter, $enclosure, $escape
        );
        if ($putRes === false) {
            return $putRes;
        }

        ++$this->bufferSize;

        if ($this->bufferSize >= $this->maxBufferSize) {
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
        $this->buffer->rewind();
        while (!$this->buffer->eof()) {
            $line = $this->buffer->fgets();
            if ($line === false) {
                throw new \Exception('Failed to get buffer line');
            }
            $bufferContent .= $line;
        }
        if ($bufferContent) {
            $flushRes = fwrite($this->fp, $bufferContent);
            if ($flushRes === false) {
                throw new \Exception('Failed to write buffer');
            }
            $this->buffer = new \SplTempFileObject();
            $this->bufferSize = 0;
        }
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function closeFile()
    {
        $res = fclose($this->fp);
        if ($res === false) {
            throw new \Exception('Failed to close file');
        }
        $this->fp = null;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        if (is_null($this->fp)) {
            return;
        }

        $this->flush()->closeFile();
    }
}
