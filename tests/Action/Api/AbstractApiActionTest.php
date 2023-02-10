<?php

declare(strict_types=1);

namespace Ekyna\Component\Payum\Payzen\Tests\Action\Api;

use Ekyna\Component\Payum\Payzen\Action\Api\AbstractApiAction;
use Ekyna\Component\Payum\Payzen\Api\Api;
use Ekyna\Component\Payum\Payzen\Api\IdGeneratedByDate;
use Ekyna\Component\Payum\Payzen\Api\IdGeneratedByFile;
use Ekyna\Component\Payum\Payzen\Api\TransactionIdInterface;
use Ekyna\Component\Payum\Payzen\Tests\Action\AbstractActionTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AbstractApiActionTest
 * @package Ekyna\Component\Payum\Payzen\Tests\Action\Api
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @property AbstractApiAction $action
 */
abstract class AbstractApiActionTest extends AbstractActionTest
{
    /** @var MockObject|Api */
    protected $api;

    /**
     * @var MockObject|TransactionIdInterface
     */
    protected $transactionIdInterface;

    protected function setUp(): void
    {
        $this->action = new $this->actionClass($this->getIdGeneratedByFile());
        $this->action->setApi($this->getApiMock());
    }

    protected function tearDown(): void
    {
        $this->api = null;
        $this->action = null;
    }

    /**
     * @return MockObject|Api
     */
    protected function getApiMock()
    {
        if ($this->api) {
            return $this->api;
        }

        return $this->api = $this->getMockBuilder(Api::class)->getMock();
    }

    /**
     * @return TransactionIdInterface
     */
    protected function getIdGeneratedByFile()
    {
        if ($this->transactionIdInterface) {
            return $this->transactionIdInterface;
        }

        return $this->transactionIdInterface = new IdGeneratedByFile(dirname(__DIR__, 3) . '/cache/');
    }
}
