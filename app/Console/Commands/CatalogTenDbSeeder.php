<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Catalog;
use App\CatalogProduct;
use App\Image;
use File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Storage;
use Symfony\Component\DomCrawler\Crawler;

class CatalogTenDbSeeder extends Command
{
    private const BASE_URL = "http://u-m-t.ru";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:catalog-ten';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set to db catalog and items from source site url';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->parseItems();

        $this->info('Well done!');
    }

    /**
     * @throws \Exception
     */
    private function parseItems(): void
    {
        $document = file_get_contents(self::BASE_URL . '/catalog/elektrokamenki');
        $crawler = new Crawler($document);

        $products = $crawler->filter('.tovat-list a')->each(function (Crawler $node) {
            return [
                'link' => $node->first()->attr('href')
            ];
        });

        $catalog = Catalog::whereId(347)->firstOrFail();

        foreach ($products as $item) {
            $this->parseCatalogProduct($catalog, $item['link']);
        }
    }

    private function parseCatalogProduct($catalogChild, $link)
    {
        $document = file_get_contents($link);

        $this->info($link);

        if ($document) {
            $crawler = new Crawler($document);

            $this->info($link);

            $image = false;
            $name = $crawler->filter('.seriya-in h1')->first()->text();

            $price = 0;

            if ($crawler->filter('.flexslider ul li img')->count()) {
                $image = $crawler->filter('.flexslider ul li img')->first()->attr('src');
            }

            $fullText = '';
            $text = '';
            if ($crawler->filter('.tabs-cont')->count()) {
                $text = trim($crawler->filter('.tabs-cont li')->eq(0)->html());
                $fullText = $crawler->filter('.tabs-cont li')->eq(1)->html();

                $text = preg_replace("!<a.*?href=\"?'?([^ \"'>]+)\"?'?.*?>(.*?)</a>!is", '$2', $text);
                $fullText = preg_replace("!<a.*?href=\"?'?([^ \"'>]+)\"?'?.*?>(.*?)</a>!is", '$2', $fullText);
            }

            $catalogProduct = new CatalogProduct();
            $catalogProduct->catalog_id = $catalogChild->id;
            $catalogProduct->name = $name;
            $catalogProduct->title = $catalogProduct->name . ' | Всё для бани';
            $catalogProduct->description = $catalogProduct->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';
            $catalogProduct->price = $price;
            $catalogProduct->text = $fullText;
            $catalogProduct->not_include_delivery = 1;
            $catalogProduct->on_request = ($price === 0);
            $catalogProduct->props = $text;
            $catalogProduct->label = 'ekm';

            $alias = Str::slug($catalogProduct->name);

            $catalogProduct->alias = $alias;

            if (!CatalogProduct::whereAlias($alias)->exists() && $catalogProduct->save() && $image) {
                $imageNew = explode('/', $image);

                $name = Str::random(40);

                $ext = explode('.', end($imageNew));

                $path = Storage::path('public/ekm') . '/' . $name . '.' . end($ext);

                try {
                    if (File::copy($image, $path)) {
                        $newImage = new Image();
                        $newImage->path = '/storage/images/' . $name . '.' . end($ext);
                        $newImage->imageable_type = CatalogProduct::class;
                        $newImage->imageable_id = $catalogProduct->id;
                        $newImage->alt = $catalogProduct->name;
                        $newImage->save();
                    }
                } catch (\Exception $exception) {
                    $this->info($exception->getMessage());
                }
            }
        }
    }
}
