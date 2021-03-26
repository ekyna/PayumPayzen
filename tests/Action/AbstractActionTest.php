<?php

declare(strict_types=1);

namespace Ekyna\Component\Payum\Payzen\Tests\Action;

use Payum\Core\GatewayInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AbstractActionTest
 * @package Ekyna\Component\Payum\Payzen\Tests\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractActionTest extends GenericActionTest
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        parent::couldBeConstructedWithoutAnyArguments();

        $this->assertTrue(true);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMockBuilder(GatewayInterface::class)->getMock();
    }

    /**
     * @return MockObject|TokenInterface
     */
    protected function createTokenMock()
    {
        return $this->getMockBuilder(TokenInterface::class)->getMock();
    }
}
