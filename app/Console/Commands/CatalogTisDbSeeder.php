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

class CatalogTisDbSeeder extends Command
{
    private const BASE_URL = "https://teplov.ru";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:catalog-tis';

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
        $document = file_get_contents(self::BASE_URL . '/catalog/elementy_dymokhoda/');
        $crawler = new Crawler($document);
        $products = $crawler->filter('.columns .catalog__section')->each(function (Crawler $node) {
           return $node->filter('a')->first()->attr('href');
        });

        $catalog = Catalog::whereId(8)->with(['products'])->firstOrFail();

        foreach ($catalog->products as $product) {
            $product->delete();
        }

        foreach ($products as $product) {
            $document = file_get_contents(self::BASE_URL . $product);
            $crawler = new Crawler($document);
            $crawler->filter('.main-table tr td a')->each(function (Crawler $node) use ($catalog) {
               if (strstr($node->attr('href'), '_430_')) {
                   $this->parseCatalogProduct($catalog, $node->attr('href'));
               }
            });
        }
    }

    private function parseCatalogProduct($catalogChild, $link)
    {
        if ($document = file_get_contents(self::BASE_URL . $link)) {
            $crawler = new Crawler($document);

            $this->info($link);

            $image = false;
            $name = $crawler->filter('h1.bold')->first()->text();

            $price = 0;
            if ($crawler->filter('.product__price b')->count()) {
                $price = trim($crawler->filter('.product__price b')->first()->html());
                $price = preg_replace('/[^0-9]/', '', $price);

                $price = (int) round($price + $price * 5 / 100);
            }

            if ($crawler->filter('.product__img img')->count()) {
                $image = $crawler->filter('.product__img img')->first()->attr('src');
            }

            $text = '';
            if ($crawler->filter('.product__description')->count()) {
                $text = trim($crawler->filter('.product__description')->first()->html());

                $text = preg_replace("!<a.*?href=\"?'?([^ \"'>]+)\"?'?.*?>(.*?)</a>!is", '$2', $text);
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
            $catalogProduct->label = 'tis';

            $alias = Str::slug($catalogProduct->name);

            $catalogProduct->alias = $alias;

            if (!CatalogProduct::whereAlias($alias)->exists() && $catalogProduct->save() && $image) {
                $imageNew = explode('/', $image);

                $name = Str::random(40);

                $ext = explode('.', end($imageNew));

                $path = Storage::path('public/tis') . '/' . $name . '.' . end($ext);

                try {
                    if (File::copy(self::BASE_URL . $image, $path)) {
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
