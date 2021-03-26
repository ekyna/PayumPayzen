<?php

declare(strict_types=1);

namespace Ekyna\Component\Payum\Payzen\Tests\Action;

use Ekyna\Component\Payum\Payzen\Action\CancelAction;
use Generator;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\GetHumanStatus;

/**
 * Class CancelActionTest
 * @package Ekyna\Component\Payum\Payzen\Tests\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CancelActionTest extends AbstractActionTest
{
    protected $requestClass = Cancel::class;

    protected $actionClass = CancelAction::class;


    /**
     * @test
     */
    public function should_set_state_override_if_status_is_new(): void
    {
        $gateway = $this->createGatewayMock();
        $gateway
            ->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function (GetHumanStatus $status) {
                $status->markNew();
            });

        $action = new CancelAction();
        $action->setGateway($gateway);

        $request = new Cancel([]);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertEquals('canceled', $model['state_override']);
    }

    /**
     * @test
     * @dataProvider provideStatusCallbacks
     *
     * @param callable $statusCallback
     */
    public function should_not_set_state_override_if_status_is_not_new(callable $statusCallback): void
    {
        $gateway = $this->createGatewayMock();
        $gateway
            ->expects(self::once())
            ->method('execute')
            ->willReturnCallback($statusCallback);

        $action = new CancelAction();
        $action->setGateway($gateway);

        $request = new Cancel([]);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayNotHasKey('state_override', $model);
    }

    public function provideStatusCallbacks(): Generator
    {
        yield [
            function (GetHumanStatus $status) {
                $status->markAuthorized();
            },
        ];
        yield [
            function (GetHumanStatus $status) {
                $status->markCaptured();
            },
        ];
        yield [
            function (GetHumanStatus $status) {
                $status->markExpired();
            },
        ];
        yield [
            function (GetHumanStatus $status) {
                $status->markFailed();
            },
        ];
        yield [
            function (GetHumanStatus $status) {
                $status->markPayedout();
            },
        ];
        yield [
            function (GetHumanStatus $status) {
                $status->markPending();
            },
        ];
        yield [
            function (GetHumanStatus $status) {
                $status->markRefunded();
            },
        ];
        yield [
            function (GetHumanStatus $status) {
                $status->markSuspended();
            },
        ];
        yield [
            function (GetHumanStatus $status) {
                $status->markUnknown();
            },
        ];
    }
}
