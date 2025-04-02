<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

class LocalImages
{
    public const MEDICATIONS = 'med_imgs';
    /**
     * Get all image files for medication.
     *
     * @return array
     */
    public static function getAllFiles(?string $category = LocalImages::MEDICATIONS): array
    {
        return File::files(database_path('seeders/local_images/' . $category));
    }

     /**
     * Get all image files for medication.
     *
     * @return array
     */
    public static function getFeaturedImage(?string $medication = LocalImages::MEDICATIONS): array
    {
        return File::files(database_path('seeders/local_images/' . $medication));
    }
}
