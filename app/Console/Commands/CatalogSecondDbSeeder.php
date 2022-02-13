<?php

namespace App\Console\Commands;

use App\Catalog;
use App\CatalogProduct;
use App\Domain\Image\Commands\DeleteImageCommand;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Image;
use File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Storage;
use Symfony\Component\DomCrawler\Crawler;

class CatalogSecondDbSeeder extends Command
{
    use DispatchesJobs;

    private const BASE_URL = 'https://grilld.ru/';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:catalog-second';

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
        //$this->clearNbsp();
        //exit;
        //$this->clear();

        $grilld = Catalog::where('id', 21)->with(['catalogs.products'])->firstOrFail();

        foreach ($grilld->catalogs as $catalog) {
            foreach ($catalog->products as $product) {
                $product->delete();
            }
            $catalog->delete();
        }
        $grilld->delete();
//        exit;

        $grilld = new Catalog();
        $grilld->name = 'GrillD';
        $grilld->title = 'GrillD';
        $grilld->description = 'GrillD';
        $grilld->alias = 'grilld';
        $grilld->text = 'банные печи';
        $grilld->save();

        $document = file_get_contents(self::BASE_URL);

        $crawler = new Crawler($document);

        $categories = $crawler->filter('.collection-list .collection-item')->each(static function (Crawler $node) {

            $image = $node->filter('.collection-img img')->first()->attr('data-src');

            $link = $node->filter('.collection-title a')->first();

            return [
                'name' => preg_replace('/\s\s+/', ' ', $link->text()),
                'link' => $link->attr('href'),
                'image' => $image
            ];
        });

        $categories = array_slice($categories,0, -2);

        foreach ($categories as $category) {

            $existsCatalog = Catalog::where('alias', str_slug($category['name']))->where('parent_id', $grilld->id)->first();

            if ($existsCatalog) {
                $this->info('Category exists - ' . $existsCatalog->name);

                $this->parseItems($category['link'], $existsCatalog->id);
                continue;
            }

            $this->parseCategory($category, $grilld->id);
        }

