<?php

declare(strict_types=1);

namespace Ekyna\Component\Payum\Payzen\Tests\Action;

use Ekyna\Component\Payum\Payzen\Action\RefundAction;
use Generator;
use Payum\Core\Request\Refund;
use Payum\Core\Request\GetHumanStatus;

/**
 * Class RefundActionTest
 * @package Ekyna\Component\Payum\Payzen\Tests\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RefundActionTest extends AbstractActionTest
{
    protected $requestClass = Refund::class;

    protected $actionClass = RefundAction::class;


    /**
     * @test
     */
    public function should_set_state_override_if_status_is_captured(): void
    {
        $gateway = $this->createGatewayMock();
        $gateway
            ->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function (GetHumanStatus $status) {
                $status->markCaptured();
            });

        $action = new RefundAction();
        $action->setGateway($gateway);

        $request = new Refund([]);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertEquals('refunded', $model['state_override']);
    }

    /**
     * @test
     * @dataProvider provideStatusCallbacks
     *
     * @param callable $statusCallback
     */
    public function should_not_set_state_override_if_status_is_not_captured(callable $statusCallback): void
    {
        $gateway = $this->createGatewayMock();
        $gateway
            ->expects(self::once())
            ->method('execute')
            ->willReturnCallback($statusCallback);

        $action = new RefundAction();
        $action->setGateway($gateway);

        $request = new Refund([]);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayNotHasKey('state_override', $model);
    }

    public function provideStatusCallbacks(): Generator
    {
        yield [
            function (GetHumanStatus $status) {
                $status->markNew();
            },
        ];
        yield [
            function (GetHumanStatus $status) {
                $status->markAuthorized();
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
