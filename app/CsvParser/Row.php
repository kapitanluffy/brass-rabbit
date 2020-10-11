<?php

namespace App\CsvParser;

class Row extends \ArrayObject
{
    protected $headers;

    protected $data;

    public function __construct($headers, $data)
    {
        $this->headers = $headers;
        $this->data = $data;

        $row = $this->buildRow();
        parent::__construct($row);
    }

    protected function buildRow()
    {
        return array_merge(array_fill_keys($this->headers, null), $this->data);
    }

    public function toArray()
    {
        $copy = $this->getArrayCopy();

        foreach ($copy as $i => $v) {
            if (is_scalar($v) || $v === null) {
                continue;
            }

            $copy[$i] = serialize($v);
        }

        return $copy;
    }
}
