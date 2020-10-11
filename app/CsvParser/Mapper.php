<?php

namespace App\CsvParser;

class Mapper
{
    protected $mapping = [];

    protected $headers = [];

    protected $unmapped = [];

    protected $verbosity = 0;

    public function __construct(array $headers, array $mapping = [])
    {
        $this->headers = $headers;
        $this->mapping = $mapping;
    }

    public function headers()
    {
        return $this->headers;
    }

    public function add($column, $callable = null)
    {
        if (is_array($column)) {
            foreach ($column as $c => $v) {
                $this->add($c, $v);
            }

            return $this;
        }

        if (!in_array($column, $this->headers)) {
            trigger_error("Mapping column \"$column\" not in specified headers", E_USER_WARNING);
            return $this;
        }

        $this->mapping[$column] = $callable;
        return $this;
    }

    public function getUnmappedColumns()
    {
        return $this->unmapped;
    }

    protected function addUnmappedColumn($column, $index = 0)
    {
        if (!in_array($column, $this->unmapped)) {
            trigger_error("Row #{$index}: Column {$column} is unmapped", E_USER_WARNING);
            $this->unmapped[] = $column;
        }
    }

    public function setVerbose($level = 0)
    {
        $this->verbosity = $level;
        return $this;
    }

    protected function log($string, $verbosity = 1)
    {
        if ($this->verbosity >= $verbosity) {
            // echo "$string\n";
        }
    }

    public function check($row, $index = null)
    {
        $new = [];
        $default = "__DEFAULT__";

        foreach ($this->headers as $header) {
            $this->log("\n-- $header --", 1);
            $new[$header] = $default;

            if (isset($row[$header])) {
                $this->log("{$header} column mapped to {$header} (automap row value w/ similar header)", 1);
                $new[$header] = $row[$header];
            }

            if (isset($this->mapping[$header])) {
                $callable = $this->mapping[$header];

                if (is_scalar($callable) == true) {
                    if (array_key_exists($callable, $row) == false) {
                        $this->log("{$header} column mapped to {$callable} (mapping value)", 1);
                        $new[$header] = $callable;
                    }

                    if (array_key_exists($callable, $row) && isset($row[$callable])) {
                        $this->log("{$header} column mapped to {$callable} (row value)", 1);
                        $new[$header] = $row[$callable];
                    }
                }

                if (is_callable($callable) == true) {
                    $this->log("{$header} column mapped to a callable mapping result", 1);
                    $new[$header] = "__CALLABLE__";
                }
            }

            if ($new[$header] === $default) {
                $this->addUnmappedColumn($header);
                $new[$header] = "";
            }
        }
    }

    public function map($row, $index = null)
    {
        $new = [];
        $default = "__DEFAULT__";

        // automap columns with similar values
        foreach ($this->headers as $header) {
            $new[$header] = $default;

            if (isset($row[$header])) {
                $new[$header] = $row[$header];
                // echo "automap: $header\n";
            }

            if (isset($this->mapping[$header])) {
                $callable = $this->mapping[$header];

                if (is_scalar($callable) == true) {
                    if (array_key_exists($callable, $row) == false) {
                        $new[$header] = $callable;
                        // echo "plain value: $header -> '$callable'\n";
                    }

                    if (array_key_exists($callable, $row) && isset($row[$callable])) {
                        $new[$header] = $row[$callable];
                        // echo "row value: $header -> $callable '{$row[$callable]}'\n";
                    }
                }

                if (is_callable($callable) == true) {
                    $new[$header] = call_user_func_array($callable, [$row, $index]);
                    // echo "callable value: $header -> '{$new[$header]}'\n";
                }
            }

            if ($new[$header] === $default) {
                // $this->addUnmappedColumn($header, $index);
                $new[$header] = "";
            }
        }

        return $new;
    }
}
