<?php

namespace Nikaia\NodeBridge\Tests;

use Nikaia\NodeBridge\BridgeException;
use Nikaia\NodeBridge\Runner;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    /** @test */
    public function it_runs_script_correctly()
    {
        $response = (new Runner())
            ->setScript(__DIR__ . '/_fixtures/ok.script.js')
            ->echoPipe('{"name":"John Doe"}')
            ->run();

        $this->assertEquals([
            'name' => 'John Doe',
            'age' => 30,
            'address' => [
                'street' => 'Main',
                'number' => 100,
            ],
        ], $response->json());
    }

    /** @test */
    public function it_fails_when_nothing_is_piped()
    {
        $this->expectException(BridgeException::class);
        $this->expectExceptionMessage('You must pipe a string to the nodejs script');

        (new Runner())
            ->setScript(__DIR__ . '/_fixtures/ok.script.js')
            ->run();
    }

    /** @test */
    public function it_fails_when_script_does_not_exist()
    {
        $this->expectException(BridgeException::class);
        $this->expectExceptionMessageMatches('/.*Error: Cannot find module.*/');

        (new Runner())
            ->setScript(__DIR__ . '/_fixtures/does-not-exist.script.js')
            ->echoPipe('{"name":"John Doe"}')
            ->run();
    }

    /** @test */
    public function it_fails_when_script_throws_error()
    {
        $this->expectException(BridgeException::class);
        $this->expectExceptionMessageMatches('/.*Error: Unexpected Error!*/');

        (new Runner())
            ->setScript(__DIR__ . '/_fixtures/error.script.js')
            ->echoPipe('{"name":"John Doe"}')
            ->run();
    }
}
