<?php

declare(strict_types=1);

namespace Ekyna\Component\Payum\Payzen\Tests\Api;

use Ekyna\Component\Payum\Payzen\Api\Api;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;

use function file_exists;
use function unlink;

/**
 * Class ApiTest
 * @package Ekyna\Component\Payum\Payzen\Tests\Api
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ApiTest extends TestCase
{
    public function test_invalid_config(): void
    {
        $this->expectException(ExceptionInterface::class);

        $api = new Api();
        $api->setConfig([
            'mode'    => null,
            'tpe'     => null,
            'key'     => null,
            'company' => null,
        ]);
    }

    public function test_valid_config(): void
    {
        $this->createApi();

        $this->assertTrue(true);
    }

    public function test_getTransactionId(): void
    {
        $this->clearCache();

        $api = $this->createApi();

        $id = $api->getTransactionId();
        $this->assertEquals('000001', $id);

        $id = $api->getTransactionId();
        $this->assertEquals('000002', $id);

        $id = $api->getTransactionId();
        $this->assertEquals('000003', $id);

        touch(__DIR__ . '/../../cache/transaction_id', time() - 60 * 60 * 24);

        $id = $api->getTransactionId();
        $this->assertEquals('000001', $id);
    }

    /**
     * @param string $hashMode
     * @param array  $data
     * @param string $expected
     *
     * @dataProvider provideGenerateSignature
     */
    public function test_generateSignature(string $hashMode, array $data, string $expected): void
    {
        $api = $this->createApi([
            'hash_mode' => $hashMode,
        ]);

        $this->assertEquals($expected, $api->generateSignature($data));
    }

    public function provideGenerateSignature(): Generator
    {
        yield [
            Api::HASH_MODE_SHA256,
            [
                'vads_amount'     => '1234',
                'vads_currency'   => '978',
                'vads_trans_date' => '20200101120000',
                'vads_trans_id'   => '000001',
            ],
            'egzpLYuwIX0LoqSS3rNxF9QYuKbvd3pp38gIoV+Oi3w=',
        ];

        yield [
            Api::HASH_MODE_SHA1,
            [
                'vads_amount'     => '1234',
                'vads_currency'   => '978',
                'vads_trans_date' => '20200101120000',
                'vads_trans_id'   => '000001',
            ],
            '7b203c8f547b6cc64e9402887e514add8e2cb29d',
        ];
    }

    public function test_createRequestData(): void
    {
        $api = $this->createApi();

        $actual = $api->createRequestData([
            'vads_amount'     => '1234',
            'vads_currency'   => '978',
            'vads_trans_date' => '20200101120000',
            'vads_trans_id'   => '000001',
        ]);

        $expected = [
            'vads_amount'         => '1234',
            'vads_currency'       => '978',
            'vads_trans_date'     => '20200101120000',
            'vads_trans_id'       => '000001',
            'vads_site_id'        => '123456789',
            'vads_action_mode'    => 'INTERACTIVE',
            'vads_page_action'    => 'PAYMENT',
            'vads_payment_config' => 'SINGLE',
            'vads_return_mode'    => 'POST',
            'vads_version'        => 'V2',
            'signature'           => 'p9TbeohlOZVPAmhEwfAlGxRJxFAKpNg5wYBSN9emuqs=',
            'vads_ctx_mode'       => 'PRODUCTION',
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param array  $config
     * @param array  $data
     * @param string $expected
     *
     * @dataProvider provideCreateRequestUrl
     */
    public function test_createRequestUrl(array $config, array $data, string $expected): void
    {
        $api = $this->createApi($config);

        $actual = $api->createRequestUrl($data);

        $this->assertEquals($expected, $actual);
    }

    public function provideCreateRequestUrl(): Generator
    {
        yield [
            [],
            [
                'vads_amount'     => '1234',
                'vads_currency'   => '978',
                'vads_trans_date' => '20200101120000',
                'vads_trans_id'   => '000001',
            ],
            'https://secure.payzen.eu/vads-payment/?vads_action_mode=INTERACTIVE&vads_page_action=PAYMENT&vads_payment_config=SINGLE&vads_return_mode=POST&vads_version=V2&vads_amount=1234&vads_currency=978&vads_trans_date=20200101120000&vads_trans_id=000001&vads_site_id=123456789&vads_ctx_mode=PRODUCTION&signature=p9TbeohlOZVPAmhEwfAlGxRJxFAKpNg5wYBSN9emuqs%3D',
        ];

        yield [
            [
                'endpoint' => Api::ENDPOINT_SYSTEMPAY,
            ],
            [
                'vads_amount'     => '1234',
                'vads_currency'   => '978',
                'vads_trans_date' => '20200101120000',
                'vads_trans_id'   => '000001',
            ],
            'https://paiement.systempay.fr/vads-payment/?vads_action_mode=INTERACTIVE&vads_page_action=PAYMENT&vads_payment_config=SINGLE&vads_return_mode=POST&vads_version=V2&vads_amount=1234&vads_currency=978&vads_trans_date=20200101120000&vads_trans_id=000001&vads_site_id=123456789&vads_ctx_mode=PRODUCTION&signature=p9TbeohlOZVPAmhEwfAlGxRJxFAKpNg5wYBSN9emuqs%3D',
        ];

        yield [
            [
                'endpoint_url' => 'https://custom-url.fr/vads-payment/',
            ],
            [
                'vads_amount'     => '1234',
                'vads_currency'   => '978',
                'vads_trans_date' => '20200101120000',
                'vads_trans_id'   => '000001',
            ],
            'https://custom-url.fr/vads-payment/?vads_action_mode=INTERACTIVE&vads_page_action=PAYMENT&vads_payment_config=SINGLE&vads_return_mode=POST&vads_version=V2&vads_amount=1234&vads_currency=978&vads_trans_date=20200101120000&vads_trans_id=000001&vads_site_id=123456789&vads_ctx_mode=PRODUCTION&signature=p9TbeohlOZVPAmhEwfAlGxRJxFAKpNg5wYBSN9emuqs%3D',
        ];


        yield [
            [
                'endpoint' => Api::ENDPOINT_SCELLIUS,
            ],
            [
                'vads_amount'     => '1234',
                'vads_currency'   => '978',
                'vads_trans_date' => '20200101120000',
                'vads_trans_id'   => '000001',
            ],
            'https://scelliuspaiement.labanquepostale.fr/vads-payment/?vads_action_mode=INTERACTIVE&vads_page_action=PAYMENT&vads_payment_config=SINGLE&vads_return_mode=POST&vads_version=V2&vads_amount=1234&vads_currency=978&vads_trans_date=20200101120000&vads_trans_id=000001&vads_site_id=123456789&vads_ctx_mode=PRODUCTION&signature=p9TbeohlOZVPAmhEwfAlGxRJxFAKpNg5wYBSN9emuqs%3D',
        ];

        yield [
            [
                'endpoint' => Api::ENDPOINT_OSB,
            ],
            [
                'vads_amount'     => '1234',
                'vads_currency'   => '978',
                'vads_trans_date' => '20200101120000',
                'vads_trans_id'   => '000001',
            ],
            'https://secure.osb.pf/vads-payment/?vads_action_mode=INTERACTIVE&vads_page_action=PAYMENT&vads_payment_config=SINGLE&vads_return_mode=POST&vads_version=V2&vads_amount=1234&vads_currency=978&vads_trans_date=20200101120000&vads_trans_id=000001&vads_site_id=123456789&vads_ctx_mode=PRODUCTION&signature=p9TbeohlOZVPAmhEwfAlGxRJxFAKpNg5wYBSN9emuqs%3D',
        ];

        yield [
            [
                'endpoint' => Api::ENDPOINT_SOGECOMMERCE,
            ],
            [
                'vads_amount'     => '1234',
                'vads_currency'   => '978',
                'vads_trans_date' => '20200101120000',
                'vads_trans_id'   => '000001',
            ],
            'https://sogecommerce.societegenerale.eu/vads-payment/?vads_action_mode=INTERACTIVE&vads_page_action=PAYMENT&vads_payment_config=SINGLE&vads_return_mode=POST&vads_version=V2&vads_amount=1234&vads_currency=978&vads_trans_date=20200101120000&vads_trans_id=000001&vads_site_id=123456789&vads_ctx_mode=PRODUCTION&signature=p9TbeohlOZVPAmhEwfAlGxRJxFAKpNg5wYBSN9emuqs%3D',
        ];
    }

    /**
     * @param array $data
     * @param bool  $expected
     *
     * @dataProvider provideResponseCheck
     */
    public function test_checkResponseIntegrity(array $data, bool $expected): void
    {
        $api = $this->createApi();

        $this->assertEquals($expected, $api->checkResponseIntegrity($data));
    }

    public function provideResponseCheck(): Generator
    {
        yield [
            [
                'vads_amount'         => '1234',
                'vads_currency'       => '978',
                'vads_trans_date'     => '20200101120000',
                'vads_trans_id'       => '000001',
                'vads_site_id'        => '123456789',
                'vads_action_mode'    => 'INTERACTIVE',
                'vads_page_action'    => 'PAYMENT',
                'vads_payment_config' => 'SINGLE',
                'vads_return_mode'    => 'POST',
                'vads_version'        => 'V2',
                'vads_ctx_mode'       => 'PRODUCTION',
            ],
            false,
        ];

        yield [
            [
                'vads_amount'         => '1234',
                'vads_currency'       => '978',
                'vads_trans_date'     => '20200101120000',
                'vads_trans_id'       => '000001',
                'vads_site_id'        => '000000000',
                'vads_action_mode'    => 'INTERACTIVE',
                'vads_page_action'    => 'PAYMENT',
                'vads_payment_config' => 'SINGLE',
                'vads_return_mode'    => 'POST',
                'vads_version'        => 'V2',
                'signature'           => '4KbhpWprEUEFHpwi17szZdEZ3qHUFvSOkzlHy55X6vw=',
                'vads_ctx_mode'       => 'PRODUCTION',
            ],
            false,
        ];

        yield [
            [
                'vads_amount'         => '1234',
                'vads_currency'       => '978',
                'vads_trans_date'     => '20200101120000',
                'vads_trans_id'       => '000001',
                'vads_site_id'        => '000000000',
                'vads_action_mode'    => 'INTERACTIVE',
                'vads_page_action'    => 'PAYMENT',
                'vads_payment_config' => 'SINGLE',
                'vads_return_mode'    => 'POST',
                'vads_version'        => 'V2',
                'signature'           => 'IwyeM3zeDw3g+yxFI1+8NGFAIqcYs3Taf1Bz1Hu83qY=',
                'vads_ctx_mode'       => 'TEST',
            ],
            false,
        ];

        yield [
            [
                'vads_amount'         => '1234',
                'vads_currency'       => '978',
                'vads_trans_date'     => '20200101120000',
                'vads_trans_id'       => '000001',
                'vads_site_id'        => '123456789',
                'vads_action_mode'    => 'INTERACTIVE',
                'vads_page_action'    => 'PAYMENT',
                'vads_payment_config' => 'SINGLE',
                'vads_return_mode'    => 'POST',
                'vads_version'        => 'V2',
                'signature'           => 'p9TbeohlOZVPAmhEwfAlGxRJxFAKpNg5wYBSN9emuqs=',
                'vads_ctx_mode'       => 'PRODUCTION',
            ],
            true,
        ];
    }

    /**
     * Returns the API instance.
     *
     * @param array $config
     *
     * @return Api
     */
    private function createApi(array $config = []): Api
    {
        $api = new Api();

        $api->setConfig(array_replace([
            'site_id'     => '123456789',
            'certificate' => '987654321',
            'ctx_mode'    => Api::MODE_PRODUCTION,
            'directory'   => __DIR__ . '/../../cache',
        ], $config));

        return $api;
    }

    private function clearCache(): void
    {
        $path = __DIR__ . '/../../cache/transaction_id';
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
