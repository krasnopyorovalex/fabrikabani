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

    private const BASE_URL = 'https://grilld.ru/catalog/';

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

        $document = file_get_contents(self::BASE_URL);

        $crawler = new Crawler($document);

        $categories = $crawler->filter('.products-block .product-item')->each(static function (Crawler $node) {

            $image = $node->filter('.product-item-img img')->first()->attr('src');

            $link = $node->filter('.product-item-title a')->first();

            return [
                'name' => preg_replace('/\s\s+/', ' ', $link->text()),
                'link' => $link->attr('href'),
                'image' => 'https://grilld.ru'.$image
            ];
        });

        $categories[] = [
            'name' => 'Дымоходы торговой марки Grill\'D',
            'link' => '/catalog/dymokhody_torgovoy_marki_grill_d/',
            'image' => 'https://grilld.ru/bitrix/templates/aspro_next/images/product5.png'
        ];

        foreach ($categories as $category) {

            $existsCatalog = Catalog::where('alias', str_slug($category['name']))->first();

            if ($existsCatalog) {
                $this->info('Category exists - ' . $existsCatalog->name);

                $this->parseItems($category['link'], $existsCatalog->id);
                //continue;
            }

            //$this->parseCategory($category);
            break;
        }

        $this->info('Well done!');
    }

    /**
     * @param array $category
     * @throws \Exception
     */
    private function parseCategory(array $category): void
    {
        $this->saveCatalog($category);
    }

    /**
     * @param array $category
     * @throws \Exception
     */
    private function saveCatalog(array $category): void
    {
        $catalog = new Catalog();
        $catalog->parent_id = 21;
        $catalog->name = $category['name'];
        $catalog->alias = str_slug($catalog->name);
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

        $links = $crawler->filter('.catalog_block.items.block_list a.thumb')->each(static function (Crawler $node) {
            return $node->attr('href');
        });

        $this->saveItems($links, $catalogId);

        $pages = $crawler->filter('.bottom_nav.block .module-pagination a.dark_link')->each(static function (Crawler $node) {
            return $node->attr('href');
        });

        if (count($pages)) {
            foreach ($pages as $page) {

                $this->info('Page - '.$page);

                $document = file_get_contents('https://grilld.ru'.$page);

                $crawler = new Crawler($document);

                $links2 = $crawler->filter('.catalog_block.items.block_list a.thumb')->each(static function (Crawler $node) {
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

                if (strstr($link, '/1271/')) {
                    continue;
                }

                $document = file_get_contents('https://grilld.ru'.$link);
                $crawler = new Crawler($document);

                $name = $crawler->filter('h2.adh2')->first()->text();

                $priceString = $crawler->filter('.prices_block .price_value')->first();
                if ( !$priceString->count()) {
                    $priceString = $crawler->filter('.only_price .values_wrapper')->first();
                }

                $this->info('https://grilld.ru'.$link);

                $price = (int)str_replace(' ', '', $priceString->text());

                $this->info('Цена: '.$price);

                $image = $crawler->filter('#photo-0 a')->first();

                if ( !$image->count()) {
                    $image = $crawler->filter('.item_slider .offers_img > a')->first();
                }

                $image  = 'https://grilld.ru/'.$image->attr('href');

                $text = $crawler->filter('.tabs_section #descr .cart__big-content');

                if ($text->count()) {
                    $text = '<p>'.$text->html().'</p>';
                } else {
                    $text = '';
                }

                $textProps = '';
                $props = $crawler->filter('.tabs_section #props .cart__parametr');
                if ($props->count()) {
                    $textProps = '<div class="row">'.$props->html().'</div>';
                }
                $textProps = preg_replace('/src="(.*?)"/', 'src="{image}"', $textProps);
                $textProps = str_replace('cart__parametr-img', 'cart__parametr-img col-md-4 col-xs-12', $textProps);
                $textProps = str_replace('cart__parametr-notice', 'cart__parametr-notice col-md-8 col-xs-12', $textProps);
                $textProps = str_replace(' :', ':', $textProps);

                $video = '';
                $videoTab = $crawler->filter('.tabs_section #video .cart__video');
                if ($videoTab->count()) {
                    $video = '<div class="row">' . str_replace('cart__video-item','col-md-6 col-xs-12',$videoTab->html()). '</div>';
                }
                $video = preg_replace('/width="(.*?)"/', 'width="100%"', $video);
                $video = preg_replace('/height="(.*?)"/', 'height="320px"', $video);
                $textProps .= $video;

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

                $exists = CatalogProduct::where('alias', str_slug(str_replace(' Grill`D', '', $name)))->exists();

                $catalogProduct->alias = $exists
                    ? str_slug($catalogProduct->name) .'-' . random_int(0,10)
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
