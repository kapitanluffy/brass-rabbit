<?php

namespace App\CsvParser;

class CsvFactory
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function loadFromFile($file)
    {
        if (($fh = fopen($file, 'r+')) == false) {
            throw new \Exception("Cannot read file [{$file}]");
        }

        $delimiter = self::getDelimiter();
        $header = fgetcsv($fh, 0, $delimiter);
        $rows = [];
        $hasKey = ($key !== 0);
        $extraColumnCount = 1;

        $headerCount = array_count_values($header);
        $headerStack = array_fill_keys($header, 0);

        // fix duplicate headers
        foreach ($header as $i => $v) {
            if ($headerCount[$v] <= 1) continue;
            $header[$i] = sprintf("%s_%s", $v, $headerStack[$v]);
            $headerStack[$v]++;
        }

        $i = 0;
        $originalHeader = $header;
        self::$errored = [$header];

        while ($row = fgetcsv($fh, 0, $delimiter)) {
            if (count($header) < count($row)) {
                $headerCount = count($header);
                $rowCount = count($row);
                trigger_error("Line #{$i}: Header count is less than row property count. Header: {$headerCount}, Row: {$rowCount}", E_USER_WARNING);
            }

            while (count($header) < count($row)) {
                $header[] = "extra_column_{$extraColumnCount}";
                $extraColumnCount++;
            }

            // @note $header is already modified
            if (count($header) > count($row)) {
                $headerCount = count($header);
                $rowCount = count($row);
                trigger_error("Line #{$i}: Header count is greater than row property count Header: {$headerCount}, Row: {$rowCount}", E_USER_WARNING);
            }

            // record all mismatched header and rows
            if (count($originalHeader) != count($row)) {
                self::$errored[] = $row;
            }

            while (count($header) > count($row)) {
                $row[] = null;
            }

            if (array_filter($row) == false) {
                trigger_error("Row #{$i} is empty", E_USER_WARNING);
                continue;
            }

            // @todo this combines the headers and row which is problematic
            // headers and row does not always match together
            $row = array_combine($header, $row);
            $index = ($hasKey && isset($row[$key])) ? $row[$key] : $key;
            $rows[$index] = $row;

            if ($hasKey == false) {
                $key++;
            }

            // echo "Rows loaded: $i\r";
            $i++;
        }

        fclose($fh);

        if (count(self::$errored) > 1) {
            self::generateErrorCsv("{$file}.errors", self::$errored);
        }

        $metafile = basename($file, '.csv') . '.metadata';
        $previous = self::loadMetadata($metafile);
        $rowCount = count($rows);
        $hash = hash_file('crc32', $file);
        array_shift(self::$errored); // remove first rows (header)

        $metadata = [
            'hash' => $hash,
            'headers' => $header,
            'rows_count' => $rowCount,
            'rows_errored' => self::$errored,
            'datetime' => date('Y-m-d H:i:s'),
            'timestamp' => time(),
        ];

        $metadataUpdate = self::compareMetadata($metadata, $previous);

        if (is_array($metadataUpdate) && $metadataUpdate['modified'] === true) {
            $metadata['update'] = $metadataUpdate;
        }

        if ($metadataUpdate !== false) {
            $source = self::generateMetadata($metafile, $metadata);
            // echo "generated source metadata: {$source}\n";
        }

        if ($metadataUpdate !== false && $previous !== null) {
            $snapshot = basename($file, '.csv') . ".{$previous['timestamp']}.metadata";
            $snapshot = self::generateMetadata($snapshot, $previous);
            // echo "previous source metadata: {$snapshot}\n";
        }

        return new Csv($header, $rows);
    }
}
