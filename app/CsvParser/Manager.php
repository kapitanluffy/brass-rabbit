<?php

namespace App\CsvParser;

// @todo: stop using static
class Manager
{
    protected static $delimiter = ",";

    protected static $errored = [];

    protected static $name = null;

    public static function setName($name)
    {
        self::$name = $name;
    }

    public function getName()
    {
        return self::$name;
    }

    public static function setDelimiter($delimiter)
    {
        self::$delimiter = $delimiter;
    }

    public static function getDelimiter()
    {
        return self::$delimiter;
    }

    public static function read($file, $key = 0)
    {
        if (($fh = fopen($file, 'r+')) == false) {
            throw new \Exception("Cannot read file [{$file}]");
        }

        $delimiter = self::getDelimiter();
        $header = fgetcsv($fh, 0, $delimiter);
        $rows = [];
        $hasKey = ($key !== 0);
        $extraColumnCount = 1;
        $warning = null;

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
        // echo "\n";

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

        $filename = (self::$name === null) ? basename($file, '.csv') : self::$name;
        $metafile = "{$filename}.metadata";
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
            // $source = self::generateMetadata($metafile, $metadata);
            // echo "generated source metadata: {$source}\n";
        }

        if ($metadataUpdate !== false && $previous !== null) {
            $snapshot = "{$filename}.{$previous['timestamp']}.metadata";
            // $snapshot = self::generateMetadata($snapshot, $previous);
            // echo "previous source metadata: {$snapshot}\n";
        }

        // @note quickfix to prevent reusing the same name across clients
        self::$name = null;

        return new Csv($header, $rows);
    }

    protected static function compareMetadata($newData, $previous)
    {
        if ($previous === null) {
            return true;
        }

        if ($newData['hash'] === $previous['hash']) {
            return false;
        }

        $changes = [
            'headers' => [],
            'rows_count' => null,
            'modified' => false,
        ];

        $newHeaders = array_diff($newData['headers'], $previous['headers']);

        foreach ($newHeaders as $newHeader) {
            // echo "New headers from source: {$newHeader}\n";
            $changes['headers'][] = $newHeader;
            $changes['modified'] = true;
        }

        if ($newData['rows_count'] != $previous['rows_count']) {
            // echo "New row count from source: {$newData['rows_count']}\n";
            $changes['rows_count'] = abs($newData['rows_count'] - $previous['rows_count']);
            $changes['modified'] = true;
        }

        return $changes;
    }

    protected static function loadMetadata($file)
    {
        if (file_exists($file) == false) {
            return null;
        }

        // $file = basename($file, '.csv') . '.metadata';
        $content = file_get_contents($file);
        $data = json_decode($content, true);

        return $data;
    }

    protected static function generateMetadata($file, $data)
    {
        // $file = basename($file, '.csv') . '.metadata';

        if (($fh = fopen($file, 'w+')) == false) {
            throw new \Exception("Cannot write file [{$file}]");
        }

        fwrite($fh, json_encode($data));
        fclose($fh);

        return $file;
    }

    protected static function generateErrorCsv($file, $rows)
    {
        if (($fh = fopen($file, 'w+')) == false) {
            throw new \Exception("Cannot write file [{$file}]");
        }

        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }

        fclose($fh);
        // echo "CSV rows with errors: {$file}\n";
    }

    public static function write($file, Csv $csv)
    {
        if (($fh = fopen($file, 'w+')) == false) {
            throw new \Exception("Cannot write file [{$file}]");
        }

        fputcsv($fh, $csv->headers());
        $total = count($csv->rows());
        $i = 1;

        while ($row = $csv->getRow()) {
            list($index, $data) = $row;
            fputcsv($fh, $data->toArray());

            // echo "Rows written: $i/$total\r";
            $i++;
        }
        // echo "\n";
        fclose($fh);
    }

    public static function fromArray($data, $headers = [])
    {
        $headers = [];
        $rows = [];

        if (empty($data)) {
            throw new \InvalidArgumentException("Cannot create csv with empty rows");
        }

        // $i = 1;
        $total = count($data);
        foreach ($data as $row) {
            $keys = array_keys($row);

            // echo "total keys: " . count($keys) . "\n";
            foreach ($keys as $i => $key) {
                if (!is_string($key)) {
                    $keys[$i] = "column_{$key}";
                }
                // echo "Key: $key\n";
            }

            $headers = array_unique(array_merge($headers, $keys));
            $empty = array_fill_keys($headers, null);
            $rows[] = array_merge($empty, $row);
            $i = count($rows);

            // echo "\rRows loaded: $i/$total";
            // usleep(100);
            // $i++;
        }
        // echo "\n";

        return new Csv($headers, $rows);
    }

    protected static function countLines($fileHandler)
    {
        $linecount = 0;

        while(!feof($handle)){
          $line = fgets($handle);
          $linecount++;
        }

        fclose($handle);

        // echo $linecount;
    }
}
