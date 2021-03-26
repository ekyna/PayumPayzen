<?php

declare(strict_types=1);

namespace Ekyna\Component\Payum\Payzen\Tests\Action;

use Ekyna\Component\Payum\Payzen\Action\ConvertPaymentAction;
use Iterator;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetCurrency;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ConvertPaymentActionTest
 * @package Ekyna\Component\Payum\Payzen\Tests\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConvertPaymentActionTest extends AbstractActionTest
{
    protected $requestClass = Convert::class;

    protected $actionClass = ConvertPaymentAction::class;

    /**
     * @test
     */
    public function should_convert_payment_to_array(): void
    {
        $gateway = $this->createGatewayMock();
        $gateway
            ->expects(self::at(0))
            ->method('execute')
            ->with(new IsInstanceOf(GetCurrency::class))
            ->willReturnCallback(function(GetCurrency $currency) {
                $currency->exp = 2;
                $currency->numeric = '978';
            });

        $action = new ConvertPaymentAction();
        $action->setGateway($gateway);

        $request = new Convert($this->mockPayment(), 'array');

        $action->execute($request);

        $result = $request->getResult();

        $this->assertEquals('1234', $result['vads_amount']);
        $this->assertEquals('978', $result['vads_currency']);
        $this->assertEquals('O01', $result['vads_order_id']);
        $this->assertEquals(123, $result['vads_cust_id']);
        $this->assertEquals('customer@example.org', $result['vads_cust_email']);
    }

    private function mockPayment(): MockObject
    {
        $payment = $this->createMock(PaymentInterface::class);
        $payment
            ->expects(self::once())
            ->method('getDetails')
            ->willReturn([]);

        $payment
            ->expects(self::once())
            ->method('getCurrencyCode')
            ->willReturn('EUR');

        $payment
            ->expects(self::once())
            ->method('getTotalAmount')
            ->willReturn(1234); // €12.34

        $payment
            ->expects(self::once())
            ->method('getNumber')
            ->willReturn('O01');

        $payment
            ->expects(self::once())
            ->method('getClientId')
            ->willReturn(123);

        $payment
            ->expects(self::once())
            ->method('getClientEmail')
            ->willReturn('customer@example.org');

        return $payment;
    }

    public function provideSupportedRequests(): Iterator
    {
        yield array(new Convert($this->createMock(PaymentInterface::class), 'array'));
    }

    public function provideNotSupportedRequests(): Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array(new Convert('foo', 'array'));
        yield array(new Convert($this->createMock(PaymentInterface::class), 'foo'));
        yield array(new Convert(new \stdClass(), 'array'));
        yield array($this->getMockForAbstractClass(Generic::class, array(array())));
    }
}
