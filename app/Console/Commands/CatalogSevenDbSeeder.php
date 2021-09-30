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

/**
 * Class CatalogSevenDbSeeder
 * @package App\Console\Commands
 */
class CatalogSevenDbSeeder extends Command
{
    private const BASE_URL = "https://www.easysteam.ru";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:catalog-seven';

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
        $document = file_get_contents(self::BASE_URL);
        $crawler = new Crawler($document);

//        $links = $crawler->filter('.header-menu .header-submenu-section')->each(static function (Crawler $node) {
//            $category = preg_replace('/\s\s+/', ' ', trim($node->filter('a.header-submenu-section__title')->first()->text()));
//
//            $category = str_replace([' русской ', ' скидка 10% на всю категорию'], '', $category);
//
//            $child = false;
//
//            if (!strstr($category, 'коммерческих')) {
//                $child = $node->closest('.header-submenu-section')->filter('ul.header-submenu-section__list li a .header-product__title')->each(static function(Crawler $node) {
//                    $name = str_replace('<br>', ' ', preg_replace('/\s\s+/', ' ', trim($node->text())));
//
//                    if (!in_array($name, ['камни для каменки', 'дымоходы'])) {
//                        return $name .'#'. $node->closest('a')->attr('href');
//                    }
//
//                    return false;
//                });
//            }
//
//            if ($category === 'Печи для коммерческих бань') {
//                $child = [$category.'#https://www.easysteam.ru/products/kommercheskiye_pechi/kommercheskiye_pechi_dlya_bani'];
//            }
//
//            if ($category === 'Печи для коммерческих бань и саун скидка 10% на всю категорию') {
//                $child = [$category.'#https://www.easysteam.ru/products/kommercheskiye_pechi/kommercheskiye_pechi_dlya_sauny'];
//            }
//
//            return [
//                $category => $child
//            ];
//        });

//        array_push($links[0]['ПЕЧИ ДЛЯ БАНИ'], 'Печи для бани и сауны#https://www.easysteam.ru/products/pechi_dlya_sauny');
//        $links = array_slice($links, 0, 3);
        $links[] = ['Дополнительное оборудование' => [
            'Конвекционные дверки#https://easysteam.ru/products/optional-equipment/konvektsionnye-dverki',
            'Изделия из природного камня#https://easysteam.ru/products/optional-equipment/izdeliya-iz-prirodnogo-kamnya',
            'Баки для воды#https://easysteam.ru/products/optional-equipment/baki-dlya-vody',
            'Газовые горелки#https://easysteam.ru/products/optional-equipment/gazovye-gorelki',
            'Экономайзеры#https://easysteam.ru/products/optional-equipment/ekonomayzery',
            'Лист перекрытия#https://easysteam.ru/products/optional-equipment/list-perekrytiya',
            'Теплообменные устройства#https://easysteam.ru/products/optional-equipment/teploobmennye-ustroystva',
        ]];

