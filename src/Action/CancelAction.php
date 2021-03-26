<?php

namespace Ekyna\Component\Payum\Payzen\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\GetHumanStatus;

/**
 * Class CancelAction
 * @package Ekyna\Component\Payum\Payzen\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CancelAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @inheritDoc
     *
     * @param Cancel $request
     *
     * @noinspection PhpMissingParamTypeInspection*/
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($status = new GetHumanStatus($model));

        if ($status->isNew()) {
            $model['state_override'] = 'canceled';
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return $request instanceof Cancel
            && $request->getModel() instanceof \ArrayAccess;
    }
}
