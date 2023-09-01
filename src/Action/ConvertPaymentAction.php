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
     * @inheritDoc
     *
     * @param Convert $request
     *
     * @noinspection PhpMissingParamTypeInspection*/
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $model = ArrayObject::ensureArrayObject($payment->getDetails());

        if (!$model['vads_amount']) {
            $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));
            if (2 < $currency->exp) {
                throw new RuntimeException('Unexpected currency exp.');
            }
            // $currecy->exp is the number of decimal required with this currency
            $multiplier = pow(10, $currency->exp);

            $model['vads_currency'] = (string)$currency->numeric;
            // used to send a non-decimal value to the platform, it can be reverted with currency->exp who be known by Payzen
            $model['vads_amount'] = (string)abs($payment->getTotalAmount() * $multiplier);
        }

        if (!$model['vads_order_id']) {
            $model['vads_order_id'] = $payment->getNumber();
        }
        if (!$model['vads_cust_id']) {
            $model['vads_cust_id'] = $payment->getClientId();
        }
        if (!$model['vads_cust_email']) {
            $model['vads_cust_email'] = $payment->getClientEmail();
        }

        $request->setResult((array)$model);
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && $request->getTo() == 'array';
    }
}
