<?php

namespace App\Http\Controllers;

use App\Http\Requests\Upload\SimpleUpload;
use Aws\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(SimpleUpload $request): JsonResponse
    {
        $file = $request->file("file");
        $extension = $file->getClientOriginalExtension();
        $content = $file->getContent();
        $now = time();
        $uid = auth()->id();
        $fileName = "$uid/$now.$extension";
        $uploaded = Storage::disk('s3')->put($fileName, $content, 'public');

        return $this->resourceItemResponse('file', [
            'url' => Storage::url($fileName),
            'uploaded' => $uploaded,
            'file_size'=>$file->getSize(),
            'extension'=>$extension,
            'name'=>$file->getClientOriginalName(),
            'mime_type'=>$file->getMimeType()
        ]);
    }
}
