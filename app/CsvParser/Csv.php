<?php

namespace App\CsvParser;

class Csv
{
    protected $headers = [];

    public $rows = [];

    public function __construct($headers, $rows)
    {
        $this->headers = $headers;

        // if (is_array($rows)) {
        //     $rows = new ArrayIterator($rows);
        // }

        $this->rows = $rows;
    }

    public function headers()
    {
        return $this->headers;
    }

    public function rows()
    {
        reset($this->rows);
        return $this->rows;
    }

    public function count()
    {
        return count($this->rows);
    }

    public function getRowByNum($index)
    {
        return array_key_exists($index, $this->rows) ? $this->createRow($this->headers, $this->rows[$index]) : null;
    }

    public function getRow($index = null)
    {
        // get current position. reset if null
        $key = key($this->rows);
        if ($key === null) {
            reset($this->rows);
            return false;
        }

        $row = current($this->rows);
        next($this->rows);

        $row = $this->createRow($this->headers, $row);
        return [$key, $row];
    }

    protected function createRow($headers, $data)
    {
        if ($data instanceof Row) {
            $data = $data->getArrayCopy();
        }

        return new Row($headers, $data);
    }

    public function transform($transformer)
    {
        $transformed = [];
        $newHeaders = $this->headers;

        foreach ($this->rows as $index => $row) {
            $row = $this->createRow($newHeaders, $row);
            $row = call_user_func_array($transformer, [$row, $index, $this]);

            if (!$row) {
                throw new \Exception("Transformer should return a Row object or an array");
            }

            $row = ($row instanceof Row) ? $row->getArrayCopy() : $row;
            $transformed[$index] = $row;
            $newHeaders = array_keys($row);
        }

        return new static($newHeaders, $transformed);
    }

    /**
     * Filter rows
     *
     *  Provided filter function should return true to keep row
     *
     * @param  callable $filter
     * @param  array $rows
     *
     * @return array
     */
    public function filter($filter)
    {
        $filtered = [];

        while (list($index, $row) = $this->getRow()) {
            if (call_user_func_array($filter, [$row, $index, $this]) == true) {
                $filtered[$index] = $row;
            }
        }

        return new static($this->headers, $filtered);
    }

    public function map(Mapper $mapper)
    {
        $mapped = [];
        $total = count($this->rows);
        $i = 1;

        $row = $this->getRowByNum(0);
        $mapper->check($row);

        while (list($index, $row) = $this->getRow()) {
            $mapped[] = $mapper->map($row, $index);
            // echo "Rows mapped: $i/$total\r";
            $i++;
        }
        // echo "\n";

        return new static($mapper->headers(), $mapped);
    }

    public function each($callback)
    {
        $results = [];

        foreach ($this->rows as $index => $row) {
            $row = $this->createRow($this->headers, $row);
            $results[$index] = call_user_func_array($callback, [$row, $index, $this]);
        }

        return $results;
    }

    // @todo refactor search to "find"
    public function search($value, $column = null)
    {
        foreach ($this->rows as $row) {
            if ($row[$column] === $value) {
                return $row;
            }
        }

        return null;
    }

    // @todo refactor search to "find"
    public function search2($value, $column = null)
    {
        $matches = [];

        foreach ($this->rows as $row) {
            if ($row[$column] === $value) {
                $matches[] = $row;
            }
        }

        return $matches;
    }

    public function toArray()
    {
        $rows = [];

        while (list($key, $row) = $this->getRow()) {
            $rows[$key] = $row->toArray();
        }

        return $rows;
    }
}
