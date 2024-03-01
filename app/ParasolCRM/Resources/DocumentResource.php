<?php

namespace App\ParasolCRM\Resources;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\File;
use ParasolCRM\Fields\Text;
use ParasolCRM\ResourceScheme;

class DocumentResource extends ResourceScheme
{
    public static $model = Document::class;

    public function fields(): array
    {
        return [
            Text::make('filename', 'File')
                ->column('filename')
                ->displayHandler(fn ($record) => $record->filename)
                ->url($this->filenameUrlCallback())
                ->onlyOnTable(),

            Text::make('size')
                ->computed()
                ->displayHandler(function ($values) {
                    return $this->getSize($values['filename']);
                })
                ->onlyOnTable(),
            Text::make('mime_type')
                ->computed()
                ->displayHandler(function ($values) {
                    return $this->getMime($values['filename']);
                })
                ->onlyOnTable(),
            File::make('filename', 'File')
                ->rules('required'),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('')->attach([
                'filename',
            ]),
        ];
    }

    protected function getMime($filename)
    {
        $file = Document::getFilePath('filename').'/original/'.$filename;
        if (Storage::exists($file)) {
            return Storage::mimeType($file);
        }
        return '';
    }

    protected function getSize($filename)
    {
        $file = Document::getFilePath('filename').'/original/'.$filename;
        if (Storage::exists($file)) {
            $size = Storage::size($file);
            if ($size && is_integer($size)) {
                $base = log($size) / log(1024);
                $suffix = ['', 'k', 'M', 'G', 'T'][floor($base)];
                return round(pow(1024, $base - floor($base)), 2).' '.$suffix;
            }
        }
        return '';
    }

    protected function filenameUrlCallback()
    {
        return function ($record) {
            if ($record->type == 'info') {
                return config('app.url').'/data/DetailedClubInfoGeneric.pdf';
            } elseif ($record->type == 'map') {
                return \URL::uploads('map/dubai_map.png');
            }
            return \URL::uploads('documents/'.$record->filename);
        };
    }
}
