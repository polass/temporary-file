<?php

namespace Polass;

class TemporaryFile
{
    /**
     * File handle.
     *
     * @var resource
     */
    protected $file;

    /**
     * Create a new instance with new temporary file.
     *
     * @return void
     */
    public function __construct()
    {
        $this->create();
    }

    /**
     * Get the file path.
     *
     * @return string|null
     */
    public function getPath(): ?string
    {
        if ($this->opened()) {
            return stream_get_meta_data($this->file)['uri'] ?? null;
        }

        return null;
    }

    /**
     * Get file size.
     *
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->stat()['size'] ?? null;
    }

    /**
     * Get infomation about the file.
     *
     * @return array|null
     */
    public function stat(): ?array
    {
        if ($this->opened()) {
            return ($stat = fstat($this->file)) !== false ? $stat : null;
        }

        return null;
    }

    /**
     * Get the file handle.
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->head()->file;
    }

    /**
     * Create a new temporary file.
     *
     * @return $this
     */
    public function create(): self
    {
        if ($this->opened()) {
            $this->close();
        }

        $this->file = tmpfile();

        return $this;
    }

    /**
     * Determine if the file handle is opened.
     *
     * @return bool
     */
    public function opened(): bool
    {
        return is_resource($this->file);
    }

    /**
     * Move the file pointer to the biginning.
     *
     * @return $this
     */
    public function head(): self
    {
        return $this->seek(0);
    }

    /**
     * Move the file pointer to the specified offset.
     *
     * @param  int  $offset
     * @param  int  $whence
     * @return $this
     */
    public function seek(int $offset, int $whence = SEEK_SET): self
    {
        if ($this->opened()) {
            fseek($this->file, $offset, $whence);
        }

        return $this;
    }

    /**
     * Get the current position of the file pointer.
     *
     * @return int|null
     */
    public function getPosition(): ?int
    {
        if ($this->opened()) {
            return ($position = ftell($this->file)) !== false ? $position : null;
        }

        return null;
    }

    /**
     * Move the file pointer to the ending.
     *
     * @return $this
     */
    public function tail(): self
    {
        return $this->seek(0, SEEK_END);
    }

    /**
     * Overwrite content.
     *
     * @param  mixed  $content
     * @return $this
     */
    public function put($content): self
    {
        $this->close();
        $this->write($content);

        return $this;
    }

    /**
     * Add content to the end of the file.
     *
     * @param  mixed  $content
     * @return $this
     */
    public function add($content): self
    {
        $this->tail();
        $this->write($content);

        return $this;
    }

    /**
     * Write content to the file.
     *
     * @param  mixed  $content
     * @return $this
     */
    public function write($content): self
    {
        if (! $this->opened()) {
            $this->create();
        }

        fwrite($this->file, (string) $content);

        return $this;
    }

    /**
     * Write a BOM.
     *
     * @return $this
     */
    public function writeBom(): self
    {
        return $this->write(pack('C*', 0xEF, 0xBB, 0xBF));
    }

    /**
     * Read content by specified number of bytes.
     *
     * @param  int  $length
     * @return string|null
     */
    public function read(int $length): ?string
    {
        if ($this->opened()) {
            return fread($this->file, $length);
        }

        return null;
    }

    /**
     * Get all content of file.
     *
     * @return string|null
     */
    public function get(): ?string
    {
        $this->head();

        if (($size = $this->getSize()) > 0) {
            return $this->read($size);
        }

        if ($this->opened()) {
            return '';
        }

        return null;
    }

    /**
     * Get line from the file and parse CSV fields.
     *
     * @param  int  $length
     * @param  string  $delimiter
     * @param  string  $enclosure
     * @param  string  $escape
     * @return array|null|false
     */
    public function getcsv(int $length = 0, string $delimiter = ',', string $enclosure = '"', string $escape = '\\')
    {
        if ($this->opened()) {
            return fgetcsv($this->file, $length, $delimiter, $enclosure, $escape);
        }

        return null;
    }

    /**
     * Copy content to the specified file path.
     *
     * @param  string  $filePath
     * @return bool
     */
    public function copy(string $filePath): bool
    {
        return copy($this->getPath(), $filePath);
    }

    /**
     * Reset file content.
     *
     * @return $this
     */
    public function reset(): self
    {
        return $this->close()->create();
    }

    /**
     * Close the file.
     *
     * @return $this
     */
    public function close(): self
    {
        if ($this->opened()) {
            fclose($this->file);
        }

        $this->file = null;

        return $this;
    }

    /**
     * Delete the file.
     *
     * @return $this
     */
    public function delete(): self
    {
        return $this->close();
    }
}
