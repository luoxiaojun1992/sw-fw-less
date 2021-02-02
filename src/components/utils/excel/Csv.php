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
     * @param bool $readable
     * @param bool $writable
     * @param bool $withBom
     * @return Csv
     * @throws \Exception
     */
    public static function createFromFilePath(
        $filePath, $readable = false, $writable = true, $withBom = false
    )
    {
        if ($readable && $writable) {
            throw new \Exception('Both writes and reads cannot be supported');
        }

        return (new static())->setReadable($readable)
            ->setWritable($writable)
            ->withBom($withBom)
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
    protected function withBom(bool $withBom)
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
    protected function refreshCsvBuffer($delimiter = ",", $enclosure = "\"", $escape = "\\")
    {
        $this->readBuffer = new \SplTempFileObject();

        for ($i = 0; $i < $this->maxReadBufferSize; ++$i) {
            if (!feof($this->readFp)) {
                $line = fgets($this->readFp);
                if ($line === false) {
                    if (feof($this->readFp)) {
                        break;
                    } else {
                        throw new \Exception('Failed to read csv line');
                    }
                }
                $fields = str_getcsv($line);
                $this->readBuffer->fputcsv($fields, $delimiter, $enclosure, $escape);
            } else {
                break;
            }
        }

        $this->readBuffer->rewind();
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function readCsvBuffer()
    {
        if (!$this->readBuffer->eof()) {
            $readBufferLine = $this->readBuffer->fgets();
            if ($readBufferLine === false) {
                throw new \Exception('Failed to get read buffer line');
            }
            if ($readBufferLine !== '') {
                return str_getcsv($readBufferLine);
            }
        }

        return null;
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
        $csvBufferLine = $this->readCsvBuffer();
        if (!is_null($csvBufferLine)) {
            return $csvBufferLine;
        }

        $this->refreshCsvBuffer($delimiter, $enclosure, $escape);

        return $this->readCsvBuffer();
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
