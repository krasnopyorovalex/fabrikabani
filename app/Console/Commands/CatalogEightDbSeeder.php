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

class CatalogEightDbSeeder extends Command
{
    private const BASE_URL = "https://tulikivi.ru";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:catalog-eight';

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
        $document = file_get_contents(app_path('Console/Commands/data/tulikivi.html'));
        $crawler = new Crawler($document);

        $links = $crawler->filter('.catalog-index-sections-ml-col')->each(static function (Crawler $node) {
            $category = trim($node->filter('a.catalog-index-section-ml-name')->first()->text());

            $child = $node->filter('.catalog-index-section-ml-subsections a')->each(static function(Crawler $node) {
                $name = str_replace('<br>', ' ', preg_replace('/\s\s+/', ' ', trim($node->text())));

                return $name .'#'. self::BASE_URL . $node->attr('href');
            });

            return [
                $category => $child
            ];
        });

        if ($links) {
            //$links = array_slice($links, 1);

            foreach ($links as $categories) {
                foreach ($categories as $category => $child) {
                    $alias = str_slug($category);

                    $catalog = new Catalog();
                    $catalog->name = $category;
                    $catalog->parent_id = 302;
                    $catalog->alias = $alias;
                    $catalog->title = $catalog->name . ' | Всё для бани';
                    $catalog->description = $catalog->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';

                    if (!Catalog::where('alias', $catalog->alias)->exists()) {
                        $catalog->save();
                    } else {
                        $catalog = Catalog::where('alias', $catalog->alias)->firstOrFail();
                    }

                    foreach ($child as $item) {
                        if (!$item) {
                            continue;
                        }

                        [$childName, $childUrl] = explode('#', $item);

                        $aliasChild = str_slug($childName.'-tulikivi');

                        if (Catalog::where('alias', $aliasChild)->exists() && $childName === 'Сопутствующие товары') {
                            $aliasChild .= '-pechi';
                        }

//                        if (!Catalog::where('alias', $aliasChild)->exists()) {
//                            $catalogChild = new Catalog();
//
//                            $catalogChild->name = $childName;
//                            $catalogChild->parent_id = $catalog->id;
//                            $catalogChild->alias = $aliasChild;
//                            $catalogChild->title = $catalogChild->name . ' | Всё для бани';
//                            $catalogChild->description = $catalogChild->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';
//
//                            $catalogChild->save();
//                        } else {
                            $catalogChild = Catalog::where('alias', $aliasChild)->first();
//                        }

                        $this->parseCategory($catalogChild, $childUrl);
                        $this->parseCategory($catalogChild, $childUrl . '?PAGEN_1=2');
                    }
                }
            }
        }
    }

    private function parseCategory($catalogChild, $url)
    {
        $document = file_get_contents($url);
        $crawler = new Crawler($document);

        $links = $crawler->filter('.catalog-block-view__item a.thumb')->each(function (Crawler $node) {
            return self::BASE_URL . $node->attr('href');
        });

        if ($links) {
            foreach (array_unique($links) as $link) {
                $this->parseCatalogProduct($catalogChild, $link);
                //break;
            }
        }
    }

    /**
     * @param $catalogChild
     * @param $link
     */
    private function parseCatalogProduct($catalogChild, $link)
    {
        $document = file_get_contents($link);
        if ($document) {
            $crawler = new Crawler($document);

            $this->info($link);

            $price = 0;
            $image = false;
            $name = $crawler->filter('#pagetitle')->first()->text();

            if (preg_match_all('#new JCCatalogElement\((.*)\);#', $crawler->html(), $matches)) {
                $rrr = str_replace('\'', '"', $matches[1][0]);
                $rrr = preg_replace('/\s\s+/', ' ', trim($rrr));
                $rrr = json_decode(str_replace('\'', '"', $rrr), true);

                if (isset($rrr['OFFERS'][0])) {
                    $product = $rrr['OFFERS'][0];

                    $price = $product['BASIS_PRICE']['VALUE_VAT'];
                    $image = $product['DETAIL_PICTURE']['SRC'];
                }
            }

            if ($crawler->filter('.prices_block .price_value')->count()) {
                $price = trim($crawler->filter('.prices_block .price_value')->first()->html());
                $price = preg_replace('/[^0-9]/', '', $price);

            }

            if (CatalogProduct::whereAlias(Str::slug($name))->exists()) {
                $catProd = CatalogProduct::whereAlias(Str::slug($name))->first();
                $this->info($catProd->name . ': ' . $catProd->price);

                $catProd->update(['price' => $price]);

                $catProd->fresh();
                $this->info($catProd->name . ': ' . $catProd->price);
            } else {
                if ($crawler->filter('.product-detail-gallery__link')->count()) {
                    $image = $crawler->filter('.product-detail-gallery__link')->first()->attr('href');
                }

                if (!$image && $crawler->filter('.first_sku_picture')->count()) {
                    $image = $crawler->filter('.first_sku_picture')->first()->attr('href');
                }

                $fullText = '';
                $text = '';
                if ($crawler->filter('.tabs')->count()) {
                    $textDesc = trim($crawler->filter('#desc .content')->first()->html());
                    $textTchar = $crawler->filter('#tchar')->first()->html();
                    $textExp = $crawler->filter('#exp')->first()->html();

                    $text = preg_replace("!<a.*?href=\"?'?([^ \"'>]+)\"?'?.*?>(.*?)</a>!is", '$2',  $textDesc);
                    $textTchar = preg_replace('/\s\s+/', ' ', trim($textTchar));
                    $textExp = preg_replace('/\s\s+/', ' ', trim($textExp));

                    $fullText = $textTchar . $textExp;
                } elseif($crawler->filter('.product-chars .generic-text')->count()) {
                    $text = $crawler->filter('.product-chars .generic-text')->first()->html();
                } elseif($crawler->filter('.catalog-element-custom-grid-main')->count()) {
                    $text = $crawler->filter('.catalog-element-custom-grid-main')->first()->html();
                }

                $catalogProduct = new CatalogProduct();
                $catalogProduct->catalog_id = $catalogChild->id;
                $catalogProduct->name = $name;
                $catalogProduct->title = $catalogProduct->name . ' | Всё для бани';
                $catalogProduct->description = $catalogProduct->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';
                $catalogProduct->price = str_replace(' ', '', $price);
                $catalogProduct->text = $text;
                $catalogProduct->not_include_delivery = 1;
                $catalogProduct->on_request = ($price === 0);
                $catalogProduct->props = $fullText;

                if ($catalogProduct->name === 'Дровяная печь TK 550/2 Tulikivi для бани и сауны') {
                    return;
                }

                $alias = Str::slug($catalogProduct->name);

                $existsProduct = CatalogProduct::where('alias', $alias)->exists();

                if ($existsProduct) {
                    while (CatalogProduct::where('alias', $alias)->exists()) {
                        $alias .= '-' . random_int(1, 1000);
                    }
                }

                $catalogProduct->alias = $alias;

                if ($catalogProduct->save() && $image) {
                    $imageNew = explode('/', $image);

                    $name = Str::random(40);

                    $ext = explode('.', end($imageNew));

                    $path = Storage::path('public/tuliviki') . '/' . $name . '.' . end($ext);

                    try {
                        if (File::copy(self::BASE_URL . $image, $path)) {
                            $newImage = new Image();
                            $newImage->path = '/storage/tuliviki/' . $name . '.' . end($ext);
                            $newImage->imageable_type = CatalogProduct::class;
                            $newImage->imageable_id = $catalogProduct->id;
                            $newImage->alt = $catalogProduct->name;

                            if ($newImage->save()) {
                                $im = (new ImageManager())->make($path);

                                $imHeight = $im->height();
                                $imWidth = $im->width();

                                $im->text('fabrikabani-krym.ru', abs($imWidth/2), abs($imHeight/2), static function($font) {
                                    $font->file(public_path('fonts/Arial-Black.ttf'));
                                    $font->size(22);
                                    $font->color(array(255, 255, 255, 0.6));
                                    $font->align('center');
                                    $font->valign('middle');
                                    $font->angle(45);
                                })->save(Storage::path('public/tuliviki') . '/' . $name . '.' . end($ext));
                            }
                        }
                    } catch (\Exception $exception) {
                        $this->info($exception->getMessage());
                    }
                }
            }
        }
    }
}
