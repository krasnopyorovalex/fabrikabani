<?php

namespace App\Domain\Cart\Commands;

use App\Domain\Cart\Queries\GetAllItemsCartQuery;
use App\Domain\CatalogProduct\Queries\GetCatalogProductByIdQuery;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Darryldecode\Cart\CartCollection;
use Exception;

/**
 * Class AddToCartCommand
 * @package App\Domain\Cart\Commands
 */
class AddToCartCommand
{
    use DispatchesJobs;

    private $product;

    /**
     * AddToCartCommand constructor.
     * @param int $product
     */
    public function __construct(int $product)
    {
        $this->product = $product;
    }

    /**
     * @return CartCollection
     * @throws Exception
     */
    public function handle(): CartCollection
    {
        $product = $this->dispatch(new GetCatalogProductByIdQuery($this->product));

        app('cart')->add($product->id, $product->name, $product->price, 1, [
            'image' => $product->image ? $product->image->path : false,
            'url' => $product->url,
            'measure' => $product->measure
        ]);

        return $this->dispatch(new GetAllItemsCartQuery());
    }
}