        if ($links) {
            foreach ($links as $categories) {
                foreach ($categories as $category => $child) {
                    $alias = str_slug(str_replace([' русской ', ' скидка 10% на всю категорию'], '', $category));

                    $catalog = new Catalog();
                    $catalog->name = $category;
                    $catalog->parent_id = 277;
                    $catalog->alias = $alias;
                    $catalog->title = $catalog->name . ' | Всё для бани';
                    $catalog->description = $catalog->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';

                    if ($catalog->name === 'Дополнительное оборудование') {
                        $catalog->alias = $catalog->alias . '-easysteam';
                    }

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

                        $aliasChild = str_slug($childName.'-easysteam');

                        if (!Catalog::where('alias', $aliasChild)->exists()) {
                            $catalogChild = new Catalog();

                            if ($category !== 'Печи для коммерческих бань' && $category !== 'Печи для коммерческих бань и саун скидка 10% на всю категорию') {
                                $catalogChild->parent_id = $catalog->id;
                            }

                            $catalogChild->name = $childName;
                            $catalogChild->alias = $aliasChild;
                            $catalogChild->title = $catalogChild->name . ' | Всё для бани';
                            $catalogChild->description = $catalogChild->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';

                            $catalogChild->save();
                        } else {
                            $catalogChild = Catalog::where('alias', $aliasChild)->first();
                        }

                        $this->parseCategory($catalogChild, $childUrl);
                    }
                }
            }
        }
    }

    private function parseCategory($catalogChild, $url)
    {
        $document = file_get_contents($url);
        $crawler = new Crawler($document);

        $links = $crawler->filter('a.product-card__link')->each(function (Crawler $node) {
            return $node->attr('href');
        });

        if ($links) {
            foreach ($links as $link) {
                $this->parseCatalogProduct($catalogChild, $link);
            }
        }
    }

    /**
     * @param $catalogChild
     * @param $link
     */
    private function parseCatalogProduct($catalogChild, $link)
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', $link, [
            'allow_redirects' => false
        ]);

        if ($response->getStatusCode() === 301) {
            $link = $response->getHeader('Location')[0];
        }

        $document = file_get_contents($link);
        $crawler = new Crawler($document);

        $this->info($link);
        $price = $crawler->filter('.product-card__price .js-product-price')->first()->text();

        $text = $crawler->filter('p.item-description-text')->first()->text();

        $textProps = $crawler->filter('.product-info-list-wrap')->first()->html();
        $textProps = str_replace(
            [
                '/files/shares/Documents/Passports/',
                '/photos/shares/Documents/Passports/',
                '/files/shares/docs/pasporta-pechey/',
                '/photos/shares/docs/pasporta-pechey/',
                '/photos/shares/Shemy%20pehci/'
            ],
            '/storage/easysteam/',
            $textProps
        );

        $textProps = str_replace('bg-white ', '', $textProps);

        $optionsBox = $crawler->filter('.card-option-list-wrap');
        $options = '';
        $optionsImages = false;
        if (count($optionsBox)) {
            $options = $optionsBox->first()->html();
            $optionsImagesBox = $optionsBox->filter('img');

            $options = str_replace('/photos/shares/ikomki_v_nabory/', '/storage/easysteam/icons/', $options);

            if ($optionsImagesBox) {
                $optionsImages = $optionsImagesBox->each(function (Crawler $node) {
                    return $node->attr('src');
                });
            }
        }

        $fullText = $options . '<div class="product-info-list-wrap">' . $textProps . '</div>';

        $catalogProduct = new CatalogProduct();
        $catalogProduct->catalog_id = $catalogChild->id;
        $catalogProduct->name = $crawler->filter('h3.item-description-title')->first()->text();
        $catalogProduct->title = $catalogProduct->name . ' | Всё для бани';
        $catalogProduct->description = $catalogProduct->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';
        $catalogProduct->price = str_replace(' ', '', $price);
        $catalogProduct->text = '<p class="item-description-text">' . $text . '</p>';
        $catalogProduct->not_include_delivery = 1;
        $catalogProduct->props = $fullText;

        $alias = Str::slug($catalogProduct->name);
        if (CatalogProduct::where('alias', $alias)->exists()) {
            $catalogProduct->alias = $alias .'-' . random_int(0,10);
        } else {
            $catalogProduct->alias = $alias;
        }

        $pdfList = $crawler->filter('.col-option-docs .product-video-wrap')->each(function (Crawler $node) {
            return $node->filter('a')->first()->attr('href') . '#' . $node->filter('img')->first()->attr('src');
        });

        $image = $crawler->filter('.card .card-img-wrap img.lazyload')->first()->attr('src');
        $imageSchemeMaybe = $crawler->filter('.product-info-list-wrap .container-options img.w-100');
        $imageScheme = false;
        if (count($imageSchemeMaybe)) {
            $imageScheme = $imageSchemeMaybe->first()->attr('src');
        }

        if ($catalogProduct->save() && $image) {
            $imageNew = explode('/', $image);

            $name = Str::random(40);

            $ext = explode('.', end($imageNew));

            $path = Storage::path('public/easysteam') . '/' . $name . '.' . end($ext);

            if (File::copy(self::BASE_URL . $image, $path)) {
                $newImage = new Image();
                $newImage->path = '/storage/easysteam/' . $name . '.' . end($ext);
                $newImage->imageable_type = CatalogProduct::class;
                $newImage->imageable_id = $catalogProduct->id;
                $newImage->alt = $catalogProduct->name;

                if ($newImage->save()) {
                    $im = (new ImageManager())->make($path);

                    $imHeight = $im->height();
                    $imWidth = $im->width();

                    $im->text('fabrikabani-krym.ru', abs($imWidth/2), abs($imHeight/2), static function($font) {
                        $font->file(public_path('fonts/Arial-Black.ttf'));
                        $font->size(50);
                        $font->color(array(255, 255, 255, 0.6));
                        $font->align('center');
                        $font->valign('middle');
                        $font->angle(45);
                    })->save(Storage::path('public/easysteam') . '/' . $name . '.' . end($ext));
                }
            }

            if (count($pdfList)) {
                foreach ($pdfList as $pdf) {
                    [$image, $preview] = explode('#', $pdf);

                    $imageChunks = explode('/', $image);
                    $previewChunks = explode('/', $preview);

                    [$imageName, $ext] = explode('.', end($imageChunks));
                    [$previewName, $extPreview] = explode('.', end($previewChunks));

                    $path = Storage::path('public/easysteam') . '/' . $imageName . '.' . $ext;
                    $pathPreview = Storage::path('public/easysteam') . '/' . $previewName . '.' . $extPreview;

                    File::copy(self::BASE_URL . $image, $path);
                    File::copy(self::BASE_URL . $preview, $pathPreview);
                }
            }

            if ($imageScheme) {
                $imageSchemeChunks = explode('/', $imageScheme);

                [$imageSchemeName, $ext] = explode('.', end($imageSchemeChunks));
                $path = Storage::path('public/easysteam') . '/' . $imageSchemeName . '.' . $ext;
                File::copy(self::BASE_URL . $imageScheme, $path);
            }

            if ($optionsImages) {
                foreach ($optionsImages as $optionsImage) {
                    $newImgChunks = explode('/', $optionsImage);

                    [$imageNewName, $ext] = explode('.', end($newImgChunks));
                    $path = Storage::path('public/easysteam/icons') . '/' . $imageNewName . '.' . $ext;
                    File::copy(self::BASE_URL . $optionsImage, $path);
                }
            }
        }
    }
}
