<?php

namespace App\Http\Controllers\Api\V1\Files;

use App\Http\Controllers\Controller;
use App\Models\CourseFile;
use App\Models\Course;
use App\Services\BunnyCdnService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    protected BunnyCdnService $bunnyCdn;

    public function __construct(BunnyCdnService $bunnyCdn)
    {
        $this->bunnyCdn = $bunnyCdn;
    }

    public function getCourseFiles(Request $request, Course $course)
    {
        // Protected by course.enrollment middleware
        $files = $course->files()->get();

        return response()->json([
            'success' => true,
            'message' => 'Files retrieved successfully.',
            'data' => $files->map(function($file) {
                return [
                    'id' => $file->id,
                    'title' => $file->title,
                    'file_name' => $file->file_name,
                    'mime_type' => $file->mime_type,
                    'file_size_bytes' => $file->file_size_bytes,
                    // Secure URL generated using HMAC signature key
                    'download_url' => $this->bunnyCdn->generateSignedUrl($file->file_path),
                ];
            })
        ]);
    }

    public function downloadFile(Request $request, $fileId)
    {
        $file = CourseFile::findOrFail($fileId);

        // Generate signed URL
        $signedUrl = $this->bunnyCdn->generateSignedUrl($file->file_path);

        return response()->json([
            'success' => true,
            'message' => 'Secure download link generated.',
            'download_url' => $signedUrl
        ]);
    }
}
