<?php

namespace App\Services\Frontend;

use App\Models\MediaAsset;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    private array $metadata = [];

    private function local(MediaAsset $asset): bool
    {
        return config('filesystems.disks.'.$asset->disk.'.driver') === 'local';
    }

    public function directory(MediaAsset $asset): string
    {
        return 'optimized/'.sha1($asset->disk.':'.$asset->path);
    }

    public function attributes(?MediaAsset $asset): array
    {
        if (! $asset || ! str_starts_with($asset->mime_type, 'image/')) {
            return [];
        }

        return $this->metadata[$asset->id] ??= $this->readAttributes($asset);
    }

    private function readAttributes(MediaAsset $asset): array
    {
        $result = ['src' => $asset->url(), 'width' => 1200, 'height' => 800, 'srcset' => ''];
        if (! $this->local($asset)) {
            return $result;
        }
        $disk = Storage::disk($asset->disk);
        $original = $disk->path($asset->path);
        $dimensions = is_file($original) ? @getimagesize($original) : false;
        if (! $dimensions) {
            return $result;
        }
        if (in_array($this->orientation($original, $asset->mime_type), [5, 6, 7, 8], true)) {
            [$dimensions[0],$dimensions[1]] = [$dimensions[1], $dimensions[0]];
        }
        $result['width'] = $dimensions[0];
        $result['height'] = $dimensions[1];
        $variants = [];
        foreach (config('frontend.image_widths') as $width) {
            if ($width > $dimensions[0]) {
                continue;
            }
            $path = $this->directory($asset).'/'.$width.'.webp';
            if ($disk->exists($path)) {
                $variants[] = $disk->url($path).' '.$width.'w';
            }
        }
        $result['srcset'] = implode(', ', $variants);

        return $result;
    }

    public function optimize(MediaAsset $asset): bool
    {
        if (! $this->local($asset) || ! in_array($asset->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true) || ! function_exists('imagewebp')) {
            return false;
        }
        $disk = Storage::disk($asset->disk);
        $path = $disk->path($asset->path);
        $info = is_file($path) ? @getimagesize($path) : false;
        if (! $info || $info[0] * $info[1] > 8000000) {
            return false;
        }
        if (in_array($this->orientation($path, $asset->mime_type), [5, 6, 7, 8], true)) {
            [$info[0],$info[1]] = [$info[1], $info[0]];
        }
        $widths = array_filter(config('frontend.image_widths'), fn ($width) => $width <= $info[0]);
        if (! $widths) {
            return false;
        }
        $source = match ($asset->mime_type) {
            'image/jpeg' => @imagecreatefromjpeg($path),'image/png' => @imagecreatefrompng($path),'image/webp' => @imagecreatefromwebp($path)
        };
        if (! $source) {
            return false;
        }
        $orientation = $this->orientation($path, $asset->mime_type);
        if (in_array($orientation, [3, 5, 6, 7, 8], true)) {
            $rotated = imagerotate($source, $orientation === 3 ? 180 : ($orientation === 8 ? 90 : -90), 0);
            if ($rotated) {
                imagedestroy($source);
                $source = $rotated;
            }
        }
        if (in_array($orientation, [2, 5], true)) {
            imageflip($source, IMG_FLIP_HORIZONTAL);
        }
        if (in_array($orientation, [4, 7], true)) {
            imageflip($source, IMG_FLIP_VERTICAL);
        }
        try {
            foreach ($widths as $width) {
                $destination = $this->directory($asset).'/'.$width.'.webp';
                if ($disk->exists($destination)) {
                    continue;
                }
                $height = max(1, (int) round($info[1] * $width / $info[0]));
                $image = imagecreatetruecolor($width, $height);
                imagealphablending($image, false);
                imagesavealpha($image, true);
                imagecopyresampled($image, $source, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
                ob_start();
                $encoded = imagewebp($image, null, 76);
                $bytes = ob_get_clean();
                imagedestroy($image);
                if ($encoded && $bytes !== '') {
                    $disk->put($destination, $bytes);
                }
            }
        } finally {
            imagedestroy($source);
        }
        unset($this->metadata[$asset->id]);

        return true;
    }

    private function orientation(string $path, string $mime): int
    {
        return $mime === 'image/jpeg' && function_exists('exif_read_data') ? (int) ((@exif_read_data($path)['Orientation']) ?? 1) : 1;
    }

    public function remove(MediaAsset $asset): void
    {
        $disk = Storage::disk($asset->disk);
        foreach (config('frontend.image_widths') as $width) {
            $disk->delete($this->directory($asset).'/'.$width.'.webp');
        }
    }
}
