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

    protected $maxReadBufferSize = 20;

    protected $maxWriteBufferSize = 20;

    protected $filePath;

    protected $readFp;

    protected $writeFp;

    protected $readable = false;

    protected $writable = true;

    protected $withBom = false;

    /**
     * @param $filePath
     * @param false $readable
     * @param bool $writable
     * @return Csv
     * @throws \Exception
     */
    public static function createFromFilePath($filePath, $readable = false, $writable = true)
    {
        return (new static())->setReadable($readable)
            ->setWritable($writable)
            ->setFile($filePath);
    }

    /**
     * @param int $maxReadBufferSize
     * @return $this
     */
    public function setMaxReadBufferSize(int $maxReadBufferSize)
    {
        $this->maxReadBufferSize = $maxReadBufferSize;
        return $this;
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
     * @param bool $readable
     * @return $this
     */
    protected function setReadable(bool $readable)
    {
        $this->readable = $readable;
        return $this;
    }

    /**
     * @param bool $writable
     * @return $this
     */
    protected function setWritable(bool $writable)
    {
        $this->writable = $writable;
        return $this;
    }

    /**
     * @param $filePath
     * @return $this
     * @throws \Exception
     */
    public function setFile($filePath)
    {
        if ($this->writable) {
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
        }
        if ($this->readable) {
            $this->readBuffer = new \SplTempFileObject();
            $this->filePath = $filePath;
            $this->readFp = fopen($filePath, 'r');
            if ($this->readFp === false) {
                throw new \Exception('Failed to open file [' . $filePath . ']');
            }
            if ($this->withBom) {
                $bom = fread($this->readFp, strlen(static::BOM));
                if ($bom === false) {
                    throw new \Exception('Failed to skip bom header');
                }
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
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @throws \Exception
     */
    protected function readCsvBuffer($delimiter = ",", $enclosure = "\"", $escape = "\\")
    {
        $this->readBuffer->rewind();
        if (!$this->readBuffer->eof()) {
            $this->readBuffer = new \SplTempFileObject();
        }

        for ($i = 0; $i < $this->maxReadBufferSize; ++$i) {
            if (!feof($this->readFp)) {
                $fields = fgetcsv($this->readFp, 0, $delimiter, $enclosure, $escape);
                if (is_null($fields) || ($fields === false)) {
                    throw new \Exception('Failed to read csv buffer');
                }
                $this->readBuffer->fputcsv($fields, $delimiter, $enclosure, $escape);
            }
        }

        $this->readBuffer->rewind();
    }

    /**
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return array
     * @throws \Exception
     */
    public function getCsv($delimiter = ",", $enclosure = "\"", $escape = "\\")
    {
        if (!$this->readBuffer->eof()) {
            return $this->readBuffer->fgetcsv($delimiter, $enclosure, $escape);
        }

        $this->flush();
        $this->readCsvBuffer($delimiter, $enclosure, $escape);

        if (!$this->readBuffer->eof()) {
            return $this->readBuffer->fgetcsv($delimiter, $enclosure, $escape);
        }

        return [];
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function flush()
    {
        if ($this->writeBufferSize <= 0) {
            return $this;
        }

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
        if (!is_null($this->readFp)) {
            $closeReadFpRes = fclose($this->readFp);
            if ($closeReadFpRes === false) {
                throw new \Exception('Failed to close file');
            }
            $this->readFp = null;
        }

        if (!is_null($this->writeFp)) {
            $closeWriteFpRes = fclose($this->writeFp);
            if ($closeWriteFpRes === false) {
                throw new \Exception('Failed to close file');
            }
            $this->writeFp = null;
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        $this->flush()->closeFile();
    }
}
