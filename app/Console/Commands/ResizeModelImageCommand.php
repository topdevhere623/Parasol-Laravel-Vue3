<?php

namespace App\Console\Commands;

use App\Models\FilePathMap;
use App\Models\Member\Member;
use App\Services\UploadFile\Facades\UploadFile;
use App\Traits\ImageSizeTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ParasolCRM\Activities\Facades\Activity;

class ResizeModelImageCommand extends Command
{
    use ImageSizeTrait;

    protected $signature = 'resize:image';

    protected $description = 'Resize Model Image Command';

    protected ?string $model = null;

    protected array $oldFileConfig = [];

    // Flag for save original name
    protected bool $saveName = false;

    // Flag for save original file
    protected bool $saveFile = false;

    // Flag for save into FilePathMap
    protected bool $fileMapSave = false;

    // Flag for the update in FilePathMap
    protected bool $fileMapUpdate = false;

    protected array $allowedMimes = [];

    public function __construct()
    {
        $this->allowedMimes = config('filesystems.allowed_mimes');
        parent::__construct();

        $this->model = Member::class;
        $this->oldFileConfig = [
            'avatar' => [
                'path' => 'member/avatar',
                'size' => [200, 300, 500],
            ],
        ];
        $this->saveName = true;
        $this->saveFile = true;
    }

    public function handle()
    {
        Activity::disable();

        $preQuery = $this->model::withTrashed();

        $count = $preQuery->count();
        $this->info('Resize Model Image in process... Items-'.$count);
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $preQuery->chunk(10, function ($items) use ($bar) {
            foreach ($items as $item) {
                foreach ($this->oldFileConfig as $column => $params) {
                    if ($column === 'gallery') {
                        $this->uploadGallery($item);
                    } else {
                        if ($item->{$column}) {
                            $this->uploadFile($item, $column);
                        }
                    }
                }
                $bar->advance();
            }
        });

        $bar->finish();

        $saved = $this->model::withTrashed()->count();
        $diff = $count - $saved == 0 ? 0 : ($count - $saved) / $count;
        $percent = (100 - $diff * 100);
        echo "\n";
        $this->info($percent.'% saved Resize Model Image Items-'.$saved);
        return 0;
    }

    protected function uploadFile($item, $column)
    {
        $oldFileName = $item->{$column};
        $oldPath = trim($this->oldFileConfig[$column]['path'], '/').'/original';
        $newPath = trim($this->model::getFilePath($column), '/');

        if (Storage::exists($oldPath.DIRECTORY_SEPARATOR.$oldFileName)
            && $this->checkMimeAllowed(Storage::mimeType($oldPath.DIRECTORY_SEPARATOR.$oldFileName))
            && (Storage::size($oldPath.DIRECTORY_SEPARATOR.$oldFileName) > 0)
        ) {
            try {
                $newFileName = UploadFile::ldm(
                    $oldFileName,
                    $oldPath,
                    $newPath,
                    $this->model::getFileSize($column),
                    $this->model::getFileAction($column),
                    $this->saveName ? $oldFileName : null
                );

                if ($newFileName) {
                    if (!$this->saveFile) {
                        $this->deleteFile($item, $column);

                        if ($this->fileMapUpdate) {
                            FilePathMap::where('new_path', $oldFileName.DIRECTORY_SEPARATOR.$oldFileName)
                                ->updateOrCreate([
                                    'new_path' => $newPath.'/original/'.$newFileName,
                                ]);
                        }
                    }

                    if ($this->fileMapSave) {
                        FilePathMap::create([
                            'old_path' => $oldPath.DIRECTORY_SEPARATOR.$oldFileName,
                            'new_path' => $newPath.'/original/'.$newFileName,
                        ]);
                    }
                }
                $item->{$column} = $newFileName;
            } catch (\Exception $e) {
                report($e);
                logger($this->model.' ID:'.$item->id." {$column} ".$oldPath.DIRECTORY_SEPARATOR.$oldFileName);
            }
        } else {
            logger($this->model.' ID:'.$item->id." {$column} ".$oldPath.DIRECTORY_SEPARATOR.$oldFileName);
        }
        $item->save();
    }

    protected function uploadGallery($item)
    {
        $preQuery = $item->gallery();
        $oldPath = trim($this->oldFileConfig['gallery']['path'], '/').'/original';
        $newPath = trim($this->model::getFilePath('gallery'), '/');

        $preQuery->chunk(10, function ($items) use ($oldPath, $newPath) {
            foreach ($items as $galleryItem) {
                $oldFileName = trim($galleryItem->name);

                if (Storage::exists($oldPath.DIRECTORY_SEPARATOR.$oldFileName)
                    && $this->checkMimeAllowed(Storage::mimeType($oldPath.DIRECTORY_SEPARATOR.$oldFileName))
                    && (Storage::size($oldPath.DIRECTORY_SEPARATOR.$oldFileName) > 0)
                ) {
                    try {
                        $newFileName = UploadFile::ldm(
                            $oldFileName,
                            $oldPath,
                            $newPath,
                            $this->model::getFileSize('gallery'),
                            $this->model::getFileAction('gallery'),
                            $this->saveName ? $oldFileName : null
                        );

                        $galleryItem->name = $newFileName;

                        if ($newFileName && $galleryItem->save()) {
                            if (!$this->saveFile) {
                                $this->deleteFile($galleryItem, 'gallery');

                                if ($this->fileMapUpdate) {
                                    FilePathMap::where('new_path', $oldFileName.DIRECTORY_SEPARATOR.$oldFileName)
                                        ->updateOrCreate([
                                            'new_path' => $newPath.'/original/'.$newFileName,
                                        ]);
                                }
                            }

                            if ($this->fileMapSave) {
                                FilePathMap::create([
                                    'old_path' => $oldFileName.DIRECTORY_SEPARATOR.$oldFileName,
                                    'new_path' => $newPath.'/original/'.$newFileName,
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        logger(
                            $this->model.' ID:'.$galleryItem->galleryable_id.' gallery '.$oldPath.DIRECTORY_SEPARATOR.$oldFileName
                        );
                    }
                } else {
                    logger($this->model.' ID:'.$galleryItem->galleryable_id.' gallery '.$oldPath.DIRECTORY_SEPARATOR.$oldFileName);
                }

                unset($galleryItem);
            }
        });
    }

    protected function deleteFile($item, $column): void
    {
        $oldFileName = $column == 'gallery' ? $item->name : $item->{$column};
        $oldPath = trim($this->oldFileConfig[$column]['path'], '/');

        Storage::delete($oldPath.'/original/'.$oldFileName);

        $sizes = $this->model::getFileSize($column);

        if (is_array($sizes) && count($sizes)) {
            foreach ($sizes as $size) {
                $size = $this->getSizeForPath($size);
                Storage::delete($oldPath.DIRECTORY_SEPARATOR.$size.DIRECTORY_SEPARATOR.$oldFileName);
            }
        }
    }

    private function checkMimeAllowed(string $mime): bool
    {
        return (bool) ($mime && in_array($mime, $this->allowedMimes));
    }
}
