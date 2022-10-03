<?php

namespace App\Console\Commands;

use App\Catalog;
use App\CatalogProduct;
use App\Domain\CatalogProduct\Commands\DeleteCatalogProductCommand;
use App\Image;
use File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Storage;
use Symfony\Component\DomCrawler\Crawler;

class GrandisMarketDoorsSeeder extends Command
{
    private const BASE_URL = 'https://grandis-market.ru/termostojkie-dveri?limit=200';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:grandis-market';

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
        $forDelete = CatalogProduct::query()->where('catalog_id', 171)->get();

        foreach ($forDelete as $item) {
            dispatch(new DeleteCatalogProductCommand($item->id));
        }

        $document = file_get_contents(self::BASE_URL);

        $crawler = new Crawler($document);

        $products = $crawler->filter('#mfilter-content-container .product-grid .product .left a')->each(static function (Crawler $node) {
            return str_replace('?limit=200', '', $node->attr('href'));
        });

        $this->parseItems(array_slice($products, 1), 171);

        $this->info('Well done!');
    }

    /**
     * @param $uri
     * @param $catalogId
     * @throws \Exception
     */
    private function parseItems($uri, $catalogId): void
    {
        if ($uri) {
            foreach ($uri as $link) {
                $document = file_get_contents($link);
                $crawler = new Crawler($document);

                $name = trim($crawler->filter('h1.product-title-cust')->first()->text());
                $price = str_replace([',', '.00', '"'], '', $crawler->filter('.price .price-new span')->first()->text());
                $price = (int) preg_replace('/[^0-9]/', '', $price);

                $text = $crawler->filter('.product-center .description')->first()->html();
                $text2 = $crawler->filter('#tab-description')->first()->html();


                $image = $crawler->filter('.product-image img')->first()->attr('src');

                $catalogProduct = new CatalogProduct();
                $catalogProduct->catalog_id = $catalogId;
                $catalogProduct->name = $name;

                if (CatalogProduct::where('alias', str_slug($name))->exists()) {
                    while (CatalogProduct::where('alias', str_slug($name))->exists()) {
                        $name .= '-' . random_int(1, 1000);
                        $catalogProduct->alias .= $name . '-' . random_int(1, 1000);
                    }
                } else {
                    $catalogProduct->alias = str_slug($name);
                }

                $catalogProduct->title = $catalogProduct->name . ' | Всё для бани';
                $catalogProduct->description = $catalogProduct->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';
                $catalogProduct->price = $price;
                $catalogProduct->text = '<div class="door-description">' . $text . '</div>';
                $catalogProduct->props = '<div class="door-props-text"><h4>Описание</h4>' . $text2 . '</div>';
                $catalogProduct->not_include_delivery = 1;

                if ($catalogProduct->save()) {
                    $imageNew = explode('/', $image);

                    $name = Str::random(40);

                    $ext = explode('.', end($imageNew));

                    $path = Storage::path('public/images') . '/' . $name . '.' . end($ext);

                    if(File::copy($image, $path)) {
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
                                $font->size(30);
                                $font->color([255, 255, 255, 0.6]);
                                $font->align('center');
                                $font->valign('middle');
                                $font->angle(45);
                            })->save(Storage::path('public/images') . '/' . $name . '.' . end($ext));
                        }
                    }
                }
            }
        }
    }
}
