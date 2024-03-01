<?php

namespace ParasolCRM\Fields;

use App\Models\Gallery;
use App\Services\UploadFile\Facades\UploadFile;
use App\Traits\ImageSizeTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

abstract class UploadGroup extends RelationField
{
    use ImageSizeTrait;

    /**
     * @var bool
     */
    public bool $displayOnTable = false;

    public function __construct($name, $label = null, $attrs = null, $relation = 'gallery', $relatedClass = Gallery::class)
    {
        parent::__construct($relation, $relatedClass, $name, $label, $attrs);
    }

    /**
     * @param $record
     * @return $this
     */
    public function setFromRecord($record): self
    {
        $gallery = $record->{$this->relationName};
        if ($gallery && $gallery->count()) {
            $this->value = [];
            foreach ($gallery as $image) {
                $this->value[] = $image;
            }
        }

        return $this;
    }

    public function getValue()
    {
        $gallery = $this->value;
        if (is_array($gallery) && count($gallery)) {
            $this->value = [];

            foreach ($gallery as $item) {
                $value = is_object($item) ? $item->toArray() : $item;
                if (!isset($value['deleteItem'])) {
                    $this->value[] = array_merge($value, $this->getDynamicImages($item));
                }
            }
        }
        return $this->value;
    }

    public function setValue($value): self
    {
        if (!is_null($this->setValueHandlerCallback)) {
            $value = call_user_func($this->setValueHandlerCallback, $value);
        }

        if (is_array($value)) {
            foreach ($value as &$item) {
                if ($item instanceof UploadedFile) {
                    $newImageName = UploadFile::upload($item, $this->record::getFilePath($this->name), $this->record::getFileSize($this->name), $this->record::getFileAction($this->name));
                    if ($newImageName) {
                        $relatedRecord = new $this->relatedClass();
                        $relatedRecord->name = $newImageName;
                        $relatedRecord->imageable_id = $this->record->id;
                        $relatedRecord->imageable_type = $this->record->getMorphClass();
                        $item = $relatedRecord->toArray();
                    }
                }
            }
        }
        $this->value = $value;

        return $this;
    }

    /**
     * @param $record
     * @return $this
     */
    public function updateRelated($record): self
    {
        $this->record = $record;

        $oldRelatedRecords = $record->{$this->relationName}()->get();

        $relatedRecords = [];
        if (is_array($this->value) && count($this->value)) {
            $sort = 1;
            foreach ($this->value as $value) {
                if (is_array($value)) {
                    if (isset($value['id']) && $relatedRecord = $oldRelatedRecords->find($value['id'])) {
                        if (key_exists('deleteItem', $value)) {
                            $this->deleteUploaded($relatedRecord->getOriginal('name'));
                            $relatedRecord->delete();
                        } else {
                            $relatedRecord->sort = $sort + 1;
                            $relatedRecords[] = $relatedRecord;
                        }
                    } elseif (key_exists('name', $value) && !empty($value['name'])) {
                        $relatedRecord = new $this->relatedClass();
                        $relatedRecord->sort = $sort + 1;
                        $relatedRecord->name = $value['name'];
                        $relatedRecords[] = $relatedRecord;
                    }
                }
            }
        }

        if (count($relatedRecords)) {
            $this->record->{$this->relationName}()->saveMany($relatedRecords);
        }

        return $this;
    }

    /**
     * Deleting files
     *
     * @return void
     */
    protected function deleteUploaded(string $fileName): void
    {
        $path = $this->record::getFilePath($this->name);
        $sizes = $this->record::getFileSize($this->name);
        $path = trim($path, '/');

        Storage::delete($path.'/original/'.$fileName);
        foreach ($sizes as $size) {
            Storage::delete($path.DIRECTORY_SEPARATOR.$this->getSizeForPath($size).DIRECTORY_SEPARATOR.$fileName);
        }
    }

    protected function getDynamicImages($model): array
    {
        return array_merge($model->dynamicImages, ['name_original' => $model->dynamicImagesOriginal['name']]);
    }
}
