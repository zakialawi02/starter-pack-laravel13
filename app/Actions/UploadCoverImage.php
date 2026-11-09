<?php

namespace App\Actions;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class UploadCoverImage
{
    /**
     * @param  string|null  $oldImageUrl  URL ke gambar versi _small
     *
     * @throws Exception
     */
    public function execute(?UploadedFile $file, ?string $oldImageUrl = null): ?string
    {
        if (! $file) {
            return $oldImageUrl;
        }

        $baseFilename = time().'_'.Str::random(20);
        $extension = $file->guessExtension();

        $largeFilename = $baseFilename.'_large.'.$extension;
        $smallFilename = $baseFilename.'_small.'.$extension;

        $largeRelativePath = 'media/img/'.$largeFilename;
        $smallRelativePath = 'media/img/'.$smallFilename;

        if ($oldImageUrl) {
            $oldSmallFilename = basename($oldImageUrl);
            $oldLargeFilename = str_replace('_small.', '_large.', $oldSmallFilename);
            $oldFilePaths = [
                'media/img/'.$oldSmallFilename,
                'media/img/'.$oldLargeFilename,
            ];
            Storage::disk('public')->delete($oldFilePaths);
        }

        Storage::disk('public')->putFileAs('media/img', $file, $largeFilename);

        try {
            // <-- 2. Inisialisasi manager dengan driver Imagick
            $manager = new ImageManager(new Driver);

            $fullPathToSaveSmall = Storage::disk('public')->path($smallRelativePath);

            // <-- 3. Gunakan manager untuk membaca (read) file dan memprosesnya
            $image = $manager->read($file);

            $image->scale(height: 300)->save($fullPathToSaveSmall, 60);
        } catch (Exception $e) {
            Storage::disk('public')->delete($largeRelativePath);
            Log::error('Gagal membuat gambar terkompresi: '.$e->getMessage());
            throw new Exception('Failed to compress and save cover image.');
        }

        return Storage::url($smallRelativePath);
    }
}
