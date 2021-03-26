<?php

declare(strict_types=1);

namespace Ekyna\Component\Payum\Payzen\Tests\Action\Api;

use Ekyna\Component\Payum\Payzen\Action\Api\ApiRequestAction;
use Ekyna\Component\Payum\Payzen\Request\Request;
use Payum\Core\Reply\HttpResponse;

/**
 * Class ApiRequestActionTest
 * @package Ekyna\Component\Payum\Payzen\Tests\Action\Api
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ApiRequestActionTest extends AbstractApiActionTest
{
    protected $requestClass = Request::class;

    protected $actionClass = ApiRequestAction::class;

    /**
     * @test
     */
    public function should_set_transaction_id_and_date_and_throw_redirect(): void
    {
        $api = $this->getApiMock();
        $api
            ->expects(static::once())
            ->method('getTransactionId')
            ->willReturn('000001');

        $api
            ->expects(static::once())
            ->method('createRequestUrl')
            ->willReturn('http://example.org');

        $this->expectException(HttpResponse::class);

        $request = new $this->requestClass([]);

        $this->action->execute($request);
    }
}
