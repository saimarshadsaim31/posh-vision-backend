<?php

namespace App;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Aws
{
    public static function generateS3SignedUrl($filePath, $fileName, $contentType)
    {
        try {
            // Generate unique key for the file
            $key = $filePath . '/' . $fileName;
            // Get S3 client from Laravel's Storage facade
            $s3Client = Storage::disk('s3')->getClient();
            // Create command for putObject operation
            $cmd = $s3Client->getCommand('PutObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $key,
                'ContentType' => $contentType,
            ]);
            // Create presigned request
            $request = $s3Client->createPresignedRequest($cmd, '+' . intval(env('AWS_S3_SIGNED_URL_LIFE')) . ' minutes');
            // Get the presigned URL
            $presignedUrl = (string) $request->getUri();
            return response()->json([
                'success' => true,
                'uploadURL' => $presignedUrl,
                'key' => $key,
                'expiresIn' => 300,
                'maxSize' => '2MB'
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate upload URL',
                'message' => $error->getMessage()
            ], 500);
        }
    }
    static public function moveFileFromTempToS3($filename)
    {
        $sourcePath = storage_path('app/public/temp_files/' . $filename);
        $destinationPath = 'cars/' . $filename;

        if (file_exists($sourcePath)) {
            try {
                // Check if the file exists in S3
                if (Storage::disk('s3')->exists($destinationPath)) {
                    Log::info('File already exists in S3: ' . $filename);
                    return $filename;
                }
            } catch (\Exception $e) {
                Log::error('Unable to check file existence on S3: ' . $filename, [
                    'error' => $e->getMessage()
                ]);
            }

            // Proceed to upload the file if it doesn't exist
            try {
                $fileContents = file_get_contents($sourcePath);
                Storage::disk('s3')->put($destinationPath, $fileContents);
                unlink($sourcePath);
                Log::alert('File successfully moved to S3: ' . $filename);
                return $filename;
            } catch (\Exception $e) {
                Log::error('Error while uploading file to S3: ' . $filename, [
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        }

        Log::error('Source file not found: ' . $filename);
        return null;
    }
}
