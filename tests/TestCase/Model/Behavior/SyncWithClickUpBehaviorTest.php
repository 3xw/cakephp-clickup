<?php
namespace Trois\Clickup\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use Trois\Clickup\Model\Behavior\SyncWithClickUpBehavior;

/**
 * Trois\Clickup\Model\Behavior\SyncWithClickUpBehavior Test Case
 */
class SyncWithClickUpBehaviorTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Trois\Clickup\Model\Behavior\SyncWithClickUpBehavior
     */
    public $SyncWithClickUp;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->SyncWithClickUp = new SyncWithClickUpBehavior();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->SyncWithClickUp);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
