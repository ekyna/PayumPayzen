<?php

namespace Ekyna\Component\Payum\Payzen\Action\Api;

use Ekyna\Component\Payum\Payzen\Api\TransactionIdInterface;
use Ekyna\Component\Payum\Payzen\Request\Request;
use Exception;
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
     * @var TransactionIdInterface
     */
    private $transactionId;

    public function __construct(
        TransactionIdInterface $transactionId
    ) {
        $this->transactionId = $transactionId;
    }

    /**
     * @inheritdoc
     *
     * @throws HttpRedirect|Exception
     */
    public function execute($request): void
    {
        /** @var Request $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['vads_trans_id']) {
            return;
        }

        // If your application needs to generate a trans_id before this request
        if (isset($model['generated_vads_trans_id']) && isset($model['generated_vads_trans_date'])) {
            $model['vads_trans_id'] = $model['generated_vads_trans_id'];
            $model['vads_trans_date'] = $model['generated_vads_trans_date'];
            unset($model['generated_vads_trans_id']);
            unset($model['generated_vads_trans_date']);
        } else {
            //You can generate a trans_id by a file method or a date method, you need to have the same date used to generate the trans_id
            $transactionId = $this->transactionId->getTransactionId();
            $model['vads_trans_id'] = $transactionId['vads_trans_id'];
            $model['vads_trans_date'] = $transactionId['vads_trans_date'];
        }

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
