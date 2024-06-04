<?php

namespace App\Reports;

abstract class JsonReport extends BaseReport
{
    /**
     * Get metadata for report
     * 
     * @return array
     */
    protected function getMeta()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getOutput()
    {
        $response = '{';
        $response .= $this->buildData() . ',';
        $response .= $this->buildMeta();
        $response .= '}';

        return $response;
    }

    /**
     * Build report meta 
     * 
     * @return string
     */
    protected function buildMeta()
    {
        return '"meta": ' . json_encode($this->getMeta());
    }

    /**
     * Build report data
     * 
     * @return string
     */
    protected function buildData()
    {
        $data = '"data": [';
        $separator = '';
    
        foreach ($this->getDataProdider() as $item) {
            $data .= $separator . json_encode($this->transformItem($item));
            $separator = ',';
        }

        $data .= ']';

        return $data;
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
        return 'json';
    }

    /**
     * @inheritdoc
     */
    public function getContentType()
    {
        return 'application/json';
    }
}