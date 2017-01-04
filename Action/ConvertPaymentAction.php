<?php

namespace Ekyna\Component\Payum\Payzen\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;

/**
 * Class ConvertPaymentAction
 * @package Ekyna\Component\Payum\Payzen\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $model = ArrayObject::ensureArrayObject($payment->getDetails());

        if (false == $model['vads_amount']) {
            $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));
            if (2 < $currency->exp) {
                throw new RuntimeException('Unexpected currency exp.');
            }
            $divisor = pow(10, 2 - $currency->exp);

            $model['vads_currency'] = $currency->numeric;
            $model['vads_amount'] = abs($payment->getTotalAmount() / $divisor);
        }

        if (false == $model['vads_order_id']) {
            $model['vads_order_id'] = $payment->getNumber();
        }
        if (false == $model['vads_cust_id']) {
            $model['vads_cust_id'] = $payment->getClientId();
        }
        if (false == $model['vads_cust_email']) {
            $model['vads_cust_email'] = $payment->getClientEmail();
        }

        $request->setResult((array)$model);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && $request->getTo() == 'array';
    }
}
