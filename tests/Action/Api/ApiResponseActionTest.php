<?php

declare(strict_types=1);

namespace Ekyna\Component\Payum\Payzen\Tests\Action\Api;

use Ekyna\Component\Payum\Payzen\Action\Api\ApiResponseAction;
use Ekyna\Component\Payum\Payzen\Request\Response;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;

/**
 * Class ApiResponseActionTest
 * @package Ekyna\Component\Payum\Payzen\Tests\Action\Api
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ApiResponseActionTest extends AbstractApiActionTest
{
    protected $requestClass = Response::class;

    protected $actionClass = ApiResponseAction::class;

    /**
     * @test
     */
    public function should_not_check_response_if_vads_result_is_not_set(): void
    {
        $this->configureGateway([
            'vads_amount' => 10,
        ]);

        $api = $this->getApiMock();
        $api
            ->expects(static::never())
            ->method('checkResponseIntegrity');

        $request = new $this->requestClass([
            'vads_amount' => 10,
        ]);

        $this->action->execute($request);
    }

    /**
     * @test
     */
    public function should_not_check_response_integrity_if_amount_do_not_equal(): void
    {
        $this->configureGateway([
            'vads_amount' => 10,
            'vads_result' => 20,
        ]);

        $api = $this->getApiMock();
        $api
            ->expects(static::never())
            ->method('checkResponseIntegrity');

        $request = new $this->requestClass([
            'vads_amount' => 20,
        ]);

        $this->action->execute($request);
    }

    /**
     * @test
     */
    public function should_not_set_request_model_if_response_integrity_is_not_valid(): void
    {
        $this->configureGateway([
            'vads_amount' => 10,
            'vads_result' => 20,
        ]);

        $api = $this->getApiMock();
        $api
            ->expects(static::once())
            ->method('checkResponseIntegrity')
            ->willReturn(false);

        $request = $this->createMock($this->requestClass);
        $request
            ->expects(self::any())
            ->method('getModel')
            ->willReturn(new ArrayObject([
                'vads_amount' => 10,
            ]));

        $request
            ->expects(self::never())
            ->method('setModel');

        $this->action->execute($request);
    }

    /**
     * @test
     */
    public function should_set_request_model_if_response_integrity_is_valid(): void
    {
        $this->configureGateway([
            'vads_amount' => 10,
            'vads_result' => 20,
        ]);

        $api = $this->getApiMock();
        $api
            ->expects(static::once())
            ->method('checkResponseIntegrity')
            ->willReturn(true);

        $request = $this->createMock($this->requestClass);
        $request
            ->expects(self::any())
            ->method('getModel')
            ->willReturn(new ArrayObject([
                'vads_amount' => 10,
            ]));

        $request
            ->expects(self::once())
            ->method('setModel');

        $this->action->execute($request);
    }

    private function configureGateway(array $data): void
    {
        $gateway = $this->createGatewayMock();
        $gateway
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) use ($data) {
                $request->query = $data;
            }));

        $this->action->setGateway($gateway);
    }
}

