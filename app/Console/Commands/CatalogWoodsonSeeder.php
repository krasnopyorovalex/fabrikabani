<?php

namespace App\Console\Commands;

use App\Catalog;
use App\CatalogProduct;
use App\Domain\Catalog\Commands\DeleteCatalogCommand;
use App\Domain\CatalogProduct\Commands\DeleteCatalogProductCommand;
use App\Image;
use File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use SimpleXMLElement;
use Storage;

class CatalogWoodsonSeeder extends Command
{
    private const URL = "https://woodson.ru/bitrix/catalog_export/yandex_531726.php";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:catalog-woodson';

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
        //$this->clearCatalog();

        Log::info('Start update: ' . date('m.d.Y H:i:s'));

        $xml = file_get_contents(self::URL);
        $catalog = new SimpleXMLElement($xml);

        $this->parseCategories($catalog);

        $this->parseProducts($catalog);

        $this->info('Well done!');

        Log::info('Stop update: ' . date('m.d.Y H:i:s'));
    }

    private function parseCategories($catalog)
    {
        $idxPos = 120;
        $categories = [348, 298, 332];

        foreach ($catalog->shop->categories[0] as $category) {
            $existCatalog = isset($category['parentId'])
                ? Catalog::query()
                    ->where('bitrix_id', $category['parentId'])
                    ->first()
                : null;

            $categories[] = (int)$category['id'];

            Catalog::updateOrCreate([
                'bitrix_id' => $category['id'],
                'alias' => Str::slug($category).'-'.$category['id']
            ], [
                'bitrix_parent_id' => $category['parentId'] ?? null,
                'title' => $category . ' | Всё для бани',
                'description' => $category . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93',
                'name' => $category ,
                'pos' => $idxPos,
                'parent_id' => $existCatalog ? $existCatalog['id'] : null
            ]);

            $idxPos++;
        }

        $catalog = Catalog::query()->select(['id'])->whereNotIn('bitrix_id', $categories)->get();
        foreach ($catalog as $catalogId) {
            dispatch(new DeleteCatalogCommand($catalogId->id));
        }
    }

    private function parseProducts(SimpleXMLElement $catalog)
    {
        $products = [];
        foreach ($catalog->shop->offers[0] as $offer) {
            if (!$category = Catalog::query()->where('bitrix_id', $offer->categoryId)->first()) {
                continue;
            }

            $props = '<div class="catalog-detail-tab-content"><table class="props_list">';
            foreach ($offer->param as $param) {
                $props .= '<tr>
                            <td class="char_name"><div class="props_item">'.$param['name'].'</div></td>
                            <td class="char_value"><span>'.$param.'</span></td>
                            </tr>';
            }
            $props .= '</table></div>';

            $products[] = (int)$offer['id'];

            /** @var CatalogProduct $catalogProduct */
            $catalogProduct = CatalogProduct::updateOrCreate([
                'alias' => Str::slug($offer->name) . '-' . (int)$offer['id'],
                'bitrix_id' => (int)$offer['id']
            ], [
                'name' => $offer->name,
                'title' => $offer->name . ' | Всё для бани',
                'description' => $offer->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93',
                'price' => $offer->price + $offer->price * 5 / 100,
                'catalog_id' => $category->id,
                'text' => '<p>'.$offer->description.'</p>',
                'props' => $props
            ]);

            $path = Storage::path('public/images');
            if ($offer->picture) {
                $image = pathinfo($offer->picture, PATHINFO_BASENAME);

                if ($catalogProduct->image && pathinfo($catalogProduct->image->path, PATHINFO_BASENAME) === $image) {
                    continue;
                }

                try {
                    File::copy($offer->picture, sprintf('%s/%s', $path, $image));

                    $newImage = new Image();
                    $newImage->path = '/storage/images/' . $image;
                    $newImage->imageable_type = CatalogProduct::class;
                    $newImage->imageable_id = $catalogProduct->id;
                    $newImage->alt = $catalogProduct->name;

                    if ($newImage->save()) {
                        $im = (new ImageManager())->make(sprintf('%s/%s', $path, $image));

                        $imHeight = $im->height();
                        $imWidth = $im->width();

                        $im->text('fabrikabani-krym.ru', abs($imWidth / 2), abs($imHeight / 2), static function ($font) {
                            $font->file(public_path('fonts/Arial-Black.ttf'));
                            $font->size(40);
                            $font->color(array(255, 255, 255, 0.6));
                            $font->align('center');
                            $font->valign('middle');
                            $font->angle(45);
                        })->save(sprintf('%s/%s', $path, $image));
                    }
                } catch (\Exception $exception) {
                    Log::error($exception->getMessage());
                }
            }

            //break;
        }

        $catalogProducts = CatalogProduct::query()->select(['id'])->whereNotIn('bitrix_id', $products)->get();

        foreach ($catalogProducts as $catalogProductId) {
            dispatch(new DeleteCatalogProductCommand($catalogProductId->id));
        }
    }

    private function clearCatalog()
    {
        Catalog::query()
            ->whereNotIn('id', [348, 298, 332])
            ->where('parent_id', null)
            ->with(['catalogs.products', 'products', 'catalogs'])
            ->chunk(10, function ($toDelete) {
                foreach ($toDelete as $catalog) {
                    if ($catalog->catalogs) {
                        foreach ($catalog->catalogs as $catalogChild) {
                            if ($catalogChild->products) {
                                foreach ($catalogChild->products as $productChild) {
                                    $productChild->delete();
                                }
                            }
                            $catalogChild->delete();
                        }
                    }

                    if ($catalog->products) {
                        foreach ($catalog->products as $product) {
                            $product->delete();
                        }
                    }
                    $catalog->delete();
                }
            });
    }
}
