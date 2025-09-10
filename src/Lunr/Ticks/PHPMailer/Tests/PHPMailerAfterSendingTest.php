<?php

/**
 * This file contains the PHPMailerAfterSendingTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherland B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Ticks\PHPMailer\Tests;

use Lunr\Ticks\AnalyticsDetailLevel;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * This class contains tests for the PHPMailer class.
 *
 * @covers Lunr\Ticks\PHPMailer\PHPMailer
 */
class PHPMailerAfterSendingTest extends PHPMailerTestCase
{

    /**
     * Test that the afterSending works correctly with analytics level None.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::afterSending
     */
    public function testAfterSendingWorksCorrectlyWithAnalyticsLevelNone(): void
    {
        $this->mockFunction('microtime', fn() => 1724932394.128985);

        $this->setReflectionPropertyValue('startTimestamp', 1724932393.008985);
        $this->setReflectionPropertyValue('Mailer', 'smtp');
        $this->setReflectionPropertyValue('MIMEHeader', 'Content-Type: text/plain');
        $this->setReflectionPropertyValue('MIMEBody', 'full mime body');
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::None);

        $this->logger->expects($this->never())
                     ->method('newEvent');

        $extra = [ 'smtp_transaction_id' => FALSE ];

        $method = $this->getReflectionMethod('afterSending');
        $method->invoke($this->class, TRUE, [ 'example@mail.com', 'John Doe' ], [], [], 'subject', 'body', 'from@mail.com', $extra);

