<?php

declare(strict_types=1);

namespace Ekyna\Component\Payum\Payzen\Tests\Action;

use Ekyna\Component\Payum\Payzen\Action\StatusAction;
use Generator;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHumanStatus;

use PHPUnit\Framework\MockObject\MockObject;

use function ucfirst;

/**
 * Class StatusActionTest
 * @package Ekyna\Component\Payum\Payzen\Tests\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StatusActionTest extends AbstractActionTest
{
    protected $requestClass = GetHumanStatus::class;

    protected $actionClass = StatusAction::class;

    /**
     * @test
     * @dataProvider provideModelAndState
     *
     * @param array         $model
     * @param string        $state
     * @param callable|null $configure
     */
    public function should_set_appropriate_status_from_request_model(
        array $model,
        string $state,
        callable $configure = null
    ): void {
        $action = new StatusAction();

        $request = $this->createMock(GetHumanStatus::class);

        $request
            ->expects(self::any())
            ->method('getModel')
            ->willReturn(new ArrayObject($model));

        $request
            ->expects(self::once())
            ->method('mark' . ucfirst($state));

        if ($configure) {
            $configure($request);
        }

        $action->execute($request);
    }

    public function provideModelAndState(): Generator
    {
        yield [
            [
                'vads_trans_id' => null,
            ],
            'new',
        ];

        yield [
            [
                'vads_trans_id' => '001',
                'vads_result'   => '00',
            ],
            'captured',
        ];

        yield [
            [
                'vads_trans_id' => '001',
                'vads_result'   => '02',
            ],
            'pending',
        ];

        yield [
            [
                'vads_trans_id' => '001',
                'vads_result'   => '17',
            ],
            'canceled',
        ];

        $failed = [
            '03',
            '04',
            '05',
            '07',
            '08',
            '12',
            '13',
            '14',
            '30',
            '31',
            '33',
            '34',
            '41',
            '43',
            '51',
            '54',
            '56',
            '57',
            '58',
            '59',
            '60',
            '61',
            '63',
            '68',
            '90',
            '91',
            '96',
            '94',
            '97',
            '98',
            '99',
        ];
        foreach ($failed as $code) {
            yield [
                [
                    'vads_trans_id' => '001',
                    'vads_result'   => $code,
                ],
                'failed',
            ];
        }

        yield [
            [
                'vads_trans_id' => '001',
                'vads_result'   => '100',
            ],
            'unknown',
        ];

        yield [
            [
                'vads_trans_id'  => '001',
                'vads_result'    => '00',
                'state_override' => 'refunded',
            ],
            'refunded',
            function(MockObject $request) {
                $request
                    ->expects(self::once())
                    ->method('isCaptured')
                    ->willReturn(true);
            }
        ];

        yield [
            [
                'vads_trans_id'  => '001',
                'state_override' => 'canceled',
            ],
            'canceled',
        ];
    }
}
