<?php

namespace Finite\Test\StateMachine;

use Finite\Event\StateMachineEvent;
use Finite\Event\TransitionEvent;
use Finite\StateMachine\ListenableStateMachine;
use Finite\Test\StateMachineTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class ListenableStateMachineTest extends StateMachineTestCase
{
    /**
     * @var ListenableStateMachine
     */
    protected $object;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    public function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->object = new ListenableStateMachine(null, $this->dispatcher, $this->accessor);
    }

    /**
     * @throws \Finite\Exception\ObjectException
     */
    public function testInitialize(): void
    {
        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(...[$this->isInstanceOf(StateMachineEvent::class), 'finite.initialize'])
        ;

        $this->initialize();
    }

    /**
     * @throws \Finite\Exception\ObjectException
     * @throws \Finite\Exception\StateException
     */
    public function testApply(): void
    {
        $this->dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(...[$this->isInstanceOf(TransitionEvent::class), 'finite.test_transition'])
        ;

        $this->dispatcher
            ->expects($this->at(2))
            ->method('dispatch')
            ->with(...[$this->isInstanceOf(TransitionEvent::class), 'finite.test_transition.t23'])
        ;

        $this->dispatcher
            ->expects($this->at(3))
            ->method('dispatch')
            ->with(...[$this->isInstanceOf(TransitionEvent::class), 'finite.pre_transition'])
        ;

        $this->dispatcher
            ->expects($this->at(4))
            ->method('dispatch')
            ->with(...[$this->isInstanceOf(TransitionEvent::class), 'finite.pre_transition.t23'])
        ;

        $this->dispatcher
            ->expects($this->at(5))
            ->method('dispatch')
            ->with(...[$this->isInstanceOf(TransitionEvent::class), 'finite.post_transition'])
        ;

        $this->dispatcher
            ->expects($this->at(6))
            ->method('dispatch')
            ->with(...[$this->isInstanceOf(TransitionEvent::class), 'finite.post_transition.t23'])
        ;

        $this->initialize();
        $this->object->apply('t23');
    }

    /**
     * @throws \Finite\Exception\ObjectException
     */
    public function testCan(): void
    {
        $this->dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(...[$this->isInstanceOf(TransitionEvent::class), 'finite.test_transition'])
        ;

        $this->dispatcher
            ->expects($this->at(2))
            ->method('dispatch')
            ->with(...[$this->isInstanceOf(TransitionEvent::class), 'finite.test_transition.t23'])
        ;

        $this->initialize();
        $this->assertFalse($this->object->can('t34'));
        $this->assertTrue($this->object->can('t23'));
    }

    /**
     * @throws \Finite\Exception\ObjectException
     */
    public function testCanWithListener(): void
    {
        $this->dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(...[$this->isInstanceOf(TransitionEvent::class), 'finite.test_transition'])
        ;

        $this->dispatcher
            ->expects($this->at(2))
            ->method('dispatch')
            ->with(
                ...[
                       $this->callback(
                           static function ($event) {
                               $event->reject();

                               return $event instanceof TransitionEvent;
                           }
                       ),
                       'finite.test_transition.t23',
                   ]
            )
        ;

        $this->initialize();
        
        $this->assertFalse($this->object->can('t34'));
        $this->assertFalse($this->object->can('t23'));
    }

    public function getObject(): ListenableStateMachine
    {
        return $this->object;
    }
}
