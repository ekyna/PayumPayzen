<?php

declare(strict_types=1);

namespace Ekyna\Component\Payum\Payzen\Tests\Action;

use Ekyna\Component\Payum\Payzen\Action\SyncAction;
use Ekyna\Component\Payum\Payzen\Request\Response;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\Constraint\IsInstanceOf;

/**
 * Class SyncActionTest
 * @package Ekyna\Component\Payum\Payzen\Tests\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SyncActionTest extends AbstractActionTest
{
    protected $requestClass = Sync::class;

    protected $actionClass = SyncAction::class;

    /**
     * @test
     */
    public function should_execute_response_with_model(): void
    {
        $assert = function(Response $request) {
            $expected = new ArrayObject([
                'vads_trans_id' => '001',
            ]);

            $this->assertEquals($expected, $request->getModel());
        };

        $gateway = $this->createGatewayMock();
        $gateway
            ->expects(self::at(0))
            ->method('execute')
            ->with(new IsInstanceOf(Response::class))
            ->willReturnCallback($assert);

        $action = new SyncAction();
        $action->setGateway($gateway);

        $request = new Sync([
            'vads_trans_id' => '001',
        ]);

        $action->execute($request);
    }
}
