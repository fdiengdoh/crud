<?php
namespace App\Controllers;

class MediaController
{
    private string $uploadDir;

    public function __construct() {
        // Use the PUBLIC_DIR constant (defined in init.php) to store uploads
        $this->uploadDir = PUBLIC_DIR . '/uploads/';
    }

    /**
     * Handle an image upload.
     * Returns the relative path to the original file (relative to the public directory) on success; null on failure.
     *
     * @param array $file
     * @return string|null
     */
    public function handleUpload(array $file): ? array
    {
        if (!$this->isValidImage($file)) {
            return null;
        }

        // Create a year/month subdirectory
        $subDir = date('Y/m');
        // Generate a unique directory inside the year/month folder
        $uniqueDir = uniqid();
        $targetDir = $this->uploadDir . $subDir . '/' . $uniqueDir;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Preserve the original file name (using basename to avoid directory traversal)
        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $targetPath = $targetDir . '/' . $originalName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Create resized versions:
            $this->resizeImage($targetPath, $targetDir . "/300x300.$extension", 300, 300);
            $this->resizeImage($targetPath, $targetDir . "/500x500.$extension", 500, 500);
            // Create a 16:9 image with a width of 1000px (height calculated as 1600/16*9 = 900)
            $this->resizeImage($targetPath, $targetDir . "/1600x900.$extension", 1600, 900);
            
            //Final Upload Directory
            $dir = 'uploads/' . $subDir . '/' . $uniqueDir . '/';

            // Return the relative path to the original file and relative dir (relative to the public directory)
            $return = ['image' =>  $dir . $originalName , 'dir' => $dir ];

            return $return;
        }

        return null;
    }

    /**
     * Validate that the uploaded file is a valid image.
     *
     * @param array $file
     * @return bool
     */
    private function isValidImage(array $file): bool
    {
        // Check if the file was uploaded via HTTP POST
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        
        // Use getimagesize() to check if it's a valid image
        $imageData = @getimagesize($file['tmp_name']);
        if ($imageData === false) {
            return false;
        }
        
        // Use the Fileinfo extension for additional security
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // Define allowed MIME types
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        return in_array($mimeType, $allowedTypes, true);
    }

    private function resizeImage(string $source, string $destination, int $targetWidth, int $targetHeight): void
{
    [$origWidth, $origHeight, $type] = getimagesize($source);
    $srcImage = match ($type) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($source),
        IMAGETYPE_PNG  => imagecreatefrompng($source),
        IMAGETYPE_GIF  => imagecreatefromgif($source),
        IMAGETYPE_WEBP => imagecreatefromwebp($source),
        default       => null,
    };

    if (!$srcImage) {
        return;
    }

    // Determine the optimal cropping dimensions
    $origAspect = $origWidth / $origHeight;
    $targetAspect = $targetWidth / $targetHeight;

    if ($origAspect > $targetAspect) {
        // Original is wider: crop width
        $newHeight = $origHeight;
        $newWidth = (int) ($origHeight * $targetAspect);
        $srcX = (int) (($origWidth - $newWidth) / 2); // Center horizontally
        $srcY = 0;
    } else {
        // Original is taller: crop height
        $newWidth = $origWidth;
        $newHeight = (int) ($origWidth / $targetAspect);
        $srcX = 0;
        $srcY = (int) (($origHeight - $newHeight) / 2); // Center vertically
    }

    // Create a new blank image with target dimensions
    $newImage = imagecreatetruecolor($targetWidth, $targetHeight);

    // Crop & Resize
    imagecopyresampled(
        $newImage, $srcImage, 
        0, 0, // Destination X, Y
        $srcX, $srcY, // Source X, Y (cropping start point)
        $targetWidth, $targetHeight, // Destination Width, Height
        $newWidth, $newHeight // Source Width, Height (cropped size)
    );

    // Save the new image
    match ($type) {
        IMAGETYPE_JPEG => imagejpeg($newImage, $destination, 90),
        IMAGETYPE_PNG  => imagepng($newImage, $destination),
        IMAGETYPE_GIF  => imagegif($newImage, $destination),
        IMAGETYPE_WEBP => imagewebp($newImage, $destination, 90),
    };

    // Free memory
    imagedestroy($srcImage);
    imagedestroy($newImage);
}

}
