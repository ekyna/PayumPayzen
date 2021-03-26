<?php

namespace Ekyna\Component\Payum\Payzen\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Refund;

/**
 * Class CaptureAction
 * @package Ekyna\Component\Payum\Payzen\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RefundAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @inheritDoc
     *
     * @param Refund $request
     *
     * @noinspection PhpMissingParamTypeInspection*/
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($status = new GetHumanStatus($model));

        if ($status->isCaptured()) {
            $model['state_override'] = 'refunded';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return $request instanceof Refund
            && $request->getModel() instanceof \ArrayAccess;
    }
}
