<?php

namespace Ekyna\Component\Payum\Payzen\Action\Api;

use Ekyna\Component\Payum\Payzen\Request\Request;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;

/**
 * Class RequestAction
 * @package Ekyna\Component\Payum\Payzen\Action\Api
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ApiRequestAction extends AbstractApiAction
{
    /**
     * @inheritdoc
     *
     * @throws \Payum\Core\Reply\HttpRedirect
     */
    public function execute($request)
    {
        /** @var Request $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['vads_trans_id']) {
            return;
        }

        $model['vads_trans_id'] = $this->api->getTransactionId();
        $model['vads_trans_date'] = date('YmdHis');

        $url = $this->api->createRequestUrl($model->getArrayCopy());

        throw new HttpRedirect($url);
    }

    /**
     * @inheritdoc
     */
    public function supports($request)
    {
        return $request instanceof Request
            && $request->getModel() instanceof \ArrayAccess;
    }
}
