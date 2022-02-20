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

class CatalogVezuviyDbSeeder extends Command
{
    private const BASE_URL = "https://vezuviy.su";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:catalog-vezuviy';

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
                'link' => '/pechi-dlya-bani-i-sauny/chugunnye-pechi-dlya-bani-i-sauny/',
                'id' => 248,
                'child' => []
            ],
            [
                'link' => '/pechi-dlya-bani-i-sauny/stalnye-pechi-dlya-bani-i-sauny/',
                'id' => 249,
                'child' => []
            ],
            [
                'link' => '/pechi-dlya-bani-i-sauny/pechi-v-oblicovke-naturalnym-kamnem/',
                'id' => 252,
                'child' => []
            ]
        ];

        foreach ($catalogItems as &$category) {
            $document = file_get_contents(self::BASE_URL . $category['link']);
            $crawler = new Crawler($document);

            $this->info('Category link: ' . self::BASE_URL . $category['link']);

            $child = $crawler->filter('.grid-list .ty-column3')
                ->each(function (Crawler $node) {
                    return [
                        'link' => $node->filter('.ty-center-block a')->first()->attr('href')
                    ];
                });

            $category['child'] = array_merge($category['child'], $child);

            for ($i = 2; $i <= 5; $i++) {
                try {
                    $document = file_get_contents(self::BASE_URL . $category['link'] . 'page-' . $i . '/');

                    $this->info('Category link: ' . self::BASE_URL . $category['link'] . 'page-' . $i . '/');

                    $crawler = new Crawler($document);

                    $child = $crawler->filter('.grid-list .ty-column3')->each(function (Crawler $node) {
                        if ($node->filter('.ty-center-block a')->count()) {
                            return [
                                'link' => $node->filter('.ty-center-block a')->first()->attr('href')
                            ];
                        }

                        return false;
                    });

                    $category['child'] = array_merge($category['child'], $child);
                } catch (\Exception $exception) {
                    $this->info($exception->getMessage());
                    continue;
                }
            }
        }

        foreach ($catalogItems as $category) {
            $catalog = Catalog::whereId($category['id'])->firstOrFail();
            foreach (array_filter($category['child']) as $item) {
                $this->parseCatalogProduct($catalog, $item['link']);
            }
        }
    }

    private function parseCatalogProduct($catalogChild, $link)
    {
        if ($document = file_get_contents($link)) {
            $crawler = new Crawler($document);

            $this->info($link);

            $image = false;
            $name = $crawler->filter('h1.ty-product-block-title')->first()->text();

            $price = 0;
            if ($crawler->filter('.ty-price .ty-price-num')->count()) {
                $price = trim($crawler->filter('.ty-price .ty-price-num')->first()->html());
                $price = preg_replace('/[^0-9]/', '', $price);
            }

            if (!$price) {
                $price = 0;
            }

            if ($crawler->filter('.ty-product-bigpicture .cm-image-previewer img')->count()) {
                $image = $crawler->filter('.ty-product-bigpicture .cm-image-previewer img')->first()->attr('src');
            }

            $fullText = '';
            $text = '';
            if ($crawler->filter('.ty-tabs')->count()) {
                $text = trim($crawler->filter('.ty-tabs__content #content_description')->first()->html());
                $fullText = $crawler->filter('.ty-tabs__content #content_features')->first()->html();

                $text = preg_replace("!<a.*?href=\"?'?([^ \"'>]+)\"?'?.*?>(.*?)</a>!is", '$2', $text);
                $fullText = preg_replace("!<a.*?href=\"?'?([^ \"'>]+)\"?'?.*?>(.*?)</a>!is", '$2', $fullText);
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
            $catalogProduct->label = 'vezuviy';

            $alias = Str::slug($catalogProduct->name);

            $catalogProduct->alias = $alias;

            if (!CatalogProduct::whereAlias($alias)->exists() && $catalogProduct->save() && $image) {
                $imageNew = explode('/', $image);

                $name = Str::random(40);

                $ext = explode('.', end($imageNew));

                $path = Storage::path('public/vezuviy') . '/' . $name . '.' . end($ext);

                try {
                    if (File::copy($image, $path)) {
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
                                $font->size(50);
                                $font->color(array(255, 255, 255, 0.6));
                                $font->align('center');
                                $font->valign('middle');
                                $font->angle(45);
                            })->save(Storage::path('public/vezuviy') . '/' . $name . '.' . end($ext));
                        }
                    }
                } catch (\Exception $exception) {
                    $this->info($exception->getMessage());
                }
            }
        }
    }
}
