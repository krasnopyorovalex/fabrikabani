<?php

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

class CatalogThirdDbSeeder extends Command
{
    private const BASE_URL = "https://kurna-tut.ru/";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:catalog-third';

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
        $oborydovanieDlyaHamama = Catalog::where('id', 22)->with(['catalogs.products'])->firstOrFail();

        foreach ($oborydovanieDlyaHamama->catalogs as $catalog) {
            foreach ($catalog->products as $product) {
                $product->delete();
            }
            $catalog->delete();
        }
        $oborydovanieDlyaHamama->delete();

        $this->parseCategories();

        $this->info('Well done!');
    }

    /**
     * @throws \Exception
     */
    private function parseCategories(): void
    {
        //var links = [];
        //$('#main .tovar-item-wr').each(function(){
        //   links.push({'img': $(this).find('img').attr('src'), 'name': $(this).find('.tovar-info-name').text(), 'price': parseInt($(this).find('.tovar-info-price').text())})
        //});
        //
        //$str = '[';
        //links.map(function(item){
        //	$str += '[\'price\' => ' + item.price + ', \'image\' => ' + '\'' + item.img.replace('.webp','.jpg') + '\'' + ', \'name\' => ' + '\'' + item.name + '\'' + '],';
        //});
        //$str += ']';

        $kyrni = [['price' => 22800, 'image' => 'https://kurna-tut.ru/wp-content/uploads/714dc78e2c0b59bb8e66c468e7a8a54d.webp', 'name' => 'Раковина мраморная РМ01'],['price' => 26400, 'image' => 'https://kurna-tut.ru/wp-content/uploads/40083c4bebe9eedf0f94c38a92883f9f.webp', 'name' => 'Раковина мраморная РМ02'],['price' => 24000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/f1ca4dba01bd95c9547d7d86d78351e3.webp', 'name' => 'Раковина мраморная РМ03'],['price' => 27600, 'image' => 'https://kurna-tut.ru/wp-content/uploads/4d0bab1f45316105850dfd75634e560e.webp', 'name' => 'Раковина мраморная РМ04'],['price' => 24000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/a12d4236be22b43ef1b8f8d8d0e5dc19.webp', 'name' => 'Раковина мраморная РМ05'],['price' => 27600, 'image' => 'https://kurna-tut.ru/wp-content/uploads/РМ06-1500x1000.webp', 'name' => 'Раковина мраморная РМ06'],['price' => 25200, 'image' => 'https://kurna-tut.ru/wp-content/uploads/d3c9d5091fd05058a6dff3dfbe041d58.webp', 'name' => 'Раковина мраморная РМ07'],['price' => 28800, 'image' => 'https://kurna-tut.ru/wp-content/uploads/e773bf1bac41cedf378857e636ecd9ad.webp', 'name' => 'Раковина мраморная РМ08'],['price' => 26400, 'image' => 'https://kurna-tut.ru/wp-content/uploads/a79c3e07d4011731d4d087fafb1e4ec7.webp', 'name' => 'Раковина мраморная РМ09'],['price' => 30000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/bf2dd90210e87f737277396f2e4a7a53.webp', 'name' => 'Раковина мраморная РМ10'],['price' => 27600, 'image' => 'https://kurna-tut.ru/wp-content/uploads/4ca89f05f9de6bd18a4382e5dcfe3499.webp', 'name' => 'Раковина мраморная РМ11'],['price' => 31200, 'image' => 'https://kurna-tut.ru/wp-content/uploads/5b40e961be732777f7c6bc63e88c531e.webp', 'name' => 'Раковина мраморная РМ12'],['price' => 28800, 'image' => 'https://kurna-tut.ru/wp-content/uploads/РМ13-1500x1000.webp', 'name' => 'Раковина мраморная РМ13'],['price' => 32400, 'image' => 'https://kurna-tut.ru/wp-content/uploads/cd4c65daf08d3150bfbbcd9ea7068211.webp', 'name' => 'Раковина мраморная РМ14'],['price' => 30000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/РМ15-1500x1000.webp', 'name' => 'Раковина мраморная РМ15'],['price' => 33600, 'image' => 'https://kurna-tut.ru/wp-content/uploads/6688c156ce24ce43a90d9e5c644320cf.webp', 'name' => 'Раковина мраморная РМ16'],['price' => 28800, 'image' => 'https://kurna-tut.ru/wp-content/uploads/РМ17-1500x1000.webp', 'name' => 'Раковина мраморная РМ17'],['price' => 32400, 'image' => 'https://kurna-tut.ru/wp-content/uploads/4af8d9feb35bf60a4e620a6079898d14.webp', 'name' => 'Раковина мраморная РМ18'],['price' => 30000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/c3716d126f6cc1aa63378e45fc7fc732.webp', 'name' => 'Раковина мраморная РМ19'],['price' => 33600, 'image' => 'https://kurna-tut.ru/wp-content/uploads/1498aa47f0bbdb55505b881fc8c63d77.webp', 'name' => 'Раковина мраморная РМ20'],['price' => 36000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/7194c6054aca9595e41c82c45f4d2ff4.webp', 'name' => 'Раковина мраморная РМ21'],['price' => 39600, 'image' => 'https://kurna-tut.ru/wp-content/uploads/РМ22-1500x1000.webp', 'name' => 'Раковина мраморная РМ22'],['price' => 37200, 'image' => 'https://kurna-tut.ru/wp-content/uploads/09d5c130704eaebed8993fa2e93b6aa9.webp', 'name' => 'Раковина мраморная РМ23'],['price' => 40800, 'image' => 'https://kurna-tut.ru/wp-content/uploads/РМ24-1500x1000.webp', 'name' => 'Раковина мраморная РМ24']];
        $parogeneratori = [['price' => 175328, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Tylo_Steam_Generator_Home_2.jpg', 'name' => 'Парогенератор Tylo Steam Home 3/6/9 kw'],['price' => 215884, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Tylo_Steam_Generator_commercial_2.jpg', 'name' => 'Парогенератор Tylo Steam Commercial 9 kw'],['price' => 255068, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Tylo_Steam_Generator_commercial_2.jpg', 'name' => 'Парогенератор Tylo Steam Commercial 12 kw'],['price' => 294251, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Tylo_Steam_Generator_commercial_2.jpg', 'name' => 'Парогенератор Tylo Steam Commercial 15 kw'],['price' => 56155, 'image' => 'https://kurna-tut.ru/wp-content/uploads/pult_elite_1.jpg', 'name' => 'Пульт управления Tylo Elite'],['price' => 7764, 'image' => 'https://kurna-tut.ru/wp-content/uploads/steam_outlet_grace900.jpg', 'name' => 'Паровая форсунка Tylo GRACE'],['price' => 26820, 'image' => 'https://kurna-tut.ru/wp-content/uploads/bahia1.jpg', 'name' => 'Паровая форсунка Tylo Bahia Home'],['price' => 35380, 'image' => 'https://kurna-tut.ru/wp-content/uploads/IMG_2329-voorkant.jpg', 'name' => 'Устройство ароматизации Tylo FlavourLux Mini'],['price' => 52180, 'image' => 'https://kurna-tut.ru/wp-content/uploads/fl_l250.jpg', 'name' => 'Устройство ароматизации Tylo FlavourLux FL-L250'],['price' => 43642, 'image' => 'https://kurna-tut.ru/wp-content/uploads/FLAVOURLUX_EC_11jpg.jpg', 'name' => 'Устройство ароматизации Tylo FlavourLux FL-EC250']];
        $komplekty = [['price' => 175328, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Tylo_Steam_Generator_Home_2.jpg', 'name' => 'Парогенератор Tylo Steam Home 3/6/9 kw'],['price' => 215884, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Tylo_Steam_Generator_commercial_2.jpg', 'name' => 'Парогенератор Tylo Steam Commercial 9 kw'],['price' => 255068, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Tylo_Steam_Generator_commercial_2.jpg', 'name' => 'Парогенератор Tylo Steam Commercial 12 kw'],['price' => 294251, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Tylo_Steam_Generator_commercial_2.jpg', 'name' => 'Парогенератор Tylo Steam Commercial 15 kw'],['price' => 56155, 'image' => 'https://kurna-tut.ru/wp-content/uploads/pult_elite_1.jpg', 'name' => 'Пульт управления Tylo Elite'],['price' => 7764, 'image' => 'https://kurna-tut.ru/wp-content/uploads/steam_outlet_grace900.jpg', 'name' => 'Паровая форсунка Tylo GRACE'],['price' => 26820, 'image' => 'https://kurna-tut.ru/wp-content/uploads/bahia1.jpg', 'name' => 'Паровая форсунка Tylo Bahia Home'],['price' => 35380, 'image' => 'https://kurna-tut.ru/wp-content/uploads/IMG_2329-voorkant.jpg', 'name' => 'Устройство ароматизации Tylo FlavourLux Mini'],['price' => 52180, 'image' => 'https://kurna-tut.ru/wp-content/uploads/fl_l250.jpg', 'name' => 'Устройство ароматизации Tylo FlavourLux FL-L250'],['price' => 43642, 'image' => 'https://kurna-tut.ru/wp-content/uploads/FLAVOURLUX_EC_11jpg.jpg', 'name' => 'Устройство ароматизации Tylo FlavourLux FL-EC250']];
        $arki = [['price' => 175328, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Tylo_Steam_Generator_Home_2.jpg', 'name' => 'Парогенератор Tylo Steam Home 3/6/9 kw'],['price' => 215884, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Tylo_Steam_Generator_commercial_2.jpg', 'name' => 'Парогенератор Tylo Steam Commercial 9 kw'],['price' => 255068, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Tylo_Steam_Generator_commercial_2.jpg', 'name' => 'Парогенератор Tylo Steam Commercial 12 kw'],['price' => 294251, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Tylo_Steam_Generator_commercial_2.jpg', 'name' => 'Парогенератор Tylo Steam Commercial 15 kw'],['price' => 56155, 'image' => 'https://kurna-tut.ru/wp-content/uploads/pult_elite_1.jpg', 'name' => 'Пульт управления Tylo Elite'],['price' => 7764, 'image' => 'https://kurna-tut.ru/wp-content/uploads/steam_outlet_grace900.jpg', 'name' => 'Паровая форсунка Tylo GRACE'],['price' => 26820, 'image' => 'https://kurna-tut.ru/wp-content/uploads/bahia1.jpg', 'name' => 'Паровая форсунка Tylo Bahia Home'],['price' => 35380, 'image' => 'https://kurna-tut.ru/wp-content/uploads/IMG_2329-voorkant.jpg', 'name' => 'Устройство ароматизации Tylo FlavourLux Mini'],['price' => 52180, 'image' => 'https://kurna-tut.ru/wp-content/uploads/fl_l250.jpg', 'name' => 'Устройство ароматизации Tylo FlavourLux FL-L250'],['price' => 43642, 'image' => 'https://kurna-tut.ru/wp-content/uploads/FLAVOURLUX_EC_11jpg.jpg', 'name' => 'Устройство ароматизации Tylo FlavourLux FL-EC250']];
        $panno = [['price' => 15750, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Панно-501-40х60.webp', 'name' => 'Панно 501 (40х60)'],['price' => 15750, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Панно-513-40х60.webp', 'name' => 'Панно 513 (40х60)'],['price' => 15750, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Панно-611-40х60.webp', 'name' => 'Панно 611 (40х60)'],['price' => 15750, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Панно-616-40х60.webp', 'name' => 'Панно 616 (40х60)'],['price' => 45150, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Панно-631-60х120.webp', 'name' => 'Панно 631 (60х120)'],['price' => 30450, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Панно-634-60х80.webp', 'name' => 'Панно 634 (60х80)'],['price' => 30450, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Панно-637-60х80.webp', 'name' => 'Панно 637 (60х80)'],['price' => 37800, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Панно-638-60х100.webp', 'name' => 'Панно 638 (60х100)'],['price' => 25200, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Панно-704-40х100.webp', 'name' => 'Панно 704 (40х100)'],['price' => 25200, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Панно-706-40х100.webp', 'name' => 'Панно 706 (40х100)'],['price' => 25200, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Панно-707-40х100.webp', 'name' => 'Панно 707 (40х100)'],['price' => 30450, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Панно-709-40х120.webp', 'name' => 'Панно 709 (40х120)']];
        $svetilniki = [['price' => 14300, 'image' => 'https://kurna-tut.ru/wp-content/uploads/свет-01.webp', 'name' => 'Светильник мраморный МС01'],['price' => 14300, 'image' => 'https://kurna-tut.ru/wp-content/uploads/свет-02.webp', 'name' => 'Светильник мраморный МС02'],['price' => 14300, 'image' => 'https://kurna-tut.ru/wp-content/uploads/свет-03.webp', 'name' => 'Светильник мраморный МС03'],['price' => 14300, 'image' => 'https://kurna-tut.ru/wp-content/uploads/свет-04.webp', 'name' => 'Светильник мраморный МС04'],['price' => 24200, 'image' => 'https://kurna-tut.ru/wp-content/uploads/001-1500x1500.webp', 'name' => 'Светильник кувшин мраморный МС05'],['price' => 24200, 'image' => 'https://kurna-tut.ru/wp-content/uploads/002-1500x1500.webp', 'name' => 'Светильник кувшин мраморный МС06'],['price' => 24200, 'image' => 'https://kurna-tut.ru/wp-content/uploads/003-1500x1500.webp', 'name' => 'Светильник кувшин мраморный МС07'],['price' => 14300, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Светильник-мраморный-МС08-угловой--1500x1500.webp', 'name' => 'Светильник мраморный МС08 угловой'],['price' => 14300, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Светильник-мраморный-МС09-угловой--1500x1500.webp', 'name' => 'Светильник мраморный МС09 угловой'],['price' => 14300, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Светильник-мраморный-МС10-угловой--1500x1500.webp', 'name' => 'Светильник мраморный МС10 угловой'],['price' => 14300, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Светильник-мраморный-МС11-угловой--1500x1500.webp', 'name' => 'Светильник мраморный МС11 угловой'],['price' => 33000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/МС12.png', 'name' => 'Светильник пушка мраморный МС12'],['price' => 35000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/МС13.png', 'name' => 'Светильник пушка мраморный МС13'],['price' => 42000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/мс14.png', 'name' => 'Светильник пушка мраморный МС14'],['price' => 43000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/мс15.png', 'name' => 'Светильник пушка мраморный МС15'],['price' => 61000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/МС16.png', 'name' => 'Светильник пушка мраморный МС16']];
        $kolonni = [['price' => 84000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/см02.png', 'name' => 'Колонна мраморная М02'],['price' => 84000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/см212.png', 'name' => 'Колонна мраморная М0212'],['price' => 47000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/см214.png', 'name' => 'Колонна мраморная М0214'],['price' => 84000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/см_03.png', 'name' => 'Колонна мраморная М03'],['price' => 84000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/см312.png', 'name' => 'Колонна мраморная М0312'],['price' => 47000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/см314.png', 'name' => 'Колонна мраморная М0314']];
        $krani = [['price' => 3850, 'image' => 'https://kurna-tut.ru/wp-content/uploads/0d082a484d0b79f00de6265d14a6bcb3.webp', 'name' => 'Кран для турецкой бани (хамам) 01 античный 1/2\"'],['price' => 3850, 'image' => 'https://kurna-tut.ru/wp-content/uploads/91d473a4b986997ec616672709f19519.webp', 'name' => 'Кран для турецкой бани (хамам) 02 античный 1/2\"'],['price' => 6545, 'image' => 'https://kurna-tut.ru/wp-content/uploads/кран-3.webp', 'name' => 'Кран для турецкой бани (хамам) 03 латунь 1/2\"'],['price' => 6798, 'image' => 'https://kurna-tut.ru/wp-content/uploads/кран-4.webp', 'name' => 'Кран для турецкой бани (хамам) 04 состаренный 1/2\"'],['price' => 7150, 'image' => 'https://kurna-tut.ru/wp-content/uploads/хром.webp', 'name' => 'Кран для турецкой бани (хамам) 05 хром 1/2\"'],['price' => 4510, 'image' => 'https://kurna-tut.ru/wp-content/uploads/sliv2.webp', 'name' => 'Слив для курны хром'],['price' => 4510, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Слив-для-курны-латунь.webp', 'name' => 'Слив для курны латунь'],['price' => 4510, 'image' => 'https://kurna-tut.ru/wp-content/uploads/84b1f3c0ffb58ba27bc20c541845351a.webp', 'name' => 'Слив для курны античный'],['price' => 2860, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Чаша-для-омовения-HTP-71-латунь-малая.webp', 'name' => 'Чаша для омовения HTP-71 латунь малая'],['price' => 3080, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Чаша-для-омовения-HTP-67-латунь-большая.webp', 'name' => 'Чаша для омовения HTP-67 латунь большая'],['price' => 2860, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Чаша-для-омовения-HTG-71-хром-малая.webp', 'name' => 'Чаша для омовения HTG-71 хром малая'],['price' => 3080, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Чаша-для-омовения-HTG-67-хром-большая.webp', 'name' => 'Чаша для омовения HTG-67 хром большая'],['price' => 2860, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Чаша-для-омовения-HTB-71-медь-малая.webp', 'name' => 'Чаша для омовения HTB-71 медь малая'],['price' => 3080, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Чаша-для-омовения-HTB-67-медь-большая.webp', 'name' => 'Чаша для омовения HTB-67 медь большая'],['price' => 660, 'image' => 'https://kurna-tut.ru/wp-content/uploads/Мыло-для-пены-800-гр..webp', 'name' => 'Мыло для пены, 800 гр.']];
        $rakovini = [['price' => 22800, 'image' => 'https://kurna-tut.ru/wp-content/uploads/714dc78e2c0b59bb8e66c468e7a8a54d.webp', 'name' => 'Раковина мраморная РМ01'],['price' => 26400, 'image' => 'https://kurna-tut.ru/wp-content/uploads/40083c4bebe9eedf0f94c38a92883f9f.webp', 'name' => 'Раковина мраморная РМ02'],['price' => 24000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/f1ca4dba01bd95c9547d7d86d78351e3.webp', 'name' => 'Раковина мраморная РМ03'],['price' => 27600, 'image' => 'https://kurna-tut.ru/wp-content/uploads/4d0bab1f45316105850dfd75634e560e.webp', 'name' => 'Раковина мраморная РМ04'],['price' => 24000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/a12d4236be22b43ef1b8f8d8d0e5dc19.webp', 'name' => 'Раковина мраморная РМ05'],['price' => 27600, 'image' => 'https://kurna-tut.ru/wp-content/uploads/РМ06-1500x1000.webp', 'name' => 'Раковина мраморная РМ06'],['price' => 25200, 'image' => 'https://kurna-tut.ru/wp-content/uploads/d3c9d5091fd05058a6dff3dfbe041d58.webp', 'name' => 'Раковина мраморная РМ07'],['price' => 28800, 'image' => 'https://kurna-tut.ru/wp-content/uploads/e773bf1bac41cedf378857e636ecd9ad.webp', 'name' => 'Раковина мраморная РМ08'],['price' => 26400, 'image' => 'https://kurna-tut.ru/wp-content/uploads/a79c3e07d4011731d4d087fafb1e4ec7.webp', 'name' => 'Раковина мраморная РМ09'],['price' => 30000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/bf2dd90210e87f737277396f2e4a7a53.webp', 'name' => 'Раковина мраморная РМ10'],['price' => 27600, 'image' => 'https://kurna-tut.ru/wp-content/uploads/4ca89f05f9de6bd18a4382e5dcfe3499.webp', 'name' => 'Раковина мраморная РМ11'],['price' => 31200, 'image' => 'https://kurna-tut.ru/wp-content/uploads/5b40e961be732777f7c6bc63e88c531e.webp', 'name' => 'Раковина мраморная РМ12'],['price' => 28800, 'image' => 'https://kurna-tut.ru/wp-content/uploads/РМ13-1500x1000.webp', 'name' => 'Раковина мраморная РМ13'],['price' => 32400, 'image' => 'https://kurna-tut.ru/wp-content/uploads/cd4c65daf08d3150bfbbcd9ea7068211.webp', 'name' => 'Раковина мраморная РМ14'],['price' => 30000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/РМ15-1500x1000.webp', 'name' => 'Раковина мраморная РМ15'],['price' => 33600, 'image' => 'https://kurna-tut.ru/wp-content/uploads/6688c156ce24ce43a90d9e5c644320cf.webp', 'name' => 'Раковина мраморная РМ16'],['price' => 28800, 'image' => 'https://kurna-tut.ru/wp-content/uploads/РМ17-1500x1000.webp', 'name' => 'Раковина мраморная РМ17'],['price' => 32400, 'image' => 'https://kurna-tut.ru/wp-content/uploads/4af8d9feb35bf60a4e620a6079898d14.webp', 'name' => 'Раковина мраморная РМ18'],['price' => 30000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/c3716d126f6cc1aa63378e45fc7fc732.webp', 'name' => 'Раковина мраморная РМ19'],['price' => 33600, 'image' => 'https://kurna-tut.ru/wp-content/uploads/1498aa47f0bbdb55505b881fc8c63d77.webp', 'name' => 'Раковина мраморная РМ20'],['price' => 36000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/7194c6054aca9595e41c82c45f4d2ff4.webp', 'name' => 'Раковина мраморная РМ21'],['price' => 39600, 'image' => 'https://kurna-tut.ru/wp-content/uploads/РМ22-1500x1000.webp', 'name' => 'Раковина мраморная РМ22'],['price' => 37200, 'image' => 'https://kurna-tut.ru/wp-content/uploads/09d5c130704eaebed8993fa2e93b6aa9.webp', 'name' => 'Раковина мраморная РМ23'],['price' => 40800, 'image' => 'https://kurna-tut.ru/wp-content/uploads/РМ24-1500x1000.webp', 'name' => 'Раковина мраморная РМ24']];
        $gliptika = [['price' => 396000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/photo_2020-01-13_10-52-07.webp', 'name' => 'Голова льва'],['price' => 396000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/photo_2020-01-13_10-55-57.webp', 'name' => 'Голова медведя'],['price' => 396000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/photo_2020-01-13_10-56-31.webp', 'name' => 'Голова пантеры']];
        $legaki = [['price' => 360000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ01.png', 'name' => 'Массажный стол для хамама ЛМ01 комплект'],['price' => 405000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ02.png', 'name' => 'Массажный стол для хамама ЛМ02 комплект'],['price' => 360000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ03.png', 'name' => 'Массажный стол для хамама ЛМ03 комплект'],['price' => 405000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ04.png', 'name' => 'Массажный стол для хамама ЛМ04 комплект'],['price' => 460000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ05.png', 'name' => 'Массажный стол для хамама ЛМ05 комплект'],['price' => 505000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ06.png', 'name' => 'Массажный стол для хамама ЛМ06 комплект'],['price' => 410000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ07.png', 'name' => 'Массажный стол для хамама ЛМ07 комплект'],['price' => 455000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ08.png', 'name' => 'Массажный стол для хамама ЛМ08 комплект'],['price' => 90000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ09.png', 'name' => 'Массажный стол для хамама ЛМ09'],['price' => 135000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ10.png', 'name' => 'Массажный стол для хамама ЛМ10'],['price' => 90000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ11.png', 'name' => 'Массажный стол для хамама ЛМ11'],['price' => 135000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ12.png', 'name' => 'Массажный стол для хамама ЛМ12'],['price' => 90000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ13.png', 'name' => 'Массажный стол для хамама ЛМ13'],['price' => 135000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ14.png', 'name' => 'Массажный стол для хамама ЛМ14'],['price' => 90000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ15.png', 'name' => 'Массажный стол для хамама ЛМ15'],['price' => 135000, 'image' => 'https://kurna-tut.ru/wp-content/uploads/ЛМ16.png', 'name' => 'Массажный стол для хамама ЛМ16']];
        $svetodiodnyeLenty = [['price' => 11290, 'image' => 'https://kurna-tut.ru/wp-content/uploads/2-3.jpg', 'name' => 'Термолента SMD2835 180 LED/M 12 W/M 24 V IP68X 2800-3200K'],['price' => 11290, 'image' => 'https://kurna-tut.ru/wp-content/uploads/1-5.jpg', 'name' => 'Термолента SMD2835 180 LED/M 12 W/M 24 V IP68X 6000-6500K'],['price' => 13665, 'image' => 'https://kurna-tut.ru/wp-content/uploads/1-6.jpg', 'name' => 'Термолента SMD4040 120led/m 14 Вт/м 24В IP68, RGB, 5000*12*12мм']];

        $categories = [
            'Курны' => $kyrni,
            'Парогенераторы' => $parogeneratori,
            'Комплекты' => $komplekty,
            'Арки' => $arki,
            'Панно' => $panno,
            'Светильники' => $svetilniki,
            'Колонны' => $kolonni,
            'Краны' => $krani,
            'Раковины' => $rakovini,
            'Глиптика' => $gliptika,
            'Лежаки' => $legaki,
            'Светодиодные ленты' => $svetodiodnyeLenty
        ];

        foreach ($categories as $category => $items) {
            $catalog = new Catalog();
            $catalog->parent_id = 332;
            $catalog->name = $category;
            $catalog->title = $category . ' | Всё для бани';
            $catalog->description = $category . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';
            $catalog->alias = Str::slug($category);
            $catalog->save();

            $this->parseItems($items, $catalog->id);
        }
    }

    private function parseItems(array $items, int $id)
    {
        foreach ($items as $item) {
            $price = $item['price'];
            $name = $item['name'];
            $image = $item['image'];

            $existsCatalogProduct = CatalogProduct::where('alias', Str::slug($name))->exists();

            $catalogProduct = new CatalogProduct();
            $catalogProduct->catalog_id = $id;
            $catalogProduct->name = $name;

            if ($existsCatalogProduct) {
                continue;
            }


            $catalogProduct->title = $catalogProduct->name . ' | Всё для бани';
            $catalogProduct->description = $catalogProduct->name . ', выгодные предложения для Вас. Звоните по номеру телефона +7 (978) 784-70-93';
            $catalogProduct->price = $price;
            $catalogProduct->alias = Str::slug($name);

            if ($catalogProduct->save() && $image) {
                $name = Str::random(40);
                $ext = pathinfo($image, PATHINFO_EXTENSION);
                $path = Storage::path('public/test_items') . '/' . $name . '.' . $ext;

                if (File::copy($image, $path)) {
                    $newImage = new Image();
                    $newImage->path = '/storage/images/' . $name . '.' . $ext;
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
                        })->save(Storage::path('public/test_items') . '/' . $name . '.' . $ext);
                    }
                }
            }
        }
    }
}
