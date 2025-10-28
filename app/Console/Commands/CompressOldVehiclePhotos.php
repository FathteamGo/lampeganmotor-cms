<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CompressOldVehiclePhotos extends Command
{
    protected $signature = 'photos:compress-old';
    protected $description = 'Compress all old vehicle photos to webp with lower size';

    public function handle()
    {
        $disk = Storage::disk('public');
        $files = $disk->allFiles('vehicle-photos');


        $manager = new ImageManager(new Driver());

        foreach ($files as $file) {
            if (!str_ends_with($file, '.webp')) {
                $this->info("Compressing {$file}...");

                try {
                    $image = $manager->read($disk->path($file))
                        ->resize(1280, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })
                        ->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 70));

                    $newPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $file);
                    $disk->put($newPath, (string) $image);
                    $disk->delete($file);
                } catch (\Throwable $e) {
                    $this->error("❌ Error compressing {$file}: " . $e->getMessage());
                }
            }
        }

        $this->info('✅ Compression done!');
    }
}
