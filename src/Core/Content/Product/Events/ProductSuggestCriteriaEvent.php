<?php declare(strict_types=1);

namespace Shopware\Core\Content\Product\Events;

class ProductSuggestCriteriaEvent extends ProductListingCriteriaEvent
{
    public const NAME = 'product.suggest.criteria';
}
