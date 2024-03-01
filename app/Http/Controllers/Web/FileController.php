<?php

namespace App\Http\Controllers\Web;

use App\Models\Member\Member;
use App\Models\Program;
use App\Services\UploadFile\Facades\UploadFile;
use Illuminate\Http\Request;
use Storage;

class FileController extends Controller
{
    public function uploadPhotos(Request $request)
    {
        $file = $request->file('file');

        if ($file && $file->isValid()) {
            $filename = UploadFile::upload(
                $file,
                Member::getFilePath('avatar'),
                Member::getFileSize('avatar'),
                Member::getFileAction('avatar')
            );

            return response()->json(
                [
                    'status' => 'success',
                    'filename' => $filename,
                    'path' => \URL::uploads(Member::getFilePath('avatar').'/original/'),
                ]
            );
        }

        return response()->json(['status' => 'error', 'message' => 'File not support'], 401);
    }

    public function getDetailedClubInfoGeneric()
    {
        $path = \Cache::remember('home_club_document', 600, function () {
            return Program::where('source', Program::SOURCE_MAP['advplus'])
                ->first()
                ?->getClubDocsPath();
        });
        try {
            return Storage::response($path);
        } catch (\Exception $exception) {
            abort(404);
        }
    }

    public function getMap()
    {
        try {
            return match (is_entertainer_subdomain()) {
                true => Storage::response('documents/original/te-dubai-map.jpg'),
                false => Storage::response('documents/original/dubai-map.png'),
            };
        } catch (\Exception $exception) {
            abort(404);
        }
    }

    public function getFile($filename)
    {
        try {
            return Storage::response('documents/original/'.$filename);
        } catch (\Exception $exception) {
            abort(404);
        }
    }
}
