<?php

namespace Ekyna\Component\Payum\Payzen;

use Ekyna\Component\Payum\Payzen\Api\IdGeneratedByFile;
use Ekyna\Component\Payum\Payzen\Api\TransactionIdInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;

/**
 * Class PayzenGatewayFactory
 * @package Ekyna\Component\Payum\Payzen
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PayzenGatewayFactory extends GatewayFactory
{
    private $transactionId;

    public function __construct(
        array $defaultConfig = array(),
        GatewayFactoryInterface $coreGatewayFactory = null,
        TransactionIdInterface $transactionId = null
    ) {
        parent::__construct($defaultConfig, $coreGatewayFactory);
        $this->transactionId = $transactionId ?? new IdGeneratedByFile($defaultConfig['directory'] ?? sys_get_temp_dir());
    }

    /**
     * Builds a new factory.
     *
     * @param array $defaultConfig
     * @param GatewayFactoryInterface|null $coreGatewayFactory
     * @param TransactionIdInterface|null $transactionId
     * @return PayzenGatewayFactory
     */
    public static function build(array $defaultConfig, GatewayFactoryInterface $coreGatewayFactory = null, TransactionIdInterface  $transactionId = null): PayzenGatewayFactory
    {
        return new static($defaultConfig, $coreGatewayFactory, $transactionId);
    }

    /**
     * @inheritDoc
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name'  => 'payzen',
            'payum.factory_title' => 'Payzen',

            'payum.action.capture'         => new Action\CaptureAction(),
            'payum.action.convert_payment' => new Action\ConvertPaymentAction(),
            'payum.action.sync'            => new Action\SyncAction(),
            'payum.action.cancel'          => new Action\CancelAction(),
            'payum.action.refund'          => new Action\RefundAction(),
            'payum.action.status'          => new Action\StatusAction(),
            'payum.action.notify'          => new Action\NotifyAction(),
            'payum.action.api.request'     => new Action\Api\ApiRequestAction($this->transactionId),
            'payum.action.api.response'    => new Action\Api\ApiResponseAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'site_id'      => null,
                'certificate'  => null,
                'ctx_mode'     => null,
                'directory'    => null,
                'endpoint'     => null,
                'endpoint_url' => null,
                'hash_mode'    => Api\Api::HASH_MODE_SHA256,
                'debug'        => false,
            ];

            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = ['site_id', 'certificate', 'ctx_mode', 'directory'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $payzenConfig = [
                    'endpoint'     => $config['endpoint'],
                    'endpoint_url' => $config['endpoint_url'],
                    'site_id'      => $config['site_id'],
                    'certificate'  => $config['certificate'],
                    'ctx_mode'     => $config['ctx_mode'],
                    'directory'    => $config['directory'],
                    'hash_mode'    => $config['hash_mode'],
                    'debug'        => $config['debug'],
                ];

                $api = new Api\Api();
                $api->setConfig($payzenConfig);

                return $api;
            };
        }
    }
}
