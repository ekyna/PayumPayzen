<?php

namespace Ekyna\Component\Payum\Payzen\Action\Api;

use Ekyna\Component\Payum\Payzen\Request\Response;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
/**
 * Class ResponseAction
 * @package Ekyna\Component\Payum\Payzen\Action\Api
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ApiResponseAction extends AbstractApiAction
{
    /**
     * @inheritdoc
     */
    public function execute($request)
    {
        /** @var Response $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (isset($httpRequest->request['vads_result'])) {
            $data = $httpRequest->request;
        } elseif (isset($httpRequest->query['vads_result'])) {
            $data = $httpRequest->query;
        } else {
            return;
        }

        // Check amount
        if ($model['vads_amount'] != $data['vads_amount']) {
            return;
        }

        // Check the response signature
        if ($this->api->checkResponseSignature($data)) {
            // Update the payment details
            $model->replace($data);
            $request->setModel($model);
        }
    }

    /**
     * @inheritdec
     */
    public function supports($request)
    {
        return $request instanceof Response
            && $request->getModel() instanceof \ArrayAccess;
    }
}