        $this->unmockFunction('microtime');
    }

    /**
     * Test that the afterSending works correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::afterSending
     */
    public function testAfterSendingThrowsErrorWhenTraceIdCannotBeFound(): void
    {
        $this->mockFunction('microtime', fn() => 1724932394.128985);

        $this->setReflectionPropertyValue('startTimestamp', 1724932393.008985);
        $this->setReflectionPropertyValue('Mailer', 'smtp');
        $this->setReflectionPropertyValue('MIMEHeader', 'Content-Type: text/plain');
        $this->setReflectionPropertyValue('MIMEBody', 'full mime body');
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Info);

        $this->controller->shouldReceive('getTraceID')
                         ->once()
                         ->andReturn(NULL);

        $this->controller->shouldReceive('getSpanID')
                         ->never();

        $this->controller->shouldReceive('getParentSpanID')
                         ->never();

        $this->controller->shouldReceive('getSpanSpecificTags')
                         ->never();

        $this->logger->expects($this->once())
                     ->method('newEvent')
                     ->with('outbound_requests_log')
                     ->willReturn($this->event);

        $this->event->expects($this->never())
                    ->method('setTraceID');

        $this->event->expects($this->never())
                    ->method('setSpanID');

        $this->event->expects($this->never())
                    ->method('setParentSpanID');

        $this->event->expects($this->never())
                    ->method('addTags');

        $this->event->expects($this->never())
                    ->method('addFields');

        $this->event->expects($this->never())
                    ->method('recordTimestamp');

        $this->event->expects($this->never())
                    ->method('record');

        $extra = [ 'smtp_transaction_id' => FALSE ];

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Trace ID not available!');

        $method = $this->getReflectionMethod('afterSending');
        $method->invoke($this->class, TRUE, [ 'example@mail.com', 'John Doe' ], [], [], 'subject', 'body', 'from@mail.com', $extra);

        $this->unmockFunction('microtime');
    }

    /**
     * Test that the afterSending works correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::afterSending
     */
    public function testAfterSendingThrowsErrorWhenSpanIdCannotBeFound(): void
    {
        $this->mockFunction('microtime', fn() => 1724932394.128985);

        $this->setReflectionPropertyValue('startTimestamp', 1724932393.008985);
        $this->setReflectionPropertyValue('Mailer', 'smtp');
        $this->setReflectionPropertyValue('MIMEHeader', 'Content-Type: text/plain');
        $this->setReflectionPropertyValue('MIMEBody', 'full mime body');
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Info);

        $this->controller->shouldReceive('getTraceID')
                         ->once()
                         ->andReturn('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->controller->shouldReceive('getSpanID')
                         ->once()
                         ->andReturn(NULL);

        $this->controller->shouldReceive('getParentSpanID')
                         ->never();

        $this->controller->shouldReceive('getSpanSpecificTags')
                         ->never();

        $this->logger->expects($this->once())
                     ->method('newEvent')
                     ->with('outbound_requests_log')
                     ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('setTraceID')
                    ->with('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->event->expects($this->never())
                    ->method('setSpanID');

        $this->event->expects($this->never())
                    ->method('setParentSpanID');

        $this->event->expects($this->never())
                    ->method('addTags');

        $this->event->expects($this->never())
                    ->method('addFields');

        $this->event->expects($this->never())
                    ->method('recordTimestamp');

        $this->event->expects($this->never())
                    ->method('record');

        $extra = [ 'smtp_transaction_id' => FALSE ];

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Span ID not available!');

        $method = $this->getReflectionMethod('afterSending');
        $method->invoke($this->class, TRUE, [ 'example@mail.com', 'John Doe' ], [], [], 'subject', 'body', 'from@mail.com', $extra);

        $this->unmockFunction('microtime');
    }

    /**
     * Test that the afterSending works correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::afterSending
     */
    public function testAfterSendingThrowsErrorWhenParentSpanIdCannotBeFound(): void
    {
        $this->mockFunction('microtime', fn() => 1724932394.128985);

        $this->setReflectionPropertyValue('startTimestamp', 1724932393.008985);
        $this->setReflectionPropertyValue('Mailer', 'smtp');
        $this->setReflectionPropertyValue('MIMEHeader', 'Content-Type: text/plain');
        $this->setReflectionPropertyValue('MIMEBody', 'full mime body');
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Info);

        $this->controller->shouldReceive('getTraceID')
                         ->once()
                         ->andReturn('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->controller->shouldReceive('getSpanID')
                         ->once()
                         ->andReturn('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->controller->shouldReceive('getParentSpanID')
                         ->once()
                         ->andReturn(NULL);

        $this->controller->shouldReceive('getSpanSpecificTags')
                         ->once()
                         ->andReturn([]);

        $this->logger->expects($this->once())
                     ->method('newEvent')
                     ->with('outbound_requests_log')
                     ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('setTraceID')
                    ->with('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->event->expects($this->once())
                    ->method('setSpanID')
                    ->with('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->event->expects($this->never())
                    ->method('setParentSpanID');

        $this->event->expects($this->once())
                    ->method('addTags')
                    ->with([
                        'type'   => 'SMTP',
                        'status' => '200',
                        'domain' => 'localhost',
                    ]);

        $this->event->expects($this->once())
                    ->method('addFields')
                    ->with([
                        'startTimestamp' => 1724932393.008985,
                        'endTimestamp'   => 1724932394.128985,
                        'executionTime'  => 1.12,
                        'url'            => 'localhost',
                    ]);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('record');

        $extra = [ 'smtp_transaction_id' => FALSE ];

        $method = $this->getReflectionMethod('afterSending');
        $method->invoke($this->class, TRUE, [ 'example@mail.com', 'John Doe' ], [], [], 'subject', 'body', 'from@mail.com', $extra);

        $this->unmockFunction('microtime');
    }

    /**
     * Test that the afterSending works correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::afterSending
     */
    public function testAfterSendingWorksCorrectlyWithInfoLevel(): void
    {
        $this->mockFunction('microtime', fn() => 1724932394.128985);

        $this->setReflectionPropertyValue('startTimestamp', 1724932393.008985);
        $this->setReflectionPropertyValue('Mailer', 'smtp');
        $this->setReflectionPropertyValue('MIMEHeader', 'Content-Type: text/plain');
        $this->setReflectionPropertyValue('MIMEBody', 'full mime body');
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Info);

        $this->controller->shouldReceive('getTraceID')
                         ->once()
                         ->andReturn('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->controller->shouldReceive('getSpanID')
                         ->once()
                         ->andReturn('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->controller->shouldReceive('getParentSpanID')
                         ->once()
                         ->andReturn('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->controller->shouldReceive('getSpanSpecificTags')
                         ->once()
                         ->andReturn([]);

        $this->logger->expects($this->once())
                     ->method('newEvent')
                     ->with('outbound_requests_log')
                     ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('setTraceID')
                    ->with('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->event->expects($this->once())
                    ->method('setSpanID')
                    ->with('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->event->expects($this->once())
                    ->method('setParentSpanID')
                    ->with('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->event->expects($this->once())
                    ->method('addTags')
                    ->with([
                        'type'   => 'SMTP',
                        'status' => '200',
                        'domain' => 'localhost',
                    ]);

        $this->event->expects($this->once())
                    ->method('addFields')
                    ->with([
                        'startTimestamp' => 1724932393.008985,
                        'endTimestamp'   => 1724932394.128985,
                        'executionTime'  => 1.12,
                        'url'            => 'localhost',
                    ]);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('record');

        $extra = [ 'smtp_transaction_id' => FALSE ];

        $method = $this->getReflectionMethod('afterSending');
        $method->invoke($this->class, TRUE, [ 'example@mail.com', 'John Doe' ], [], [], 'subject', 'body', 'from@mail.com', $extra);

        $this->unmockFunction('microtime');
    }

    /**
     * Test that the afterSending works correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::afterSending
     */
    public function testAfterSendingWorksCorrectlyWithDefaultSMTPPort(): void
    {
        $this->mockFunction('microtime', fn() => 1724932394.128985);

        $this->setReflectionPropertyValue('startTimestamp', 1724932393.008985);
        $this->setReflectionPropertyValue('Mailer', 'smtp');
        $this->setReflectionPropertyValue('MIMEHeader', 'Content-Type: text/plain');
        $this->setReflectionPropertyValue('MIMEBody', 'full mime body');
        $this->setReflectionPropertyValue('Host', 'smtp://smtp.example.com:25');
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Info);

        $this->controller->shouldReceive('getTraceID')
                         ->once()
                         ->andReturn('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->controller->shouldReceive('getSpanID')
                         ->once()
                         ->andReturn('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->controller->shouldReceive('getParentSpanID')
                         ->once()
                         ->andReturn('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->controller->shouldReceive('getSpanSpecificTags')
                         ->once()
                         ->andReturn([]);

        $this->logger->expects($this->once())
                     ->method('newEvent')
                     ->with('outbound_requests_log')
                     ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('setTraceID')
                    ->with('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->event->expects($this->once())
                    ->method('setSpanID')
                    ->with('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->event->expects($this->once())
                    ->method('setParentSpanID')
                    ->with('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->event->expects($this->once())
                    ->method('addTags')
                    ->with([
                        'type'   => 'SMTP',
                        'status' => '200',
                        'domain' => 'smtp.example.com',
                    ]);

        $this->event->expects($this->once())
                    ->method('addFields')
                    ->with([
                        'startTimestamp' => 1724932393.008985,
                        'endTimestamp'   => 1724932394.128985,
                        'executionTime'  => 1.12,
                        'url'            => 'smtp://smtp.example.com',
                    ]);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('record');

        $extra = [ 'smtp_transaction_id' => FALSE ];

        $method = $this->getReflectionMethod('afterSending');
        $method->invoke($this->class, TRUE, [ 'example@mail.com', 'John Doe' ], [], [], 'subject', 'body', 'from@mail.com', $extra);

        $this->unmockFunction('microtime');
    }

    /**
     * Test that the afterSending works correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::afterSending
     */
    public function testAfterSendingWorksCorrectlyWithCustomSMTPPort(): void
    {
        $this->mockFunction('microtime', fn() => 1724932394.128985);

        $this->setReflectionPropertyValue('startTimestamp', 1724932393.008985);
        $this->setReflectionPropertyValue('Mailer', 'smtp');
        $this->setReflectionPropertyValue('MIMEHeader', 'Content-Type: text/plain');
        $this->setReflectionPropertyValue('MIMEBody', 'full mime body');
        $this->setReflectionPropertyValue('Host', 'smtp://smtp.example.com:26');
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Info);

        $this->controller->shouldReceive('getTraceID')
                         ->once()
                         ->andReturn('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->controller->shouldReceive('getSpanID')
                         ->once()
                         ->andReturn('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->controller->shouldReceive('getParentSpanID')
                         ->once()
                         ->andReturn('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->controller->shouldReceive('getSpanSpecificTags')
                         ->once()
                         ->andReturn([]);

        $this->logger->expects($this->once())
                     ->method('newEvent')
                     ->with('outbound_requests_log')
                     ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('setTraceID')
                    ->with('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->event->expects($this->once())
                    ->method('setSpanID')
                    ->with('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->event->expects($this->once())
                    ->method('setParentSpanID')
                    ->with('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->event->expects($this->once())
                    ->method('addTags')
                    ->with([
                        'type'   => 'SMTP',
                        'status' => '200',
                        'domain' => 'smtp.example.com',
                    ]);

        $this->event->expects($this->once())
                    ->method('addFields')
                    ->with([
                        'startTimestamp' => 1724932393.008985,
                        'endTimestamp'   => 1724932394.128985,
                        'executionTime'  => 1.12,
                        'url'            => 'smtp://smtp.example.com:26',
                    ]);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('record');

        $extra = [ 'smtp_transaction_id' => FALSE ];

        $method = $this->getReflectionMethod('afterSending');
        $method->invoke($this->class, TRUE, [ 'example@mail.com', 'John Doe' ], [], [], 'subject', 'body', 'from@mail.com', $extra);

        $this->unmockFunction('microtime');
    }

    /**
     * Test that the afterSending works correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::afterSending
     */
    public function testAfterSendingWorksCorrectlyWithDefaultSMTPSPort(): void
    {
        $this->mockFunction('microtime', fn() => 1724932394.128985);

        $this->setReflectionPropertyValue('startTimestamp', 1724932393.008985);
        $this->setReflectionPropertyValue('Mailer', 'smtp');
        $this->setReflectionPropertyValue('MIMEHeader', 'Content-Type: text/plain');
        $this->setReflectionPropertyValue('MIMEBody', 'full mime body');
        $this->setReflectionPropertyValue('Host', 'smtps://smtp1.example.com:587');
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Info);

        $this->controller->shouldReceive('getTraceID')
                         ->once()
                         ->andReturn('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->controller->shouldReceive('getSpanID')
                         ->once()
                         ->andReturn('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->controller->shouldReceive('getParentSpanID')
                         ->once()
                         ->andReturn('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->controller->shouldReceive('getSpanSpecificTags')
                         ->once()
                         ->andReturn([]);

        $this->logger->expects($this->once())
                     ->method('newEvent')
                     ->with('outbound_requests_log')
                     ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('setTraceID')
                    ->with('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->event->expects($this->once())
                    ->method('setSpanID')
                    ->with('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->event->expects($this->once())
                    ->method('setParentSpanID')
                    ->with('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->event->expects($this->once())
                    ->method('addTags')
                    ->with([
                        'type'   => 'SMTP',
                        'status' => '200',
                        'domain' => 'smtp1.example.com',
                    ]);

        $this->event->expects($this->once())
                    ->method('addFields')
                    ->with([
                        'startTimestamp' => 1724932393.008985,
                        'endTimestamp'   => 1724932394.128985,
                        'executionTime'  => 1.12,
                        'url'            => 'smtps://smtp1.example.com',
                    ]);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('record');

        $extra = [ 'smtp_transaction_id' => FALSE ];

        $method = $this->getReflectionMethod('afterSending');
        $method->invoke($this->class, TRUE, [ 'example@mail.com', 'John Doe' ], [], [], 'subject', 'body', 'from@mail.com', $extra);

        $this->unmockFunction('microtime');
    }

    /**
     * Test that the afterSending works correctly.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::afterSending
     */
    public function testAfterSendingWorksCorrectlyWithCustomSMTPSPort(): void
    {
        $this->mockFunction('microtime', fn() => 1724932394.128985);

        $this->setReflectionPropertyValue('startTimestamp', 1724932393.008985);
        $this->setReflectionPropertyValue('Mailer', 'smtp');
        $this->setReflectionPropertyValue('MIMEHeader', 'Content-Type: text/plain');
        $this->setReflectionPropertyValue('MIMEBody', 'full mime body');
        $this->setReflectionPropertyValue('Host', 'smtps://smtp1.example.com:465');
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Info);

        $this->controller->shouldReceive('getTraceID')
                         ->once()
                         ->andReturn('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->controller->shouldReceive('getSpanID')
                         ->once()
                         ->andReturn('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->controller->shouldReceive('getParentSpanID')
                         ->once()
                         ->andReturn('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->controller->shouldReceive('getSpanSpecificTags')
                         ->once()
                         ->andReturn([]);

        $this->logger->expects($this->once())
                     ->method('newEvent')
                     ->with('outbound_requests_log')
                     ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('setTraceID')
                    ->with('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->event->expects($this->once())
                    ->method('setSpanID')
                    ->with('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->event->expects($this->once())
                    ->method('setParentSpanID')
                    ->with('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->event->expects($this->once())
                    ->method('addTags')
                    ->with([
                        'type'   => 'SMTP',
                        'status' => '200',
                        'domain' => 'smtp1.example.com',
                    ]);

        $this->event->expects($this->once())
                    ->method('addFields')
                    ->with([
                        'startTimestamp' => 1724932393.008985,
                        'endTimestamp'   => 1724932394.128985,
                        'executionTime'  => 1.12,
                        'url'            => 'smtps://smtp1.example.com:465',
                    ]);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('record');

        $extra = [ 'smtp_transaction_id' => FALSE ];

        $method = $this->getReflectionMethod('afterSending');
        $method->invoke($this->class, TRUE, [ 'example@mail.com', 'John Doe' ], [], [], 'subject', 'body', 'from@mail.com', $extra);

        $this->unmockFunction('microtime');
    }

    /**
     * Test that the afterSending works correctly with empty extra.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::afterSending
     */
    public function testAfterSendingWorksCorrectlyWithDetailedLevel(): void
    {
        $string  = 'b7rrrEKWPBBniam2zDQjn2QaYE5dAPLgfyTy2RbTPVykQDrYeq3HKjTKPLeSgaf8dTJNiatfrbGKMUBU4VYY8PphqxBZSe6mKuz2R7FVdcc9VZmAEkNDg7mfT7EPcvg';
        $string .= 'LgTKUihAfxc76CihMFqVpnU7e3iqWJdBPLnP34JQ2zQVBmSv8kvHjAGrv5fCVnPCEvbQx5PUNBukQVNFZukLtEtb2ZYy54JqjbHi4CF9kWV9MHq2Ah5A9vjYLxTBziT';
        $string .= 'MYcTCtXxcFCVYQ6awvkN9TdupdD7ihecSHB79JbqPSAVbRbz4ZFtnbe2aPzVRmVvkLDuFefmutDfGgKCizYMGJnExv6ViCryU4JZAufWxeag22BrDJ34aBRwbnCqwEa';
        $string .= 't2K6p45zvvCVpen5Z6VkQCiLGV5kGzfhb6cgUvnvyKK5tzjE7xx95PLupW8uPaCYyrpgT9RS8GQNf72qwnA5bebjRe3hi66KXLaJU2d5Tkpe4eRutgucvKFFBk8MxkY';

        $this->mockFunction('microtime', fn() => 1724932394.128985);

        $this->setReflectionPropertyValue('startTimestamp', 1724932393.008985);
        $this->setReflectionPropertyValue('Mailer', 'smtp');
        $this->setReflectionPropertyValue('MIMEHeader', 'Content-Type: text/plain');
        $this->setReflectionPropertyValue('MIMEBody', $string . 'E984TBDFDAKJF');
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Detailed);
        $this->setReflectionPropertyValue('SMTPOptions', [ 'SMTPExtra' => 'extra_value' ]);

        $this->controller->shouldReceive('getTraceID')
                         ->once()
                         ->andReturn('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->controller->shouldReceive('getSpanID')
                         ->once()
                         ->andReturn('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->controller->shouldReceive('getParentSpanID')
                         ->once()
                         ->andReturn('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->controller->shouldReceive('getSpanSpecificTags')
                         ->once()
                         ->andReturn([]);

        $this->logger->expects($this->once())
                     ->method('newEvent')
                     ->with('outbound_requests_log')
                     ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('setTraceID')
                    ->with('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->event->expects($this->once())
                    ->method('setSpanID')
                    ->with('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->event->expects($this->once())
                    ->method('setParentSpanID')
                    ->with('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->event->expects($this->once())
                    ->method('addTags')
                    ->with([
                        'type'   => 'SMTP',
                        'status' => '200',
                        'domain' => 'localhost',
                    ]);

        $options = [
            'SMTPPort'      => 25,
            'SMTPHelo'      => '',
            'SMTPSecure'    => '',
            'SMTPAutoTLS'   => TRUE,
            'SMTPAuth'      => FALSE,
            'SMTPUsername'  => '',
            'SMTPPassword'  => '',
            'SMTPKeepAlive' => FALSE,
            'SMTPAuthType'  => '',
            'SMTPTimeout'   => 300,
            'SMTPExtra'     => 'extra_value'
        ];

        $this->event->expects($this->once())
                    ->method('addFields')
                    ->with([
                        'startTimestamp' => 1724932393.008985,
                        'endTimestamp'   => 1724932394.128985,
                        'executionTime'  => 1.12,
                        'url'            => 'localhost',
                        'requestHeaders' => '{"Content-Type":"text\/plain"}',
                        'requestBody'    => $string . 'E984...',
                        'options'        => json_encode($options),
                    ]);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('record');

        $method = $this->getReflectionMethod('afterSending');
        $method->invoke($this->class, TRUE, [ 'example@mail.com', 'John Doe' ], [], [], 'subject', 'body', 'from@mail.com', []);

        $this->unmockFunction('microtime');
    }

    /**
     * Test that the afterSending works correctly with empty extra.
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::afterSending
     */
    public function testAfterSendingWorksCorrectlyWithFullLevel(): void
    {
        $string  = 'b7rrrEKWPBBniam2zDQjn2QaYE5dAPLgfyTy2RbTPVykQDrYeq3HKjTKPLeSgaf8dTJNiatfrbGKMUBU4VYY8PphqxBZSe6mKuz2R7FVdcc9VZmAEkNDg7mfT7EPcvg';
        $string .= 'LgTKUihAfxc76CihMFqVpnU7e3iqWJdBPLnP34JQ2zQVBmSv8kvHjAGrv5fCVnPCEvbQx5PUNBukQVNFZukLtEtb2ZYy54JqjbHi4CF9kWV9MHq2Ah5A9vjYLxTBziT';
        $string .= 'MYcTCtXxcFCVYQ6awvkN9TdupdD7ihecSHB79JbqPSAVbRbz4ZFtnbe2aPzVRmVvkLDuFefmutDfGgKCizYMGJnExv6ViCryU4JZAufWxeag22BrDJ34aBRwbnCqwEa';
        $string .= 't2K6p45zvvCVpen5Z6VkQCiLGV5kGzfhb6cgUvnvyKK5tzjE7xx95PLupW8uPaCYyrpgT9RS8GQNf72qwnA5bebjRe3hi66KXLaJU2d5Tkpe4eRutgucvKFFBk8MxkY';

        $this->mockFunction('microtime', fn() => 1724932394.128985);

        $this->setReflectionPropertyValue('startTimestamp', 1724932393.008985);
        $this->setReflectionPropertyValue('Mailer', 'smtp');
        $this->setReflectionPropertyValue('MIMEHeader', 'Content-Type: text/plain');
        $this->setReflectionPropertyValue('MIMEBody', $string . 'E984TBDFDAKJF');
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Full);
        $this->setReflectionPropertyValue('SMTPOptions', [ 'SMTPExtra' => 'extra_value' ]);

        $this->controller->shouldReceive('getTraceID')
                         ->once()
                         ->andReturn('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->controller->shouldReceive('getSpanID')
                         ->once()
                         ->andReturn('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->controller->shouldReceive('getParentSpanID')
                         ->once()
                         ->andReturn('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->controller->shouldReceive('getSpanSpecificTags')
                         ->once()
                         ->andReturn([]);

        $this->logger->expects($this->once())
                     ->method('newEvent')
                     ->with('outbound_requests_log')
                     ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('setTraceID')
                    ->with('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->event->expects($this->once())
                    ->method('setSpanID')
                    ->with('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->event->expects($this->once())
                    ->method('setParentSpanID')
                    ->with('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->event->expects($this->once())
                    ->method('addTags')
                    ->with([
                        'type'   => 'SMTP',
                        'status' => '200',
                        'domain' => 'localhost',
                    ]);

        $options = [
            'SMTPPort'      => 25,
            'SMTPHelo'      => '',
            'SMTPSecure'    => '',
            'SMTPAutoTLS'   => TRUE,
            'SMTPAuth'      => FALSE,
            'SMTPUsername'  => '',
            'SMTPPassword'  => '',
            'SMTPKeepAlive' => FALSE,
            'SMTPAuthType'  => '',
            'SMTPTimeout'   => 300,
            'SMTPExtra'     => 'extra_value'
        ];

        $this->event->expects($this->once())
                    ->method('addFields')
                    ->with([
                        'startTimestamp' => 1724932393.008985,
                        'endTimestamp'   => 1724932394.128985,
                        'executionTime'  => 1.12,
                        'url'            => 'localhost',
                        'requestHeaders' => '{"Content-Type":"text\/plain"}',
                        'requestBody'    => $string . 'E984TBDFDAKJF',
                        'options'        => json_encode($options),
                    ]);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('record');

        $method = $this->getReflectionMethod('afterSending');
        $method->invoke($this->class, TRUE, [ 'example@mail.com', 'John Doe' ], [], [], 'subject', 'body', 'from@mail.com', []);

        $this->unmockFunction('microtime');
    }

    /**
     * Test that the afterSending works correctly with a base 64 encoded body
     *
     * @covers Lunr\Ticks\PHPMailer\PHPMailer::afterSending
     */
    public function testAfterSendingWorksCorrectlyWithBase64Body(): void
    {
        $MIMEBody = 'SGksCllvdXIgTXlUQUcgb25lLXRpbWUgdmVyaWZpY2F0aW9uIGNvZGUgaXMKCjcwOTMzNiAKClBs
                     ZWFzZSBkbyBub3Qgc2hhcmUgdGhpcyBjb2RlIHdpdGggYW55b25lLiBJZiB5b3UgZGlkIG5vdCBt
                     YWtlIHRoaXMgcmVxdWVzdCwgcGxlYXNlIGlnbm9yZSB0aGlzIGVtYWlsLgoKSG9uZyBLb25nIElu
                     dGVybmF0aW9uYWwgQWlycG9ydAo=';

        $this->mockFunction('microtime', fn() => 1724932394.128985);

        $this->setReflectionPropertyValue('startTimestamp', 1724932393.008985);
        $this->setReflectionPropertyValue('Mailer', 'smtp');
        $this->setReflectionPropertyValue('MIMEHeader', 'Content-Type: text/plain');
        $this->setReflectionPropertyValue('MIMEBody', $MIMEBody);
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Full);
        $this->setReflectionPropertyValue('SMTPOptions', [ 'SMTPExtra' => 'extra_value' ]);
        $this->setReflectionPropertyValue('Encoding', PHPMailer::ENCODING_BASE64);

        $this->controller->shouldReceive('getTraceID')
                         ->once()
                         ->andReturn('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->controller->shouldReceive('getSpanID')
                         ->once()
                         ->andReturn('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->controller->shouldReceive('getParentSpanID')
                         ->once()
                         ->andReturn('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->controller->shouldReceive('getSpanSpecificTags')
                         ->once()
                         ->andReturn([]);

        $this->logger->expects($this->once())
                     ->method('newEvent')
                     ->with('outbound_requests_log')
                     ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('setTraceID')
                    ->with('bc5bfcc7-8d8d-4e59-b4be-7453b97410d');

        $this->event->expects($this->once())
                    ->method('setSpanID')
                    ->with('ef14c184-5b4a-4e0b-8026-7c5683e611c7');

        $this->event->expects($this->once())
                    ->method('setParentSpanID')
                    ->with('6cb28307-95b0-491e-a82a-9d679f511e43');

        $this->event->expects($this->once())
                    ->method('addTags')
                    ->with([
                        'type'   => 'SMTP',
                        'status' => '200',
                        'domain' => 'localhost',
                    ]);

        $options = [
            'SMTPPort'      => 25,
            'SMTPHelo'      => '',
            'SMTPSecure'    => '',
            'SMTPAutoTLS'   => TRUE,
            'SMTPAuth'      => FALSE,
            'SMTPUsername'  => '',
            'SMTPPassword'  => '',
            'SMTPKeepAlive' => FALSE,
            'SMTPAuthType'  => '',
            'SMTPTimeout'   => 300,
            'SMTPExtra'     => 'extra_value'
        ];

        $this->event->expects($this->once())
                    ->method('addFields')
                    ->with([
                        'startTimestamp' => 1724932393.008985,
                        'endTimestamp'   => 1724932394.128985,
                        'executionTime'  => 1.12,
                        'url'            => 'localhost',
                        'requestHeaders' => '{"Content-Type":"text\/plain"}',
                        'requestBody'    => base64_decode($MIMEBody),
                        'options'        => json_encode($options),
                    ]);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('record');

        $method = $this->getReflectionMethod('afterSending');
        $method->invoke($this->class, TRUE, [ 'example@mail.com', 'John Doe' ], [], [], 'subject', 'body', 'from@mail.com', []);

        $this->unmockFunction('microtime');
    }

}

?>
