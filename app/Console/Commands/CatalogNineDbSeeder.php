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

class CatalogNineDbSeeder extends Command
{
    private const BASE_URL = "https://ekedr.ru";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:catalog-nine';

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
        $catalogItems = [
            [
                'link' => '/catalog/fitobochki/',
                'name' => 'Фитобочки',
                'child' => []
            ],
            [
                'link' => '/catalog/kupeli/',
                'name' => 'Купели и фитобочки',
                'child' => []
            ],
            [
                'link' => '/catalog/ik_sauny/',
                'name' => 'ИК сауны',
                'child' => []
            ]
        ];

        foreach ($catalogItems as &$category) {
            $document = file_get_contents(self::BASE_URL . $category['link']);
            $crawler = new Crawler($document);

            $child = $crawler->filter('.catalog-section .product-item')
                ->each(function (Crawler $node) {
                    return [
                        'link' => $node->filter('.product-item-title a')->first()->attr('href')
                    ];
                });

            $category['child'] = array_merge($category['child'], $child);

            for ($i = 2; $i <= 10; $i++) {
                $document = file_get_contents(self::BASE_URL . $category['link'] . '?PAGEN_1=' . $i);
                if (!$document) {
                    continue;
                }

                $crawler = new Crawler($document);

                $child = $crawler->filter('.catalog-section .product-item')->each(function (Crawler $node) {
                    return [
                        'link' => $node->filter('.product-item-title a')->first()->attr('href')
                    ];
                });

                $category['child'] = array_merge($category['child'], $child);
            }
        }

        foreach ($catalogItems as $category) {
            $alias = Str::slug($category['name']);

            $catalog = new Catalog();
            $catalog->name = $category['name'];
            //$catalog->parent_id = 302;
            $catalog->alias = $alias;
            $catalog->title = $catalog->name . ' | Всё для бани';
            $catalog->description = $catalog->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';

            if (!Catalog::where('alias', $catalog->alias)->exists()) {
                $catalog->save();
            } else {
                $catalog = Catalog::where('alias', $catalog->alias)->firstOrFail();
            }

            foreach ($category['child'] as $item) {
                $this->parseCatalogProduct($catalog, $item['link']);
            }
        }
    }

    private function parseCatalogProduct($catalogChild, $link)
    {
        $document = file_get_contents(self::BASE_URL . $link);

        $this->info(self::BASE_URL . $link);

        if ($document) {
            $crawler = new Crawler($document);

            $this->info($link);

            $image = false;
            $name = $crawler->filter('h1.bx-title')->first()->text();

            $price = 0;
            if ($crawler->filter('.product-item-detail-price-current')->count()) {
                $price = trim($crawler->filter('.product-item-detail-price-current')->first()->html());
                $price = preg_replace('/[^0-9]/', '', $price);
            }

            if (!$price) {
                $price = 0;
            }

            if ($crawler->filter('.product-item-detail-slider-images-container .product-item-detail-slider-image img')->count()) {
                $image = $crawler->filter('.product-item-detail-slider-images-container .product-item-detail-slider-image img')->first()->attr('src');
            }

            $fullText = '';
            $text = '';
            if ($crawler->filter('.product-item-detail-tabs-container')->count()) {
                $textDesc = trim($crawler->filter('.product-item-detail-tab-content[data-value=description]')->first()->html());
                $textProperties = $crawler->filter('.product-item-detail-tab-content[data-value=properties]')->first()->html();

                $textDesc = preg_replace("!<a.*?href=\"?'?([^ \"'>]+)\"?'?.*?>(.*?)</a>!is", '$2', $textDesc);
                $textProperties = preg_replace("!<a.*?href=\"?'?([^ \"'>]+)\"?'?.*?>(.*?)</a>!is", '$2', $textProperties);

                $text = $textDesc . $textProperties;
            }

            if ($crawler->filter('.product-youtube-block')->count()) {
                $youtube = $crawler->filter('.product-youtube-block')->first()->attr('data-yt-video-id');

                $fullText = '<iframe width="100%" height="400" src="https://www.youtube.com/embed/'.$youtube.'" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            }

            $catalogProduct = new CatalogProduct();
            $catalogProduct->catalog_id = $catalogChild->id;
            $catalogProduct->name = $name;
            $catalogProduct->title = $catalogProduct->name . ' | Всё для бани';
            $catalogProduct->description = $catalogProduct->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';
            $catalogProduct->price = $price;
            $catalogProduct->text = $text;
            $catalogProduct->not_include_delivery = 1;
            $catalogProduct->on_request = ($price === 0);
            $catalogProduct->props = $fullText;

            $alias = Str::slug($catalogProduct->name);

            $catalogProduct->alias = $alias;

            if (!CatalogProduct::whereAlias($alias)->exists() && $catalogProduct->save() && $image) {
                $imageNew = explode('/', $image);

                $name = Str::random(40);

                $ext = explode('.', end($imageNew));

                $path = Storage::path('public/ekedr') . '/' . $name . '.' . end($ext);

                try {
                    if (File::copy(self::BASE_URL . $image, $path)) {
                        $newImage = new Image();
                        $newImage->path = '/storage/images/' . $name . '.' . end($ext);
                        $newImage->imageable_type = CatalogProduct::class;
                        $newImage->imageable_id = $catalogProduct->id;
                        $newImage->alt = $catalogProduct->name;

                        if ($newImage->save()) {
                            $im = (new ImageManager())->make($path);

                            $imHeight = $im->height();
                            $imWidth = $im->width();

                            $im->text('fabrikabani-krym.ru', abs($imWidth / 2), abs($imHeight / 2), static function ($font) {
                                $font->file(public_path('fonts/Arial-Black.ttf'));
                                $font->size(22);
                                $font->color(array(255, 255, 255, 0.6));
                                $font->align('center');
                                $font->valign('middle');
                                $font->angle(45);
                            })->save(Storage::path('public/ekedr') . '/' . $name . '.' . end($ext));
                        }
                    }
                } catch (\Exception $exception) {
                    $this->info($exception->getMessage());
                }
            }
        }
    }
}
