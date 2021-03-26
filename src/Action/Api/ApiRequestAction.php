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
     * @throws HttpRedirect
     */
    public function execute($request): void
    {
        /** @var Request $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['vads_trans_id']) {
            return;
        }

        $model['vads_trans_id'] = $this->api->getTransactionId();
        // Current UTC date time
        $model['vads_trans_date'] = (new \DateTime('now', new \DateTimeZone('UTC')))->format('YmdHis');

        $data = $model->getArrayCopy();

        $this->logRequestData($data);

        $url = $this->api->createRequestUrl($data);

        throw new HttpRedirect($url);
    }

    /**
     * Logs the request data.
     *
     * @param array $data
     */
    private function logRequestData(array $data): void
    {
        $this->logData("[Payzen] Request", $data, [
            'vads_order_id',
            'vads_amount',
            'vads_ctx_mode',
            'vads_currency',
            'vads_payment_config',
            'vads_site_id',
            'vads_trans_date',
            'vads_trans_id',
            'vads_version',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return $request instanceof Request
            && $request->getModel() instanceof \ArrayAccess;
    }
}
