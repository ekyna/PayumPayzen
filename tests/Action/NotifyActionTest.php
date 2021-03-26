<?php

declare(strict_types=1);

namespace Ekyna\Component\Payum\Payzen\Tests\Action;

use Ekyna\Component\Payum\Payzen\Action\NotifyAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\Constraint\IsInstanceOf;

/**
 * Class NotifyActionTest
 * @package Ekyna\Component\Payum\Payzen\Tests\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotifyActionTest extends AbstractActionTest
{
    protected $requestClass = Notify::class;

    protected $actionClass = NotifyAction::class;

    /**
     * @test
     */
    public function should_execute_sync_request_with_model_details(): void
    {
        $assert = function(Sync $request) {
            $expected = new ArrayObject([
                'vads_amount' => '1234',
            ]);
            $this->assertEquals($expected, $request->getModel());
        };

        $gateway = $this->createGatewayMock();
        $gateway
            ->expects(self::at(0))
            ->method('execute')
            ->with(new IsInstanceOf(Sync::class))
            ->willReturnCallback($assert);

        $action = new NotifyAction();
        $action->setGateway($gateway);

        $request = new Notify([
            'vads_amount' => '1234',
        ]);

        $action->execute($request);
    }
}
