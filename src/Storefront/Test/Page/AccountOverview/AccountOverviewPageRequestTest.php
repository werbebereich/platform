<?php declare(strict_types=1);

namespace Shopware\Storefront\Test\Page\AccountOverview;

use Shopware\Storefront\Page\AccountOverview\AccountOverviewPageRequest;
use Shopware\Storefront\Page\AccountOverview\AccountOverviewPageRequestResolver;
use Shopware\Storefront\Test\Page\PageRequestTestCase;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class AccountOverviewPageRequestTest extends PageRequestTestCase
{
    /**
     * @var AccountOverviewPageRequestResolver
     */
    private $requestResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->requestResolver = $this->getContainer()->get(AccountOverviewPageRequestResolver::class);
    }

    public function testResolveArgument()
    {
        $httpRequest = $this->buildRequest();

        $request = $this->requestResolver->resolve(
            $httpRequest,
            new ArgumentMetadata('foo', self::class, false, false, null)
        );

        $request = iterator_to_array($request);
        $request = array_pop($request);

        static::assertInstanceOf(AccountOverviewPageRequest::class, $request);
    }
}