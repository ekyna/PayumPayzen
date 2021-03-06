<?php

namespace Ekyna\Component\Payum\Payzen\Action;

use Ekyna\Component\Payum\Payzen\Request\Response;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;

/**
 * Class SyncAction
 * @package Ekyna\Component\Payum\Payzen\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @inheritDoc
     *
     * @param Sync $request
     *
     * @noinspection PhpMissingParamTypeInspection*/
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute(new Response($model));
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return $request instanceof Sync
            && $request->getModel() instanceof \ArrayAccess;
    }
}
