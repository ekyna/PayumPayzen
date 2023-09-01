<?php

namespace Ekyna\Component\Payum\Payzen\Tests\Api;

use Ekyna\Component\Payum\Payzen\Api\IdGeneratedByFile;
use Ekyna\Component\Payum\Payzen\Api\TransactionIdInterface;
use Ekyna\Component\Payum\Payzen\Tests\Assert\TransactionIdAssertTrait;
use PHPUnit\Framework\TestCase;

class IdGeneratedByFileTest extends TestCase
{
    use TransactionIdAssertTrait;

    public function test_getTransactionId(): void
    {
        $this->clearCache();

        $transactionIdInterface = $this->createIdGeneratedByFile();

        $data = $transactionIdInterface->getTransactionId();
        $this->assertArrayHasVadsTransIdAndDateKeysTypedString($data);
        $this->assertEquals('000001', $data['vads_trans_id']);

        $data = $transactionIdInterface->getTransactionId();
        $this->assertArrayHasVadsTransIdAndDateKeysTypedString($data);
        $this->assertEquals('000002', $data['vads_trans_id']);

        $data = $transactionIdInterface->getTransactionId();
        $this->assertArrayHasVadsTransIdAndDateKeysTypedString($data);
        $this->assertEquals('000003', $data['vads_trans_id']);

        touch(dirname(__DIR__, 2) . '/cache/transaction_id', time() - 60 * 60 * 24);

        $data = $transactionIdInterface->getTransactionId();
        $this->assertArrayHasVadsTransIdAndDateKeysTypedString($data);
        $this->assertEquals('000001', $data['vads_trans_id']);
    }

    /**
     * Returns the TransactionIdInterface instance.
     *
     * @return TransactionIdInterface
     */
    private function createIdGeneratedByFile(): TransactionIdInterface
    {
        return new IdGeneratedByFile(dirname(__DIR__, 2) . '/cache/');
    }

    private function clearCache(): void
    {
        $path = dirname(__DIR__, 2) . '/cache/transaction_id';
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
