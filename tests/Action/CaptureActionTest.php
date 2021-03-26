<?php

declare(strict_types=1);

namespace Ekyna\Component\Payum\Payzen\Tests\Action;

use Ekyna\Component\Payum\Payzen\Action\CaptureAction;
use Ekyna\Component\Payum\Payzen\Request\Request;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CaptureActionTest
 * @package Ekyna\Component\Payum\Payzen\Tests\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CaptureActionTest extends AbstractActionTest
{
    protected $requestClass = Capture::class;

    protected $actionClass = CaptureAction::class;

    /**
     * @test
     */
    public function should_set_vads_urls_if_request_has_token(): void
    {
        $assert = function(Request $request) {
            $model = $request->getModel();

            $fields = [
                'vads_url_cancel',
                'vads_url_error',
                'vads_url_referral',
                'vads_url_refused',
                'vads_url_success',
                'vads_url_return',
            ];

            foreach ($fields as $field) {
                $this->assertEquals('https://exmaple.org/return', $model[$field]);
            }
        };

        $gateway = $this->createGatewayMock();
        $gateway
            ->expects(self::at(0))
            ->method('execute')
            ->with(new IsInstanceOf(Request::class))
            ->willReturnCallback($assert);

        $action = new CaptureAction();
        $action->setGateway($gateway);

        $request = new Capture($this->mockToken());
        $request->setModel([]);

        $action->execute($request);
    }

    /**
     * @test
     */
    public function should_set_vad_url_check_if_token_factory_is_available(): void
    {
        $assert = function(Request $request) {
            $model = $request->getModel();

            $this->assertEquals('https://exmaple.org/notify', $model['vads_url_check']);
        };

        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects(self::once())
            ->method('getTargetUrl')
            ->willReturn('https://exmaple.org/notify');

        $tokenFactory = $this->createMock(GenericTokenFactoryInterface::class);
        $tokenFactory
            ->expects(self::once())
            ->method('createNotifyToken')
            ->with('payzen', [])
            ->willReturn($token);

        $gateway = $this->createGatewayMock();
        $gateway
            ->expects(self::at(0))
            ->method('execute')
            ->with(new IsInstanceOf(Request::class))
            ->willReturnCallback($assert);

        $action = new CaptureAction();
        $action->setGateway($gateway);
        $action->setGenericTokenFactory($tokenFactory);

        $request = new Capture($this->mockToken());
        $request->setModel([]);

        $action->execute($request);
    }

    /**
     * @test
     */
    public function should_not_execute_api_request_if_trans_id_is_set(): void
    {
        $gateway = $this->createGatewayMock();
        $gateway
            ->expects(self::at(0))
            ->method('execute')
            ->with(new IsInstanceOf(Sync::class));

        $action = new CaptureAction();
        $action->setGateway($gateway);

        $request = new Capture($this->mockToken());
        $request->setModel([
            'vads_trans_id' => '1',
        ]);

        $action->execute($request);
    }

    private function mockToken(): MockObject
    {
        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects(self::once())
            ->method('getTargetUrl')
            ->willReturn('https://exmaple.org/return');

        $token
            ->expects(self::any())
            ->method('getGatewayName')
            ->willReturn('payzen');

        $token
            ->expects(self::any())
            ->method('getDetails')
            ->willReturn([]);

        return $token;
    }
}
