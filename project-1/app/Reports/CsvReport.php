<?php

namespace App\Reports;

abstract class CsvReport extends BaseReport
{
    /**
     * Get report header
     * 
     * @return array
     */
    abstract protected function getColumns();

    /**
     * Get report header
     * 
     * @return array
     */
    protected function getHeaderLines()
    {
        return [];
    }

    /**
     * Get report footer
     * 
     * @return array
     */
    protected function getFooterLines()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getOutput()
    {
        $file = fopen('php://memory', 'r+');
        $columns = $this->getColumns();
        $keys = array_keys($columns);

        fputcsv($file, array_values($columns));

        foreach ($this->getHeaderLines() as $line) {
            fputcsv($file, $this->makeCsvRow($line, $keys));
        }

        foreach ($this->getDataProdider() as $item) {
            $item = $this->transformItem($item);
            $key = key($item);

            if (is_numeric($key) && is_array($item[$key])) {
                foreach ($item as $subItem) {
                    fputcsv($file, $this->makeCsvRow($subItem, $keys));
                }
            } else {
                fputcsv($file, $this->makeCsvRow($item, $keys));
            }
        }

        foreach ($this->getFooterLines() as $line) {
            fputcsv($file, $this->makeCsvRow($line, $keys));
        }

        rewind($file);
        $content = '';
        while (! feof($file)) {
            $content .= fgets($file);
        }
        fclose($file);
        
        return $content;
    }

    /**
     * Make csv row
     * 
     * @param array $data
     * @param array $keys
     * @return array
     */
    protected function makeCsvRow(&$data, &$keys)
    {
        return array_map(function($key) use ($data) {
            return $data[$key] ?? '';
        }, $keys);
    }

    /**
     * Transform single report item
     * 
     * @param mixed $item
     * @return mixed
     */
    protected function transformItem($item)
    {
        return $item;
    }

    /**
     * @inheritdoc
     */
    public function getFileExtension()
    {
        return 'csv';
    }

    /**
     * @inheritdoc
     */
    public function getContentType()
    {
        return 'text/csv';
    }
}