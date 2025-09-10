<?php

/**
 * This file contains the PHPMailer class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Ticks\PHPMailer;

use Lunr\Ticks\AnalyticsDetailLevel;
use Lunr\Ticks\EventLogging\EventLoggerInterface;
use Lunr\Ticks\TracingControllerInterface;
use Lunr\Ticks\TracingInfoInterface;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer as BaseMailer;
use RuntimeException;

/**
 * PHPMailer class
 */
class PHPMailer extends BaseMailer
{

    /**
     * Shared instance of the event logger
     * @var EventLoggerInterface
     */
    protected readonly EventLoggerInterface $eventLogger;

    /**
     * Shared instance of the info tracing controller
     * @var TracingControllerInterface&TracingInfoInterface
     */
    protected readonly TracingControllerInterface&TracingInfoInterface $tracingController;

    /**
     * Profiling level
     * @var AnalyticsDetailLevel
     */
    protected AnalyticsDetailLevel $analyticsDetailLevel;

    /**
     * Start time of the mail sending
     * @var float
     */
    protected float $startTimestamp;

    /**
     * Constructor.
     *
     * @param EventLoggerInterface                            $eventLogger Instance of an event logger
     * @param TracingControllerInterface&TracingInfoInterface $controller  Instance of a tracing controller
     * @param AnalyticsDetailLevel                            $level       Analytics detail level (defaults to Info)
     * @param bool|null                                       $exceptions  Should we throw external exceptions?
     */
    public function __construct(
        EventLoggerInterface $eventLogger,
        TracingControllerInterface&TracingInfoInterface $controller,
        AnalyticsDetailLevel $level = AnalyticsDetailLevel::Info,
        ?bool $exceptions = NULL
    )
    {
        $this->eventLogger          = $eventLogger;
        $this->tracingController    = $controller;
        $this->analyticsDetailLevel = $level;

        parent::__construct($exceptions);

        /**
         * The type of action_function is defined as string, but it should be callable, so we ignore the phpstan warning
         * @phpstan-ignore assign.propertyType
         */
        $this->action_function = [ $this, 'afterSending' ]; // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->startTimestamp);
        unset($this->analyticsDetailLevel);

        parent::__destruct();
    }

    /**
     * Sets the analytics details level
     *
     * @param AnalyticsDetailLevel $level Analytics detail level.
     *
     * @return void
     */
    public function setAnalyticsDetailLevel(AnalyticsDetailLevel $level): void
    {
        $this->analyticsDetailLevel = $level;
    }

    /**
     * Create a message and send it.
     * Uses the sending method specified by $Mailer.
     *
     * @return bool false on error - See the ErrorInfo property for details of the error
     */
    public function send(): bool
    {
        if ($this->analyticsDetailLevel === AnalyticsDetailLevel::None)
        {
            return parent::send();
        }

        $this->startTimestamp = microtime(as_float: TRUE);

        $this->tracingController->startChildSpan();

        try
        {
            $return = parent::send();
        }
        catch (Exception $e)
        {
            $this->failureHook();

            $this->tracingController->stopChildSpan();

            throw $e;
        }

        if ($return === FALSE)
        {
            $this->failureHook();
        }

        $this->tracingController->stopChildSpan();

        return $return;
    }

    /**
     * Hook for when the email failed.
     *
     * @return void
     */
    protected function failureHook(): void
    {
        // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        if ($this->Mailer !== 'smtp')
        {
            $this->afterSending(
                FALSE,
                $this->to, // @phpstan-ignore argument.type
                $this->cc, // @phpstan-ignore argument.type
                $this->bcc, // @phpstan-ignore argument.type
                $this->Subject, // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
                $this->MIMEBody,
                $this->From, // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
                []
            );
            return;
        }

        foreach ([ $this->to, $this->cc, $this->bcc ] as $toGroup)
        {
            foreach ($toGroup as $to)
            {
                $this->afterSending(
                    FALSE,
                    [ $to[0], $to[1] ], // @phpstan-ignore-line offsetAccess.nonOffsetAccessible
                    [],
                    [],
                    $this->Subject, // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
                    $this->MIMEBody,
                    $this->From, // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
                    []
                );
            }
        }

        return;
    }

