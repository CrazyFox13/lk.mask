<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;


//https://gist.github.com/mxmtsk/78f92f5e87ff198ddcbc372c66c83611
//https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#uploadpart

class MultipartUploadController extends Controller
{
    public function createMultipartUpload(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
            'file_type' => 'required|string'
        ]);

        $originalFilename = $request->get('filename');
        $filename = auth()->id() . "/" . md5(uniqid()) . "-" . uniqid() . "." . File::extension($originalFilename);

        $disk = Storage::disk('s3');
        $client = $disk/*->getDriver()->getAdapter()*/ ->getClient();

        try {
            $uploader = $client->createMultipartUpload([
                'Bucket' => Config::get('filesystems.disks.s3.bucket'),
                'Key' => $filename,
                'Metadata' => $request->get('metadata') ?? [],
                'ACL' => 'public-read',
                'Expires' => 3000,
                'ContentType' => $request->get('file_type'),
            ]);

            return response()->json([
                "Key" => $filename,
                "UploadId" => $uploader["UploadId"],
            ]);
        } catch (Exception $e) {
            return response()->json([
                "error" => 's3: something went wrong',
                'e' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadPart(string $UploadId, Request $request): JsonResponse
    {
        $request->validate([
            'part' => ['required', 'file', $request->get('PartNumber') == 1 ? 'min:5000' : '', 'max:10000'],
            'Key' => 'required',
            'PartNumber' => 'required|integer|min:0',
        ]);

        $disk = Storage::disk('s3');
        $client = $disk->getClient();
        $part = $request->file('part');

        try {
            $result = $client->uploadPart([
                'Body' => $part->getContent(),
                'Bucket' => Config::get('filesystems.disks.s3.bucket'), // REQUIRED
                'Key' => $request->get('Key'), // REQUIRED
                'PartNumber' => $request->get('PartNumber'), // REQUIRED
                'UploadId' => $UploadId, // REQUIRED
                #'ContentLength' => $part->getSize(),
                #'ChecksumAlgorithm' => 'CRC32|CRC32C|SHA1|SHA256',
                #'ChecksumCRC32' => '<string>',
                #'ChecksumCRC32C' => '<string>',
                #'ChecksumSHA1' => '<string>',
                #'ChecksumSHA256' => '<string>',
                #'ContentLength' => '<integer>',
                #'ContentSHA256' => '<string>',
                #'ExpectedBucketOwner' => '<string>',
                #'RequestPayer' => 'requester',
                #'SSECustomerAlgorithm' => '<string>',
                #'SSECustomerKey' => '<string>',
                #'SSECustomerKeyMD5' => '<string>',
                #'SourceFile' => '<string>',
            ]);

            return response()->json([
                "Key" => $request->get('Key'),
                "PartNumber" => $request->get('PartNumber'),
                "UploadId" => $UploadId,
                "result" => $result,
                "PartSize" => $part->getSize() // in bytes

            ]);
        } catch (Exception $e) {
            return response()->json([
                "error" => 's3: something went wrong',
                'e' => $e->getMessage()
            ], 500);
        }
    }

    public function getUploadedParts(string $UploadId, Request $request): JsonResponse
    {
        $request->validate([
            'Key' => 'required|string'
        ]);

        $disk = Storage::disk('s3');
        $client = $disk->getClient();

        $parts = [];
        $key = $request->get('Key');

        $listPartsPage = function ($startAt) use ($key, $UploadId, $client, &$parts, &$listPartsPage) {
            $listParts = $client->listParts([
                'Bucket' => Config::get('filesystems.disks.s3.bucket'),
                'Key' => $key,
                'UploadId' => $UploadId,
                'PartNumberMarker' => $startAt,
            ]);

            if ($listParts["IsTruncated"]) {
                $listPartsPage($listParts["NextPartNumberMarker"]);
            }
            $parts = array_merge($listParts["Parts"], $parts);
        };


        $listPartsPage(0);

        return response()->json(["parts" => $parts]);
    }

    public function signPartUpload(Request $request)
    {
        $uploadId = $request->route('uploadId');
        $partNumber = $request->route('partNumber');
        $key = $request->input('key');

        if (!$key || !is_string($key)) {
            return response()->json(["error" => 's3: the object key must be passed as a query parameter. For example: "?key=abc.jpg"'], 400);
        }

        $disk = Storage::disk('s3');
        $client = $disk/*->getDriver()->getAdapter()*/ ->getClient();

        try {
            $cmd = $client->getCommand('UploadPart', [
                'Bucket' => Config::get('filesystems.disks.s3.bucket'),
                'Key' => $key,
                'UploadId' => $uploadId,
                'PartNumber' => $partNumber,
                'ACL' => 'public-read',
                //'Body' => '',
                'Expires' => 3000,
                //'ContentType' => 'application/octet-stream',
            ]);

            $request = $client->createPresignedRequest($cmd, '+20 minutes');

            // Get the actual presigned-url
            $presignedUrl = (string)$request->getUri();

            return response()->json([
                "url" => $presignedUrl,
            ]);
        } catch (Exception $e) {
            return response()->json(["error" => 's3: something went wrong'], 500);
        }
    }

    public function completeMultipartUpload(string $UploadId, Request $request)
    {
        $request->validate([
            'Key' => 'required',
            'Parts' => "required|array|min:1",
            'Parts.*.ETag' => "required",
            'Parts.*.PartNumber' => "required"
        ]);

        $disk = Storage::disk('s3');
        $client = $disk->getClient();

        try {
            $completeUpload = $client->completeMultipartUpload([
                'Bucket' => Config::get('filesystems.disks.s3.bucket'),
                'Key' => $request->get("Key"),
                'UploadId' => $UploadId,
                'MultipartUpload' => [
                    'Parts' => $request->get("Parts")
                ],
            ]);
            $head = $client->headObject([
                'Bucket' => Config::get('filesystems.disks.s3.bucket'),
                'Key' => $request->get("Key"),
            ]);
            $metaData=[
                'url' => $completeUpload["Location"],
                'uploaded' => true,
                'file_size'=>$head['ContentLength'],
                'mime_type'=>$head['ContentType']
            ];
            return $this->resourceItemResponse('file', $metaData);
        } catch (Exception $e) {
            return response()->json([
                "error" => 's3: something went wrong',
                'message' => $e->getMessage(),
                'body' => [
                    'Bucket' => Config::get('filesystems.disks.s3.bucket'),
                    'Key' => $request->get("Key"),
                    'UploadId' => $UploadId,
                    'MultipartUpload' => [
                        'Parts' => $request->get("Parts")
                    ],
                ]
            ], 500);
        }
    }

    public function abortMultipartUpload(Request $request)
    {
        $uploadId = $request->route('uploadId');
        $key = $request->input('key');

        if (!$key || !is_string($key)) {
            return response()->json(["error" => 's3: the object key must be passed as a query parameter. For example: "?key=abc.jpg"'], 400);
        }

        $disk = Storage::disk('s3');
        $client = $disk/*->getDriver()->getAdapter()*/ ->getClient();

        try {
            $client->abortMultipartUpload([
                'Bucket' => Config::get('filesystems.disks.s3.bucket'),
                'Key' => $key,
                'UploadId' => $uploadId,
            ]);

            return response()->json([]);
        } catch (Exception $e) {
            return response()->json(["error" => 's3: something went wrong'], 500);
        }
    }
}
