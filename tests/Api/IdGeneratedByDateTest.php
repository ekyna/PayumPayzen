<?php

namespace Ekyna\Component\Payum\Payzen\Tests\Api;

use Ekyna\Component\Payum\Payzen\Api\IdGeneratedByDate;
use Ekyna\Component\Payum\Payzen\Api\TransactionIdInterface;
use Ekyna\Component\Payum\Payzen\Tests\Assert\TransactionIdAssertTrait;
use PHPUnit\Framework\TestCase;

class IdGeneratedByDateTest extends TestCase
{
    use TransactionIdAssertTrait;

    public function test_getTransactionId(): void
    {
        $transactionIdInterface = $this->createIdGeneratedByDate();

        $data = $transactionIdInterface->getTransactionId();
        $this->assertArrayHasVadsTransIdAndDateKeysTypedString($data);
    }

    /**
     * Returns the TransactionIdInterface instance.
     *
     * @return TransactionIdInterface
     */
    private function createIdGeneratedByDate(): TransactionIdInterface
    {
        return new IdGeneratedByDate();
    }
}
