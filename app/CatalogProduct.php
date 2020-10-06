<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * App\CatalogProduct
 *
 * @property int $id
 * @property int|null $catalog_id
 * @property string $name
 * @property string $title
 * @property string|null $description
 * @property string|null $keywords
 * @property string $text
 * @property string $alias
 * @property integer $price
 * @property string $is_published
 * @property int $pos
 * @property int $on_request
 * @property int $not_include_delivery
 * @property string $props
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Catalog $catalog
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CatalogProduct[] $relativeProducts
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CatalogProduct whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CatalogProduct whereCatalogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CatalogProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CatalogProduct whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CatalogProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CatalogProduct whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CatalogProduct whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CatalogProduct whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CatalogProduct wherePos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CatalogProduct whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CatalogProduct whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CatalogProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CatalogProduct extends Model
{
    use AutoAliasTrait;

    private const LABELS = [
        '' => 'Не выбрано',
        'sale' => 'Акция',
        'new' => 'Новинка'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'on_request' => 'boolean',
    ];

    protected $with = ['catalog', 'image'];

    /**
     * @var array
     */
    protected $guarded= ['image'];

    /**
     * @return HasOne
     */
    public function catalog(): HasOne
    {
        return $this->hasOne(Catalog::class, 'id', 'catalog_id');
    }

    /**
     * @return MorphOne
     */
    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    /**
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(CatalogProductImage::class)->orderBy('pos');
    }

    /**
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return route('catalog_product.show', $this->alias);
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return sprintf('%s &#8381;', number_format($this->price, 0, '.', ' '));
    }

    /**
     * @return array
     */
    public function getLabels(): array
    {
        return self::LABELS;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getLabelName(string $key)
    {
        return self::LABELS[$key];
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isSelectedLabel(string $key): bool
    {
        return $key === $this->label;
    }

    /**
     * @return bool
     */
    public function priceNotIncludedDelivery(): bool
    {
        return ($this->catalog->parent && in_array($this->catalog->parent->id, [21,22,40,46], true)) || $this->catalog->id === 269;
    }
}
