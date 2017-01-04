<?php

namespace Ekyna\Component\Payum\Payzen;

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
    /**
     * Builds a new factory.
     *
     * @param array                   $defaultConfig
     * @param GatewayFactoryInterface $coreGatewayFactory
     *
     * @return PayzenGatewayFactory
     */
    public static function build(array $defaultConfig, GatewayFactoryInterface $coreGatewayFactory = null)
    {
        return new static($defaultConfig, $coreGatewayFactory);
    }

    /**
     * @inheritDoc
     */
    protected function populateConfig(ArrayObject $config)
    {
        $apiConfig = false != $config['payum.api_config']
            ? (array)$config['payum.api_config']
            : [];

        $config->defaults([
            'payum.factory_name'  => 'payzen',
            'payum.factory_title' => 'Payzen',

            'payum.action.capture'         => new Action\CaptureAction(),
            'payum.action.convert_payment' => new Action\ConvertPaymentAction(),
            'payum.action.api_request'     => new Action\Api\ApiRequestAction(),
            'payum.action.api_response'    => new Action\Api\ApiResponseAction(),
            'payum.action.sync'            => new Action\SyncAction(),
            'payum.action.status'          => new Action\StatusAction(),
        ]);

        $defaultOptions = [];
        $requiredOptions = [];

        if (false == $config['payum.api']) {
            $defaultOptions['api'] = array_replace([
                'site_id'     => null,
                'certificate' => null,
                'ctx_mode'    => null,
            ], $apiConfig);

            $requiredOptions[] = 'api';

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $api = new Api\Api(); // TODO logger
                $api->setConfig($config['api']);

                return $api;
            };
        }

        $config['payum.default_options'] = $defaultOptions;
        $config['payum.required_options'] = $requiredOptions;

        $config->defaults($config['payum.default_options']);
    }
}
