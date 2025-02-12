<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function generateSignedUrl(Request $request)
    {
        $request->validate([
            "fileType" => ['required', 'in:artist_product,artist_collection'],
            "fileName" => ['required', 'regex:/^.+\.(jpg|jpeg|png|webp)$/i'],
            "contentType" => ['required', 'in:jpg,jpeg,png,webp']
        ]);
        $path = 'artist_products';
        if($request->fileType == 'collection') {
            $path = 'artist_collections';
        }
        return response()->json(\App\Aws::generateS3SignedUrl(
            $path,
            $request->fileName,
            $request->contentType
        ));
    }
}
