<?php

namespace Ekyna\Component\Payum\Payzen\Api;

interface TransactionIdInterface
{
    /** @return array{vads_trans_date: string, vads_trans_id: string} */
    public function getTransactionId(): array;
}
