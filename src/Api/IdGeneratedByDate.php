<?php

namespace Ekyna\Component\Payum\Payzen\Api;

use Exception;

class IdGeneratedByDate implements TransactionIdInterface
{
    /**
     * @throws Exception
     */
    public function getTransactionId(): array
    {
        $diff = (new \DateTimeImmutable('midnight', new \DateTimeZone('UTC')))
            ->diff(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        return [
            'vads_trans_date' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('YmdHis'),
            'vads_trans_id' => sprintf('%06d', random_int(0, 9) + (($diff->h * 3600 + $diff->i * 60 + $diff->s) * 10))
        ];
    }
}
