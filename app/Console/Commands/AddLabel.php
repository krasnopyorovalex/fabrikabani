<?php

namespace App\Console\Commands;

use App\Catalog;
use Illuminate\Console\Command;
use Intervention\Image\ImageManager;
use Storage;

class AddLabel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:add-label';

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
        $catalog = Catalog::whereParentId(46)->with(['catalogs.products'])->get();

        foreach ($catalog as $item) {
            foreach ($item->products as $product) {
                $product->update([
                    'label' => 'teplodar'
                ]);
            }
        }

        $this->info('Well done!');
    }
}
