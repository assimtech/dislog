<?php

declare(strict_types=1);

namespace spec\Assimtech\Dislog\Processor;

use Assimtech\Dislog;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EnsureEncodingSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Dislog\Processor\EnsureEncoding::class);
    }

    function it_returns_null_for_null_payload()
    {
        $this->__invoke(null)->shouldReturn(null);
    }

    function it_returns_payload_when_encoding_matches()
    {
        $this->__invoke("hello")->shouldReturn("hello");
    }

    function it_returns_base64_encoded_payload_when_invalid(\Psr\Log\LoggerInterface $psrLogger)
    {
        $this->beConstructedWith('UTF-8', 'base64_encode', $psrLogger);
        $psrLogger->warning(
            Argument::type('string'),
            Argument::that(function ($context) {
                return isset($context['encoding'])
                    && $context['encoding'] === 'UTF-8'
                    && isset($context['exception'])
                    && $context['exception'] instanceof \InvalidArgumentException;
            })
        )->shouldBeCalled();

        $this->__invoke("\xFF")->shouldReturn("/w==");
    }

    function it_uses_fallback_reencoder_if_provided()
    {
        $capturedPayload = null;
        $fallback = function ($payload) use (&$capturedPayload) {
            $capturedPayload = $payload;
            return 'reencoded';
        };

        $this->beConstructedWith('UTF-8', $fallback, new \Psr\Log\NullLogger());

        $this->__invoke("\xFF")->shouldReturn('reencoded');

        if ($capturedPayload !== "\xFF") {
            throw new \RuntimeException('Fallback reencoder was not invoked with expected payload');
        }
    }

    function it_does_not_warn_when_payload_encoding_is_valid(\Psr\Log\LoggerInterface $psrLogger)
    {
        $this->beConstructedWith('UTF-8', null, $psrLogger);

        $psrLogger->warning(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->__invoke('hello')->shouldReturn('hello');
    }

    function it_warns_warning_when_encoding_invalid_and_no_fallback(\Psr\Log\LoggerInterface $psrLogger)
    {
        $this->beConstructedWith('UTF-8', null, $psrLogger);

        $psrLogger->warning(
            Argument::type('string'),
            Argument::that(function ($context) {
                return isset($context['encoding'])
                    && $context['encoding'] === 'UTF-8'
                    && isset($context['exception'])
                    && $context['exception'] instanceof \InvalidArgumentException;
            })
        )->shouldBeCalled();

        $this->__invoke("\xFF")->shouldReturn(null);
    }
}
