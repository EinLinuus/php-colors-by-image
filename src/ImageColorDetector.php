<?php

class ImageColorDetector
{

    /**
     * @throws Exception
     */
    public static function getColors(string $image_path): array
    {
        $image = self::loadImage($image_path);

        $resized_image = self::resizeImage($image, 24, 24);

        imagedestroy($image);

        $width = imagesx($resized_image);
        $height = imagesy($resized_image);

        $colors = [];
        for ($x = 0; $x < $width; $x += 10) {
            for ($y = 0; $y < $height; $y += 10) {
                $rgb = imagecolorat($resized_image, $x, $y);
                $colors[] = self::rgbToHex($rgb);
            }
        }

        imagedestroy($resized_image);

        return array_unique($colors);
    }

    public static function getDominantColor(array $colors): string
    {
        $color_counts = array_count_values($colors);
        arsort($color_counts);
        return array_key_first($color_counts);
    }

    public static function getContrastColor(string $color): string
    {
        $r = hexdec(substr($color, 1, 2));
        $g = hexdec(substr($color, 3, 2));
        $b = hexdec(substr($color, 5, 2));

        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return ($yiq >= 128) ? "#000000" : "#ffffff";
    }

    public static function getContrastRatio(string $color1, string $color2): float
    {
        $l1 = self::getLuminance($color1);
        $l2 = self::getLuminance($color2);

        if ($l1 > $l2) {
            return ($l1 + 0.05) / ($l2 + 0.05);
        } else {
            return ($l2 + 0.05) / ($l1 + 0.05);
        }
    }

    private static function getLuminance(string $color): float
    {
        $r = hexdec(substr($color, 1, 2)) / 255;
        $g = hexdec(substr($color, 3, 2)) / 255;
        $b = hexdec(substr($color, 5, 2)) / 255;

        $r = ($r <= 0.03928) ? $r / 12.92 : (($r + 0.055) / 1.055) ** 2.4;
        $g = ($g <= 0.03928) ? $g / 12.92 : (($g + 0.055) / 1.055) ** 2.4;
        $b = ($b <= 0.03928) ? $b / 12.92 : (($b + 0.055) / 1.055) ** 2.4;

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * @throws Exception
     */
    private static function loadImage(string $image_path): GdImage
    {
        $image_mime_type = mime_content_type($image_path);

        if ($image_mime_type === "image/jpeg") {
            return imagecreatefromjpeg($image_path);
        } else if ($image_mime_type === "image/png") {
            return imagecreatefrompng($image_path);
        } else if ($image_mime_type === "image/gif") {
            return imagecreatefromgif($image_path);
        } else {
            throw new Exception("Unsupported image type");
        }
    }

    private static function resizeImage(GdImage $image, int $width = 100, int $height = 100): GdImage
    {
        $old_width = imagesx($image);
        $old_height = imagesy($image);

        $new_image = imagecreatetruecolor($width, $height);

        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, $old_width, $old_height);

        return $new_image;
    }

    private static function rgbToHex(int $rgb): string
    {
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

}