    /**
     * Callback after each mail send
     *
     * @param bool                                          $isSent  Result of the send action
     * @param array{0: string,1?: string}                   $to      Email addresses of the recipients
     * @param array<array{0: string,1?: string}>            $cc      Cc email addresses
     * @param array<array{0: string,1?: string}>            $bcc     Bcc email addresses
     * @param string                                        $subject The subject
     * @param string                                        $body    The email body
     * @param string                                        $from    Email address of sender
     * @param array{smtp_transaction_id?: bool|string|null} $extra   Extra information of possible use
     *
     * @return void
     */
    protected function afterSending(bool $isSent, array $to, array $cc, array $bcc, string $subject, string $body, string $from, array $extra): void
    {
        if ($this->analyticsDetailLevel === AnalyticsDetailLevel::None)
        {
            return;
        }

        $endTimestamp = microtime(as_float: TRUE);

        $url = $this->Host; // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName

        // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        if ($this->Mailer === 'smtp')
        {
            $smtpUrl = explode(':', $url);

            if (($smtpUrl[0] === 'smtp' && isset($smtpUrl[2]) && $smtpUrl[2] === '25')
                || ($smtpUrl[0] === 'smtps' && isset($smtpUrl[2]) && $smtpUrl[2] === '587')
            )
            {
                $url = $smtpUrl[0] . ':' . $smtpUrl[1];
            }

            unset($smtpUrl);
        }

        $fields = [
            'startTimestamp' => $this->startTimestamp,
            'endTimestamp'   => $endTimestamp,
            'executionTime'  => (float) bcsub((string) $endTimestamp, (string) $this->startTimestamp, 4),
            'url'            => $url,
        ];

        if ($this->analyticsDetailLevel->atleast(AnalyticsDetailLevel::Detailed))
        {
            $options = [];

            // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
            if ($this->Mailer === 'smtp')
            {
                $options = [
                    'SMTPPort'      => $this->Port, // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
                    'SMTPHelo'      => $this->Helo, // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
                    'SMTPSecure'    => $this->SMTPSecure,
                    'SMTPAutoTLS'   => $this->SMTPAutoTLS,
                    'SMTPAuth'      => $this->SMTPAuth,
                    'SMTPUsername'  => $this->Username, // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
                    'SMTPPassword'  => $this->Password, // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
                    'SMTPKeepAlive' => $this->SMTPKeepAlive,
                    'SMTPAuthType'  => $this->AuthType, // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
                    'SMTPTimeout'   => $this->Timeout, // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
                ];

                $options += $this->SMTPOptions;
            }

            // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
            $body    = $this->Encoding === self::ENCODING_BASE64 ? base64_decode($this->MIMEBody) : $this->MIMEBody;
            $headers = json_encode($this->getMIMEHeaderArray());
            $options = json_encode($options + $extra);

            $fields['requestHeaders'] = is_bool($headers) ? NULL : $headers;
            $fields['requestBody']    = $this->prepareLogData($body);
            $fields['options']        = is_bool($options) ? NULL : $options;

            unset($headers, $options);
        }

        $tags = [
            'type'   => strtoupper($this->Mailer), // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
            'status' => $isSent ? '200' : '400',
            'domain' => parse_url($this->Host, PHP_URL_HOST) ?? $this->Host, // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        ];

        $event = $this->eventLogger->newEvent('outbound_requests_log');

        $event->setTraceId($this->tracingController->getTraceId() ?? throw new RuntimeException('Trace ID not available!'));
        $event->setSpanId($this->tracingController->getSpanId() ?? throw new RuntimeException('Span ID not available!'));

        $parentSpanID = $this->tracingController->getParentSpanId();

        if ($parentSpanID != NULL)
        {
            $event->setParentSpanId($parentSpanID);
        }

        $event->addTags(array_merge($this->tracingController->getSpanSpecificTags(), $tags));
        $event->addFields($fields);
        $event->recordTimestamp();
        $event->record();
    }

    /**
     * Prepare data according to loglevel.
     *
     * @param string $data Data to prepare for logging.
     *
     * @return string Prepare data to log.
     */
    private function prepareLogData(string $data): string
    {
        // If the profiling level is Detailed we want to log part of the info
        if ($this->analyticsDetailLevel === AnalyticsDetailLevel::Detailed && strlen($data) > 512)
        {
            return substr($data, 0, 512) . '...';
        }

        return $data;
    }

    /**
     * Get the MIME headers as an associative array.
     *
     * @return array<string, string>
     */
    protected function getMIMEHeaderArray(): array
    {
        $headers = [];

        $lines = preg_split('/\r?\n/', $this->MIMEHeader) ?: [];

        foreach ($lines as $line)
        {
            $line = trim($line);

            if ($line === '')
            {
                continue;
            }

            $parts = explode(': ', $line, 2);

            $headers[trim($parts[0])] = trim($parts[1]);
        }

        return $headers;
    }

}

?>
