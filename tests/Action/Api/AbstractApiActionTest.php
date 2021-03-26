<?php

declare(strict_types=1);

namespace Ekyna\Component\Payum\Payzen\Tests\Action\Api;

use Ekyna\Component\Payum\Payzen\Action\Api\AbstractApiAction;
use Ekyna\Component\Payum\Payzen\Api\Api;
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

    protected function setUp(): void
    {
        $this->action = new $this->actionClass();
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
    protected function getApiMock(): MockObject
    {
        if ($this->api) {
            return $this->api;
        }

        return $this->api = $this->getMockBuilder(Api::class)->getMock();
    }
}
