<?php

namespace Modules\Article\Jobs;

use Fynduck\FilesUpload\ManipulationImage;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Modules\Article\Entities\Article;
use Modules\Article\Entities\ArticleSettings;
use Modules\Article\Services\ArticleService;

class RegenerateImageSizes implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @param ArticleService $articleService
     * @return void
     */
    public function handle(ArticleService $articleService)
    {
        $imageSettings = ArticleSettings::where('name', 'sizes')->first();
        if ($imageSettings) {
            $data = $articleService->prepareImgParams($imageSettings);
            $articles = Article::where('image', '!=', '')->get(['image']);
            foreach ($articles as $article) {
                $articleService->deleteImages($article->image);
                if ($article->image) {
                    $imageName = $articleService->getOriginalImageName($article->image);
                    $path = Storage::get(Article::FOLDER_IMG . '/' . $imageName);
                    $this->generateBannerImages($path, $data, $article->image);
                }
            }
        }
    }

    /**
     * @param string $path
     * @param array $data
     * @param string $imageName
     * @param string|null $encode
     */
    private function generateBannerImages(string $path, array $data, string $imageName, ?string $encode = 'webp')
    {
        ManipulationImage::load($path)
            ->setSizes($data['sizes'])
            ->setName($imageName)
            ->setFolder(Article::FOLDER_IMG)
            ->setGreyscale($data['greyscale'])
            ->setBlur($data['blur'])
            ->setBrightness($data['brightness'])
            ->setBackground($data['background'])
            ->setOptimize($data['optimize'])
            ->setEncodeFormat($encode)
            ->save($data['resizeMethod']);
    }
}
