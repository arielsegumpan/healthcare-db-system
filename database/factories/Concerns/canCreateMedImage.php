<?php

namespace Database\Factories\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Database\Seeders\LocalImages;

trait CanCreateMedImage
{
    protected static $unusedImages = null;

    public function createMedImage(): ?string
    {
        if (is_null(self::$unusedImages)) {
            self::$unusedImages = LocalImages::getFeaturedImage();
        }

        if (empty(self::$unusedImages)) {
            throw new \Exception("No more unique images available for medicine.");
        }

        $imageFile = array_pop(self::$unusedImages);
        $image = file_get_contents($imageFile->getPathname());
        $newFilename = Str::uuid() . '.jpg';

        Storage::disk('public')->put($newFilename, $image);

        return $newFilename;

    }

    public static function setUnusedFeaturedImages($images)
    {
        self::$unusedImages = $images;
    }

    public static function getUnusedFeaturedImages()
    {
        return self::$unusedImages;
    }
}
