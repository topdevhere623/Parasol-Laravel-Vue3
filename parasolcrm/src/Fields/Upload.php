<?php

namespace ParasolCRM\Fields;

use App\Services\UploadFile\Facades\UploadFile;
use App\Traits\ImageSizeTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

abstract class Upload extends Field
{
    use ImageSizeTrait;

    /** @var bool */
    public bool $displayOnTable = false;

    public $record = null;

    /**
     * @var int|string
     */
    public $minSize = '';

    /**
     * @param int|string $minSize
     * @return $this
     */
    public function minSize($minSize): self
    {
        $this->minSize = $minSize;
        return $this;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function from(string $className): self
    {
        $this->record = $className;
        return $this;
    }

    /**
     * @param $record
     * @return $this
     */
    public function fillRecord($record): self
    {
        $this->record = $record;

        if ($this->fillableRecord) {
            $record->setAttribute($this->column, $this->setRecordValue($this->value));
        }

        return $this;
    }

    /**
     * @param $value
     * @return string|null
     */
    public function setRecordValue($value): ?string
    {
        $column = $this->record->getOriginal($this->name);
        if ($this->record && !empty($column) && $column !== $value) {
            $this->deleteUploaded($column);
        }

        if ($value instanceof UploadedFile) {
            $this->value = UploadFile::upload(
                $value,
                $this->record::getFilePath($this->name),
                $this->record::getFileSize($this->name),
                $this->record::getFileAction($this->name)
            );
        }

        return $this->value;
    }

    /**
     * @param string $value
     * @return void
     */
    protected function deleteUploaded(string $value): void
    {
        $path = $this->record::getFilePath($this->name);
        $path = trim($path, '/');
        Storage::delete($path.'/original/'.$value);

        foreach ($this->record::getFileSize($this->name) ?? [] as $size) {
            Storage::delete($path.DIRECTORY_SEPARATOR.$this->getSizeForPath($size).DIRECTORY_SEPARATOR.$value);
        }
    }

    /**
     * @return string
     */
    protected function getFileUrl($withSize = null): string
    {
        if ($this->record && $this->value) {
            $path = $this->record::getFilePath($this->name);
            return \URL::uploads(
                trim($path, '/').DIRECTORY_SEPARATOR.($withSize ? $this->getMinFileSize() : 'original/').$this->value
            );
        }
        return '';
    }

    /**
     * @return string
     */
    protected function getMinFileSize(): string
    {
        if ($this->minSize && $this->record && $sizes = $this->record::getFileSize($this->name)) {
            if (count($sizes)) {
                foreach ($sizes as $size) {
                    if (is_array($size) && current($size) === $this->minSize) {
                        return $this->getSizeForPath($size);
                    }
                    if (is_string($size) && $size === $this->minSize) {
                        return $this->getSizeForPath($size);
                    }
                }
            }
        }
        return '';
    }

    /**
     * @param $record
     * @return $this
     */
    public function setFromRecord($record): self
    {
        $this->record = $record;
        $this->value = $record->getOriginal($this->name);
        $path = trim($record::getFilePath($this->name), '/');
        $sizes = $record::getFileSize($this->name);
        $size = is_array($sizes) && count($sizes) ? "/{$this->getSizeForPath(current($sizes))}/" : '/original/';

        $this->withMeta([
            'path' => \URL::uploads($path.$size).'/',
            'originalPath' => \URL::uploads($path.'/original').'/',
        ]);

        return $this;
    }

    public function displayValue($record)
    {
        $this->record = $record;
        return $this->getFileUrl();
    }

    /**
     * Show on table
     *
     * @param $record
     */
    public function resolveDisplayValue($record)
    {
        if (!is_null($this->displayHandlerCallback)) {
            return call_user_func($this->displayHandlerCallback, $record, $this);
        }
        if ($this->displayOnTable) {
            return file_url($record, $this->name, 'small');
        }
    }
}
