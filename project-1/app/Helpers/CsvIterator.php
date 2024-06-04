<?php

namespace App\Helpers;

class CsvIterator implements \Iterator
{
    /**
     * @var array
     */
    protected $headers = null;

    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var array
     */
    protected $line = null;

    /**
     * @var bool
     */
    protected $hasHeaders;

    /**
     * @var int
     */
    protected $index = 0;

    /**
     * @param string $path
     * @param bool $hasHeaders
     */
    public function __construct($path, $hasHeaders = false)
    {
        $this->resource = @fopen($path, 'r');

        if (! is_resource($this->resource)) {
            throw new \Exception('File "'. $path .'" does not exist');
        }

        $this->hasHeaders = $hasHeaders;
    }

    /**
     * Destructor
     */
    function __destruct()
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
    }

    /**
     * @inheritdoc
     */
    public function rewind(): void
    {
        rewind($this->resource);

        if ($this->hasHeaders && ! feof($this->resource)) {
            $headers = fgetcsv($this->resource);

            if ($this->headers === null) {
                $this->headers = array_map('strtoupper', $headers);
            }
        }

        $this->index = 0;
        $this->readLine();
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->line;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->index;
    }

    /**
     * @inheritdoc
     */
    public function next(): void
    {
        $this->readLine();
        $this->index++;
    }

    /**
     * @inheritdoc
     */
    public function valid(): bool
    {
        return $this->line !== null;
    }

    /**
     * Get CSV headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set custom headers
     *
     * @param array $headers
     * @return $this
     */
    public function useHeaders(array $headers)
    {
        $this->headers = array_map('strtoupper', $headers);

        return $this;
    }

    /**
     * Read next line
     */
    protected function readLine()
    {
        while (true) {
            if (feof($this->resource)) {
                $this->line = null;
                break;
            }

            $data = fgetcsv($this->resource);

            if ($data === false) {
                $this->line = null;
                break;
            }

            $this->line = $this->headers !== null
                ? array_combine($this->headers, $data)
                : $data;

            break;
        }
    }
}
