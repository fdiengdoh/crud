<?php
// src/Controllers/MediaController.php
namespace App\Controllers;

class MediaController
{
    private string $uploadDir;

    public function __construct() {
        $this->uploadDir = PUBLIC_DIR . '/uploads/';
    }

    public function handleUpload(array $file): ?array
    {
        if (!$this->isValidImage($file)) {
            return null;
        }

        $subDir = date('Y/m');
        $uniqueDir = uniqid();
        $targetDir = $this->uploadDir . $subDir . '/' . $uniqueDir;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $targetPath = $targetDir . '/' . $originalName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // If the file is an SVG, return it directly (skip conversion & resizing)
            if ($extension === 'svg') {
                return ['image' => 'uploads/' . $subDir . '/' . $uniqueDir . '/' . $originalName, 'dir' => 'uploads/' . $subDir . '/' . $uniqueDir . '/'];
            }

            // Convert to WebP before resizing (for JPG/PNG)
            $webpPath = $this->convertToWebp($targetPath);
            if ($webpPath) {
                $targetPath = $webpPath;
            }

            // Resize images using the WebP file
            $this->resizeImage($targetPath, $targetDir . "/300x300.webp", 300, 300);
            $this->resizeImage($targetPath, $targetDir . "/500x500.webp", 500, 500);
            $this->resizeImage($targetPath, $targetDir . "/1600x900.webp", 1600, 900);

            return ['image' => 'uploads/' . $subDir . '/' . $uniqueDir . '/' . basename($targetPath), 'dir' => 'uploads/' . $subDir . '/' . $uniqueDir . '/'];
        }

        return null;
    }

    private function convertToWebp(string $source, int $quality = 80): ?string
    {
        [$width, $height, $type] = getimagesize($source);
        $image = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($source),
            IMAGETYPE_PNG  => imagecreatefrompng($source),
            default        => null,
        };

        if (!$image) {
            return null;
        }

        $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $source);
        if (imagewebp($image, $webpPath, $quality)) {
            imagedestroy($image);
            return $webpPath;
        }

        imagedestroy($image);
        return null;
    }

    private function isValidImage(array $file): bool
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        $imageData = @getimagesize($file['tmp_name']);
        $isRasterImage = $imageData !== false;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        // Allow both raster images and SVG files
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

        return in_array($mimeType, $allowedTypes, true) || (!$isRasterImage && $mimeType === 'image/svg+xml');
    }

    private function resizeImage(string $source, string $destination, int $targetWidth, int $targetHeight): void
    {
        [$origWidth, $origHeight, $type] = getimagesize($source);
        $srcImage = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($source),
            IMAGETYPE_PNG  => imagecreatefrompng($source),
            IMAGETYPE_GIF  => imagecreatefromgif($source),
            IMAGETYPE_WEBP => imagecreatefromwebp($source),
            default        => null,
        };

        if (!$srcImage) {
            return;
        }

        $origAspect = $origWidth / $origHeight;
        $targetAspect = $targetWidth / $targetHeight;

        if ($origAspect > $targetAspect) {
            $newHeight = $origHeight;
            $newWidth = (int) ($origHeight * $targetAspect);
            $srcX = (int) (($origWidth - $newWidth) / 2);
            $srcY = 0;
        } else {
            $newWidth = $origWidth;
            $newHeight = (int) ($origWidth / $targetAspect);
            $srcX = 0;
            $srcY = (int) (($origHeight - $newHeight) / 2);
        }

        $newImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled(
            $newImage, $srcImage, 
            0, 0,
            $srcX, $srcY,
            $targetWidth, $targetHeight,
            $newWidth, $newHeight
        );

        imagewebp($newImage, $destination, 90);

        imagedestroy($srcImage);
        imagedestroy($newImage);
    }
}
