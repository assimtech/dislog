<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Processor;

/**
 * @see https://www.php.net/mb_check_encoding
 */
class EnsureEncoding implements ProcessorInterface
{
    private string $encoding;
    /**
     * @var ?callable $fallbackReEncoder
     */
    private $fallbackReEncoder;
    private ?\Psr\Log\LoggerInterface $psrLogger;

    public function __construct(
        string $encoding = 'UTF-8',
        ?callable $fallbackReEncoder = null,
        ?\Psr\Log\LoggerInterface $psrLogger = null
    ) {
        $this->encoding = $encoding;
        $this->fallbackReEncoder = $fallbackReEncoder;
        $this->psrLogger = $psrLogger;
    }

    public function __invoke(
        ?string $payload
    ): ?string {
        if (null === $payload) {
            return null;
        }

        if (\mb_check_encoding($payload, $this->encoding)) {
            return $payload;
        }

        $exception = new \InvalidArgumentException("Invalid payload encoding, expected {$this->encoding}");
        if (null === $this->psrLogger) {
            \trigger_error($exception->getMessage(), \E_USER_WARNING);
        } else {
            $this->psrLogger->warning($exception->getMessage(), [
                'encoding' => $this->encoding,
                'exception' => $exception,
                'payload' => $payload,
            ]);
        }

        if (null === $this->fallbackReEncoder) {
            return null;
        }

        return \call_user_func($this->fallbackReEncoder, $payload);
    }
}
