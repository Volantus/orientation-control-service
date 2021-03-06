<?php
namespace Volantus\OrientationControlService\Tests\Networking;

use Volantus\FlightBase\Src\Client\ClientService;
use Volantus\FlightBase\Src\General\GyroStatus\GyroStatus;
use Volantus\FlightBase\Src\General\Motor\IncomingMotorControlMessage;
use Volantus\FlightBase\Src\General\Motor\MotorControlMessage;
use Volantus\FlightBase\Src\General\Role\ClientRole;
use Volantus\FlightBase\Tests\Client\MspClientServiceTest;
use Volantus\OrientationControlService\Src\Networking\MessageHandler;
use Volantus\OrientationControlService\Src\Orientation\MspAdapter;
use Volantus\OrientationControlService\Src\Orientation\OrientationController;

/**
 * Class MessageHandlerTest
 *
 * @package Volantus\OrientationControlService\Tests\Networking
 */
class MessageHandlerTest extends MspClientServiceTest
{
    /**
     * @var MspAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mspAdapter;

    /**
     * @var OrientationController|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orientationController;

    protected function setUp()
    {
        $this->mspAdapter = $this->getMockBuilder(MspAdapter::class)->disableOriginalConstructor()->getMock();
        $this->orientationController = $this->getMockBuilder(OrientationController::class)->disableOriginalConstructor()->getMock();
        $this->orientationController->method('getAdapter')->willReturn($this->mspAdapter);
        $this->mspRepositories[] = $this->mspAdapter;
        parent::setUp();
    }

    /**
     * @return ClientService
     */
    protected function createService(): ClientService
    {
        return new MessageHandler($this->dummyOutput, $this->messageService, $this->orientationController);
    }

    public function test_handleMessage_motorControlMessageHandledCorrectly()
    {
        $message = new IncomingMotorControlMessage($this->server, new MotorControlMessage(new GyroStatus(1, 2, 3), 1, 1, true));

        $this->messageService->expects(self::once())
            ->method('handle')
            ->with($this->server, 'correct')->willReturn($message);

        $this->orientationController->expects(self::once())
            ->method('handleControlMessage')
            ->with(self::equalTo($message->getMotorControl()));

        $this->service->addServer($this->server);
        $this->service->newMessage($this->connection, 'correct');
    }

    protected function getExpectedClientRole(): int
    {
        return ClientRole::ORIENTATION_CONTROL_SERVICE;
    }
}