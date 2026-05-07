<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\HealthChecks;

use Throwable;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class RabbitMqHealthCheck extends HealthCheck
{
    protected string $name = 'rabbitmq';

    public function status(): Status
    {
        $host = (string) config('queue.connections.rabbitmq.hosts.0.host', 'rabbitmq');
        $port = (int) config('queue.connections.rabbitmq.hosts.0.port', 5672);
        $timeout = 2;

        try {
            $socket = @fsockopen($host, $port, $errorCode, $errorMessage, $timeout);

            if ($socket === false) {
                return $this->problem('Could not connect to RabbitMQ', [
                    'host' => $host,
                    'port' => $port,
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                ]);
            }

            fclose($socket);

            return $this->okay([
                'host' => $host,
                'port' => $port,
            ]);
        } catch (Throwable $exception) {
            return $this->problem('RabbitMQ check failed', [
                'host' => $host,
                'port' => $port,
                'exception' => [
                    'message' => $exception->getMessage(),
                    'class' => $exception::class,
                ],
            ]);
        }
    }
}
