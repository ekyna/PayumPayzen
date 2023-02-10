<?php

namespace Ekyna\Component\Payum\Payzen\Tests\Assert;

use Ekyna\Component\Payum\Payzen\Tests\Constraint\ArrayItem;
use PHPUnit\Framework\Constraint\ArrayHasKey;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\Constraint\LogicalAnd;
use PHPUnit\Framework\Constraint\RegularExpression;

trait TransactionIdAssertTrait
{
    abstract public static function assertThat($value, Constraint $constraint, string $message = ''): void;

    protected function assertArrayHasVadsTransIdAndDateKeysTypedString($array, string $message = '')
    {
        $this->assertThat(
            $array,
            LogicalAnd::fromConstraints(
                new ArrayHasKey('vads_trans_id'),
                new ArrayHasKey('vads_trans_date'),
                new ArrayItem('vads_trans_id', new IsType('string')),
                new ArrayItem('vads_trans_date', new IsType('string')),
                new ArrayItem('vads_trans_id', new RegularExpression('/\d{6}/')),
                new ArrayItem('vads_trans_date', new RegularExpression('/\d{8}/'))
            ),
            $message
        );
    }
}
