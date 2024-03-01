<?php

namespace App\Services\UploadFile;

use App\Traits\ImageSizeTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class UploadFileService
{
    use ImageSizeTrait;

    private const DEFAULT_IMAGE_QUALITY = 100;

    protected array $allowedMimes = [];

    protected array $extensionActions = [
        'jpeg2jpg',
        'png2jpg',
        'jpg2png',
        'jpeg2png',
        'bmp2jpg',
        'bmp2png',
    ];

    protected array $actions = [
        'resize',
        'crop',
    ];

    public function __construct()
    {
        $this->allowedMimes = config('filesystems.allowed_mimes');
    }

    private function getNewFileNameWithExtension(string $path, string $extension, $actions = []): string
    {
        $extension = $this->getExtensionFromAction($extension, $actions);

        $newFilename = $this->generateNewFileName().'.'.$extension;
        if ($this->checkFileExist($path, $newFilename)) {
            return $this->getNewFileNameWithExtension($path, $extension);
        }

        return $newFilename;
    }

    private function getExtensionFromAction(string $extension, $actions = []): string
    {
        foreach ($this->extensionActions as $item) {
            if (array_search($item, $actions)) {
                $ext = explode(2, $item);
                if (current($ext) === $extension) {
                    return next($ext);
                }
            }
        }

        return $extension;
    }

    public function generateNewFileName(): string
    {
        return time().Str::random(config('filesystems.new_file_length'));
    }

    public function checkFileExist(string $path, string $newFilename): bool
    {
        return Storage::exists($path.'/original/'.$newFilename);
    }

    protected function createTemporaryFile($tmpFile, string $mime): UploadedFile
    {
        return new UploadedFile(
            stream_get_meta_data($tmpFile)['uri'],
            $this->generateNewFileName(),
            $mime
        );
    }

    /**
     * Create directory for file with sizes
     */
    protected function makeDirectory(string $path, $sizes = null): void
    {
        Storage::makeDirectory($path.'/original');

        if (is_array($sizes) && count($sizes)) {
            foreach ($sizes as $size) {
                Storage::makeDirectory($path.DIRECTORY_SEPARATOR.$this->getSizeForPath($size));
            }
        } else {
            Storage::makeDirectory($path);
        }
    }

    protected function getResizeType($image, $actions, $size): ?string
    {
        if (key_exists('crop', $actions) || in_array('crop', $actions)) {
            list($width, $height) = $this->getSizes($size);

            $originalHeight = $image->height();
            $originalWidth = $image->width();

            if ($originalHeight < $originalWidth) {
                return $width > $height ? 'autoHeight' : 'autoWidth';
            }
            return $width >= $height ? 'autoHeight' : 'autoWidth';
        }

        if (key_exists('autoHeight', $actions) || in_array('autoHeight', $actions)) {
            return 'autoHeight';
        }
        if (key_exists('autoWidth', $actions) || in_array('autoWidth', $actions)) {
            return 'autoWidth';
        }
        return null;
    }

    private function crop(&$image, $size, $actions): void
    {
        if (key_exists('crop', $actions) || in_array('crop', $actions)) {
            list($width, $height) = $this->getSizes($size);
            $image->crop($width, $height);
        }
    }

    private function resize(&$image, $size, $actions): void
    {
        list($width, $height) = $this->getSizes($size);

        $resizeType = $this->getResizeType($image, $actions, $size);

        if ($resizeType === 'autoHeight') {
            $image->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        } elseif ($resizeType === 'autoWidth') {
            $image->resize(null, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
        } else {
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
    }

    /**
     * @param  array|string  $size
     * @return array
     */
    protected function getSizes($size): array
    {
        return is_array($size)
            ? [current($size), next($size)]
            : [$size, $size];
    }

    public function isImage($extension): bool
    {
        if (key_exists($extension, $this->allowedMimes)) {
            return current(explode('/', $this->allowedMimes[$extension])) === 'image';
        }
        return false;
    }

    public function isDocument($extension): bool
    {
        if (key_exists($extension, $this->allowedMimes)) {
            return current(explode('/', $this->allowedMimes[$extension])) !== 'image';
        }
        return false;
    }

    /**
     * @param  string|UploadedFile  $file
     * @throws \ErrorException
     */
    public function getExtension($file, string $type): string
    {
        if ($type === 'object') {
            $mime = $file->getMimeType();
            if ($extension = $this->getExtensionByMime($mime)) {
                return $extension;
            }
        }

        if ($type === 'string') {
            $exploded = explode(',', $file);
            $mime = is_array($exploded) ? current($exploded) : null;
            unset($exploded);

            if ($mime && $extension = $this->getExtensionByMime($mime)) {
                return $extension;
            }
        }

        throw new \ErrorException('Unsupported file extension!.');
    }

    private function checkMimeAllowed(string $mime): bool
    {
        if ($mime && in_array($mime, $this->allowedMimes)) {
            return true;
        }
        throw new \ErrorException('Unsupported file mime type!.');
    }

    private function getExtensionByMime(string $mime): ?string
    {
        if ($extension = array_search($mime, $this->allowedMimes)) {
            if (is_string($extension)) {
                return $extension;
            }
        }
        throw new \ErrorException('Unsupported file mime type!.');
    }

    /**
     * @param  string|UploadedFile  $file
     * @throws \ErrorException
     */
    public function getType($file): string
    {
        if (gettype($file) === 'object' && $file instanceof UploadedFile) {
            return 'object';
        } elseif (is_string($file)) {
            return 'string';
        }
        throw new \ErrorException('Unsupported file type!.');
    }

    protected function saveFileFromStringBase64(
        string $file,
        string $path,
        string $extension,
        $sizes = null,
        $actions = [],
        $fileName = null
    ): ?string {
        $exploded = explode(',', $file);
        $mime = current($exploded);

        if ($this->checkMimeAllowed($mime)) {
            $fileStringData = base64_decode(next($exploded));
            $tmpFile = tmpfile();
            fwrite($tmpFile, $fileStringData);
            $tempUploadFile = $this->createTemporaryFile($tmpFile, $mime);

            if ($this->isImage($extension)) {
                $fileName = $this->saveImage($tempUploadFile, $path, $extension, $sizes, $actions, $fileName);
            }
            if ($this->isDocument($extension)) {
                $fileName = $this->saveDocument($tempUploadFile, $path, $extension, $fileName);
            }
            fclose($tmpFile);
        }

        return $fileName ?? null;
    }

    private function getActionName($action): ?string
    {
        $action = strtolower($action);

        if (key_exists($action, $this->actions) || in_array($action, $this->actions)) {
            return $action;
        }
        return null;
    }

    private function saveImage(
        UploadedFile $file,
        string $path,
        string $extension,
        $sizes = null,
        $actions = [],
        $fileName = null
    ): string {
        $this->makeDirectory($path, $sizes);

        $extension = $this->getExtensionFromAction($extension, $actions);

        if ($fileName) {
            $filename = \Str::beforeLast($fileName, '.').'.'.$extension;
        } else {
            $filename = $this->getNewFileNameWithExtension($path, $extension, $actions);
        }

        $original = Image::make($file)
            ->orientate();

        clone $original->encode($extension, self::DEFAULT_IMAGE_QUALITY);
        Storage::put($path.'/original/'.$filename, $original);

        if (count($sizes)) {
            foreach ($sizes as $size) {
                $image = clone $original;

                foreach ($actions as $action) {
                    $currentActionName = $this->getActionName($action);
                    if ($currentActionName) {
                        $this->{$currentActionName}($image, $size, $actions);
                    }
                }

                $image->encode($extension, self::DEFAULT_IMAGE_QUALITY);
                Storage::put(
                    $path.DIRECTORY_SEPARATOR.$this->getSizeForPath($size).DIRECTORY_SEPARATOR.$filename,
                    $image
                );
                $image->destroy();
                unset($image);
            }
        }
        $original->destroy();

        return $filename;
    }

    public function saveDocument($file, string $path, string $extension, $fileName = null): ?string
    {
        $this->makeDirectory($path);

        if ($fileName) {
            $filename = \Str::beforeLast($fileName, '.').'.'.$extension;
        } else {
            $filename = $this->getNewFileNameWithExtension($path, $extension);
        }

        return Storage::putFileAs($path.'/original/', $file, $filename) ? $filename : null;
    }

    private function uploadFile($file, string $path, $sizes = null, $actions = [], $fileName = null): ?string
    {
        if (empty($file)) {
            return null;
        }
        $path = trim($path, '/');

        $sizes = is_string($sizes) ? [$sizes] : (is_array($sizes) ? $sizes : []);
        $type = $this->getType($file);
        $extension = $this->getExtension($file, $type);

        if ($type === 'object' && $this->isImage($extension)) {
            return $this->saveImage($file, $path, $extension, $sizes, $actions, $fileName);
        }

        if ($type === 'object' && $this->isDocument($extension)) {
            return $this->saveDocument($file, $path, $extension, $fileName);
        }

        if ($type === 'string') {
            return $this->saveFileFromStringBase64($file, $path, $extension, $sizes, $actions, $fileName);
        }

        return null;
    }

    /**
     * Universal file upload
     * Copies the file along the path with the sizes of any extension
     *
     * If the file type is a string then it must be encoded using the
     * $file->getMimeType() . ',' . base64_encode(file_get_contents($file))
     *
     * @param  null|string|UploadedFile  $file
     * @param  string  $path
     * @param  null|array|string  $sizes
     * @param  array  $actions
     * @param  null|string  $fileName
     * @return null|string
     */
    public function upload($file, string $path, $sizes = null, array $actions = [], $fileName = null): ?string
    {
        return $this->uploadFile($file, $path, $sizes, $actions, $fileName);
    }

    /**
     * Copies the file along the path to the same directory with sizes
     *
     * @return string|null
     */
    public function copy($file, string $from, string $to, $sizes = null, bool $removeFrom = false): ?string
    {
        $from = trim($from, '/');
        $to = trim($to, '/');

        if ($file && Storage::exists($from.'/original/'.$file)) {
            $newFileName = $this->generateNewFileName().'.'.File::extension($file);

            $uploaded = Storage::copy($from.'/original/'.$file, $to.'/original/'.$newFileName);

            if ($uploaded && $removeFrom) {
                Storage::delete($from.'/original/'.$file);
            }

            if ($sizes && count($sizes)) {
                foreach ($sizes as $size) {
                    $size = $this->getSizeForPath($size);
                    $uploadedSize = Storage::copy(
                        $from.DIRECTORY_SEPARATOR.$size.DIRECTORY_SEPARATOR.$file,
                        $to.DIRECTORY_SEPARATOR.$size.DIRECTORY_SEPARATOR.$newFileName
                    );
                    if ($uploadedSize && $removeFrom) {
                        Storage::delete($from.DIRECTORY_SEPARATOR.$size.DIRECTORY_SEPARATOR.$file);
                    }
                }
            }

            return $newFileName;
        }
        return null;
    }

    public function move($file, string $from, string $to, $sizes = null): ?string
    {
        return $this->copy($file, $from, $to, $sizes, true);
    }

    public function ldm(string $file, string $from, string $to, $sizes = null, $actions = [], $fileName = null): ?string
    {
        gc_disable();

        Storage::disk('local')->put(
            'temp/path/'.$file,
            Storage::get($from.DIRECTORY_SEPARATOR.$file)
        );

        $tempFile = new UploadedFile(
            Storage::disk('local')->path('temp/path/'.$file),
            $file,
            Storage::disk('local')->mimeType('temp/path/'.$file),
            0,
            false
        );

        $newFilename = $this->uploadFile($tempFile, $to, $sizes, $actions, $fileName);
        unset($tempFile);

        Storage::disk('local')->delete('temp/path/'.$file);

        gc_enable();
        gc_collect_cycles();

        return $newFilename;
    }
}
