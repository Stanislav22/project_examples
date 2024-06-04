<?php

namespace App\Reports;

use Illuminate\Support\Str;
use Carbon\Carbon;

abstract class BaseReport implements \App\Contracts\Report
{
    /**
     * @var \Illuminate\Support\LazyCollection
     */
    protected $dataProvider;

    /**
     * Get data provider
     * 
     * @return \Illuminate\Support\LazyCollection
     */
    protected function getDataProdider()
    {
        if ($this->dataProvider === null) {
            $this->dataProvider = $this->createDataProvider();
        }

        return $this->dataProvider;
    }

    /**
     * Set data provider
     * 
     * @param \Illuminate\Support\LazyCollection $dataProvider
     * @return $this
     */
    public function usingDataProvider($dataProvider)
    {
        $this->dataProvider = $dataProvider;

        return $this;
    }

    /**
     * Create data provider
     * 
     * @return \Illuminate\Support\LazyCollection
     */
    protected function createDataProvider()
    {
        throw new \Exception('Method createDataProvider() must be implemented');
    }

    /**
     * @inheritdoc
     */
    public function getFilename()
    {
        return Str::kebab(class_basename($this)) 
            . $this->getFileSuffix() 
            .'.'. $this->getFileExtension();
    }

    /**
     * Get file suffix
     * 
     * @return string
     */
    protected function getFileSuffix()
    {
        return '-'. Carbon::now()->format('Y-m-d');
    }

    /**
     * Get file extension
     * 
     * @return string
     */
    abstract protected function getFileExtension();

    /**
     * @inheritdoc
     */
    abstract public function getContentType();
}