        $this->info('Well done!');
    }

    /**
     * @param array $category
     * @throws \Exception
     */
    private function parseCategory(array $category, $id): void
    {
        $this->saveCatalog($category, $id);
    }

    /**
     * @param array $category
     * @throws \Exception
     */
    private function saveCatalog(array $category, $id): void
    {
        $catalog = new Catalog();
        $catalog->parent_id = $id;
        $catalog->name = $category['name'];
        $catalog->alias = str_slug($catalog->name.'-grilld');
        $catalog->title = $catalog->name . ' | Всё для бани';
        $catalog->description = $catalog->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';

        if ($catalog->save() && $category['image']) {

            $image = explode('/', $category['image']);

            $name = Str::random(40);

            $ext = explode('.', end($image));

            $path = Storage::path('public/test') . '/' . $name . '.' . end($ext);

            if(File::copy($category['image'], $path)) {
                $newImage = new Image();
                $newImage->path = '/storage/images/' . $name . '.' . end($ext);
                $newImage->imageable_type = Catalog::class;
                $newImage->imageable_id = $catalog->id;
                $newImage->alt = $catalog->name;
                $newImage->save();
            }

            $this->parseItems($category['link'], $catalog->id);
        }
    }

    /**
     * @param $uri
     * @param $catalogId
     * @throws \Exception
     */
    private function parseItems($uri, $catalogId): void
    {
        $this->info('https://grilld.ru'.$uri);

        $document = file_get_contents('https://grilld.ru'.$uri);

        $crawler = new Crawler($document);

        $links = $crawler->filter('.catalog .img-ratio__inner a')->each(static function (Crawler $node) {
            return $node->attr('href');
        });

        $this->saveItems($links, $catalogId);

        $lastPage = $crawler->filter('.pagination .pagination-items a');

        if (count($lastPage)) {
            $lastPage = (int) $lastPage->last()->text();

            for ($i = 2; $i <= $lastPage; $i++) {

                $this->info('Page - '.$i);

                $document = file_get_contents('https://grilld.ru'.$uri.'?page='.$i);

                $crawler = new Crawler($document);

                $links2 = $crawler->filter('.catalog .img-ratio__inner a')->each(static function (Crawler $node) {
                    return $node->attr('href');
                });

                $this->saveItems($links2, $catalogId);
            }
        }
    }

    private function saveItems($links, $catalogId)
    {
        if (count($links)) {
            foreach ($links as $link) {
                $document = file_get_contents('https://grilld.ru'.$link);
                $crawler = new Crawler($document);

                $name = trim($crawler->filter('.product__title')->first()->html());

                $price = 0;
                if (preg_match_all('#<script type="application/ld\+json">(.+?)</script>#su', $crawler->html(), $matches)) {
                    $meta = json_decode(str_replace('\'', '"', $matches[1][0]), true);

                    $price = (int) $meta['offers']['lowPrice'];
                }

                $this->info('https://grilld.ru'.$link);

                $this->info('Цена: '.$price);

                $imageHtml = $crawler->filter('.product__area-photo img.lazyload');
                $image = '';
                if ($imageHtml->count()) {
                    $image = $imageHtml->first()->attr('data-src');
                }

                $textHtml = $crawler->filter('.product__short-description .product__description-content.static-text');
                $text = '';
                if ($textHtml->count()) {
                    $text = $textHtml->html();
                }

                $textProps = '';
                $props = $crawler->filter('#product-characteristics')->first();
                if ($props->count()) {
                    $textProps = $props->html();
                }
//                $textProps = preg_replace('/src="(.*?)"/', 'src="{image}"', $textProps);
//                $textProps = str_replace('cart__parametr-img', 'cart__parametr-img col-md-4 col-xs-12', $textProps);
//                $textProps = str_replace('cart__parametr-notice', 'cart__parametr-notice col-md-8 col-xs-12', $textProps);
//                $textProps = str_replace(' :', ':', $textProps);

//                $existsCatalogProduct = CatalogProduct::where('alias', str_slug(str_replace(' Grill`D', '', $name)))
//                    ->where('catalog_id', $catalogId)
//                    ->first();
//
//                if ($existsCatalogProduct) {
//                    continue;
//                }

                $catalogProduct = new CatalogProduct();
                $catalogProduct->catalog_id = $catalogId;
                $catalogProduct->name = $name;

                $exists = CatalogProduct::where('alias', str_slug($name))->exists();

                $catalogProduct->alias = $exists
                    ? str_slug($catalogProduct->name) .'-' . random_int(0,1000000)
                    : str_slug($catalogProduct->name);

                $catalogProduct->title = $catalogProduct->name . ' | Всё для бани';
                $catalogProduct->description = $catalogProduct->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';
                $catalogProduct->price = $price;
                $catalogProduct->text = $text;
                $catalogProduct->props = $textProps;

                if ($exists) {
                    $this->info('Название для обновления - '.$name);
                    CatalogProduct::where('alias', str_slug(str_replace(' Grill`D', '', $name)))
                        ->update([
                            'price' => $price,
                            'name' => $name
                        ]);
                } elseif ($catalogProduct->save() && $image) {
                    $this->info('Сохранение: '.$name);

                    $imageNew = explode('/', $image);

                    $name = Str::random(40);

                    $ext = explode('.', end($imageNew));

                    $path = Storage::path('public/test_items') . '/' . $name . '.' . end($ext);

                    if(File::copy($image, $path)) {
                        $newImage = new Image();
                        $newImage->path = '/storage/images/' . $name . '.' . end($ext);
                        $newImage->imageable_type = CatalogProduct::class;
                        $newImage->imageable_id = $catalogProduct->id;
                        $newImage->alt = $catalogProduct->name;
                        $newImage->save();

                        $textProps = str_replace('{image}', $newImage->path, $textProps);

                        $catalogProduct->props = $textProps;
                        $catalogProduct->save();
                    }
                }
            }
        }
    }

    private function clear()
    {
        $categories = Catalog::where('parent_id', 21)->with(['products' => function($query){
            return $query->with(['image']);
        }])->get();

        foreach ($categories as $category) {
            foreach ($category->products as $product) {
                /** @var CatalogProduct $product */
                if($product->image) {
                    $this->dispatch(new DeleteImageCommand($product->image));
                }
                $product->delete();
            }
            $category->delete();
        }
    }

    private function clearNbsp()
    {
        $categories = Catalog::where('parent_id', 21)->with(['products' => function($query){
            return $query->with(['image']);
        }])->get();

        foreach ($categories as $category) {
            foreach ($category->products as $product) {
                //$product->props = preg_replace("/\s+/u", " ", $product->props);
                //$product->text = str_replace('<p> </p>', '', $product->text);
                //$product->text = str_replace('<br>', '', $product->text);
                $product->props = str_replace('</iframe>>', '</iframe>', $product->props);
                $product->save();
            }
        }
    }
}
