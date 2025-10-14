<?php

class SimpleCsv
{
    protected string $file;
    protected array $headers = [];
    protected array $rows = [];

    // CREATE new CSV
    public static function create(string $file, array $headers, bool $overwrite = true): self
    {
        if (!$overwrite && file_exists($file)) {
            throw new \RuntimeException("CSV file already exists: $file");
        }

        if (empty($headers)) {
            throw new \InvalidArgumentException("Headers cannot be empty");
        }

        if (count($headers) !== count(array_unique($headers))) {
            throw new \InvalidArgumentException("Headers must be unique");
        }

        $csv = new self($file);
        $csv->headers = array_values($headers);
        $csv->rows = [];
        $csv->save(); // create file immediately
        return $csv;
    }

    // LOAD existing CSV
    public static function load(string $file): self
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("CSV file does not exist: $file");
        }

        $csv = new self($file);
        $csv->read();
        return $csv;
    }

    protected function __construct(string $file)
    {
        $this->file = $file;
    }

    // READ CSV into memory
    protected function read(): void
    {
        $this->rows = [];

        $handle = fopen($this->file, 'r');
        if (!$handle) {
            throw new \RuntimeException("Cannot open CSV file for reading: {$this->file}");
        }

        $this->headers = fgetcsv($handle);
        if ($this->headers === false || empty($this->headers)) {
            fclose($handle);
            throw new \RuntimeException("CSV file has no headers or is empty: {$this->file}");
        }

        if (count($this->headers) !== count(array_unique($this->headers))) {
            fclose($handle);
            throw new \RuntimeException("CSV headers must be unique: {$this->file}");
        }

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($this->headers)) {
                fclose($handle);
                throw new \RuntimeException("CSV row does not match headers count: " . implode(',', $data));
            }
            $this->rows[] = array_combine($this->headers, $data);
        }

        fclose($handle);
    }

    // GET all rows
    public function all(): array
    {
        return $this->rows;
    }

    // FILTER rows by callback (returns array of rows)
    public function filter(callable $callback): array
    {
        return array_values(array_filter($this->rows, $callback));
    }

    // INSERT single or multiple rows (strict: exact keys required)
    public function insert(array $data): self
    {
        $rowsToInsert = isset($data[0]) && is_array($data[0]) ? $data : [$data];

        foreach ($rowsToInsert as $row) {
            if (!is_array($row)) {
                throw new \InvalidArgumentException("Each row must be an associative array");
            }

            $rowKeys = array_values(array_keys($row));
            $expected = array_values($this->headers);

            // strict: keys must match exactly (order doesn't matter, but sets must be equal)
            $missing = array_diff($expected, $rowKeys);
            $extra   = array_diff($rowKeys, $expected);

            if (!empty($missing) || !empty($extra)) {
                $messages = [];
                if (!empty($missing)) $messages[] = "missing: " . implode(', ', $missing);
                if (!empty($extra))   $messages[] = "extra: " . implode(', ', $extra);
                throw new \InvalidArgumentException("Row keys mismatch (" . implode('; ', $messages) . ")");
            }

            // normalize into headers order
            $this->rows[] = $this->normalizeRow($row);
        }

        return $this;
    }

    // UPDATE rows matching callback
    // Strict: newData keys must be subset of headers. Throws if no rows matched.
    public function update(callable $callback, array $newData): self
    {
        if (empty($newData)) {
            throw new \InvalidArgumentException("newData cannot be empty for update");
        }

        $extra = array_diff(array_keys($newData), $this->headers);
        if (!empty($extra)) {
            throw new \InvalidArgumentException("update() contains unknown keys: " . implode(', ', $extra));
        }

        $changed = 0;
        foreach ($this->rows as &$row) {
            // ensure callback returns a truthy value to match
            if ($callback($row)) {
                // merge only allowed keys (we validated already)
                foreach ($newData as $k => $v) {
                    $row[$k] = $v;
                }
                $changed++;
            }
        }

        if ($changed === 0) {
            throw new \RuntimeException("update() matched no rows");
        }

        return $this;
    }

    // DELETE rows matching callback (throws if nothing deleted)
    public function delete(callable $callback): self
    {
        $before = count($this->rows);
        $this->rows = array_values(array_filter($this->rows, fn($r) => !$callback($r)));
        $after = count($this->rows);
        $removed = $before - $after;

        if ($removed === 0) {
            throw new \RuntimeException("delete() matched no rows");
        }

        return $this;
    }

    // SAVE CSV to file
    public function save(): self
    {
        $handle = fopen($this->file, 'w');
        if (!$handle) {
            throw new \RuntimeException("Cannot open CSV file for writing: {$this->file}");
        }

        if (fputcsv($handle, $this->headers) === false) {
            fclose($handle);
            throw new \RuntimeException("Failed to write CSV headers to file: {$this->file}");
        }

        foreach ($this->rows as $row) {
            // strict: ensure row keys match headers exactly (ordered)
            if (array_values(array_keys($row)) !== array_values($this->headers)) {
                fclose($handle);
                throw new \RuntimeException("Row keys do not match headers order when saving: " . implode(',', array_keys($row)));
            }

            $line = array_map(fn($h) => $row[$h] ?? '', $this->headers);
            if (fputcsv($handle, $line) === false) {
                fclose($handle);
                throw new \RuntimeException("Failed to write CSV row to file: " . implode(',', $line));
            }
        }

        fclose($handle);
        return $this;
    }

    // Ensure row matches headers order and return normalized row
    protected function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($this->headers as $h) {
            // since insert validated keys, we can safely access
            $normalized[$h] = $row[$h];
        }
        return $normalized;
    }

    // Create CSV directly from array of arrays (strict)
    public static function createFromArray(string $file, array $rows): self
    {
        if (empty($rows)) {
            throw new \InvalidArgumentException("No data provided");
        }

        // Validate all rows have the same keys (and same set as first)
        $headerKeys = array_keys($rows[0]);
        foreach ($rows as $idx => $row) {
            if (!is_array($row)) {
                throw new \InvalidArgumentException("Each row must be an array (row index: {$idx})");
            }
            if (array_keys($row) !== $headerKeys) {
                throw new \RuntimeException("All rows must have the same keys and order (row index: {$idx})");
            }
        }

        return self::create($file, $headerKeys)
            ->insert($rows)
            ->save();
    }
}
