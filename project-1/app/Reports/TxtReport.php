<?php

namespace App\Reports;

abstract class TxtReport extends CsvReport
{
    /**
     * @inheritdoc
     */
    public function getFileExtension()
    {
        return 'txt';
    }

    /**
     * @inheritdoc
     */
    public function getContentType()
    {
        return 'text/plain';
    }
}
