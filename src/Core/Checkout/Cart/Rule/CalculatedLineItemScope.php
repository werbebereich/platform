<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\Rule;

use Shopware\Core\Checkout\Cart\LineItem\CalculatedLineItemInterface;
use Shopware\Core\Checkout\CheckoutContext;
use Shopware\Core\Checkout\CheckoutRuleScope;

class CalculatedLineItemScope extends CheckoutRuleScope
{
    /**
     * @var CheckoutContext
     */
    protected $context;

    /**
     * @var CalculatedLineItemInterface
     */
    protected $calculatedLineItem;

    public function __construct(CalculatedLineItemInterface $calculatedLineItem, CheckoutContext $context)
    {
        parent::__construct($context);
        $this->calculatedLineItem = $calculatedLineItem;
    }

    public function getCalculatedLineItem(): CalculatedLineItemInterface
    {
        return $this->calculatedLineItem;
    }
}
