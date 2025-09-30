<?php

/**
 * This file contains the PHPMailerBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Ticks\PHPMailer\Tests;

use Lunr\Ticks\AnalyticsDetailLevel;

/**
 * This class contains tests for the PHPMailer class.
 *
 * @covers Lunr\Ticks\PHPMailer\PHPMailer
 */
class PHPMailerBaseTest extends PHPMailerTestCase
{

    /**
     * Test that the event logger is set correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::__construct
     */
    public function testEventLoggerIsSetCorrectly(): void
    {
        $this->assertPropertySame('eventLogger', $this->logger);
    }

    /**
     * Test that the controller is set correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::__construct
     */
    public function testControllerIsSetCorrectly(): void
    {
        $this->assertPropertySame('tracingController', $this->controller);
    }

    /**
     * Test that the level is set correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::__construct
     */
    public function testLevelIsSetCorrectly(): void
    {
        $this->assertPropertySame('analyticsDetailLevel', AnalyticsDetailLevel::Info);
    }

    /**
     * Test that the action_function is set correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::__construct
     */
    public function testActionFunctionIsSetCorrectly(): void
    {
        $this->assertPropertySame('action_function', [ $this->class, 'afterSending' ]);
    }

    /**
     * Test that the setAnalyticsDetailLevel() sets properties correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::setAnalyticsDetailLevel
     */
    public function testSetAnalyticsDetailLevelProperty(): void
    {
        $this->class->setAnalyticsDetailLevel(AnalyticsDetailLevel::None);

        $this->assertPropertySame('analyticsDetailLevel', AnalyticsDetailLevel::None);
    }

    /**
     * Test that the startTimestamp is unset correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::__destruct
     */
    public function testStartTimeIsUnsetCorrectly(): void
    {
        $this->setReflectionPropertyValue('startTimestamp', microtime(TRUE));

        $this->class->__destruct();

        $this->assertPropertyUnset('startTimestamp');
    }

    /**
     * Test that action_function is cloned correctly
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::__clone
     */
    public function testActionFunctionIsClonedCorrectly(): void
    {
        $cloned = clone $this->class;

        $this->assertSame($cloned, $cloned->action_function[0]); // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
    }

}

?>
