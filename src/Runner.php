<?php

declare(strict_types=1);

namespace Nikaia\NodeBridge;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Runner
{
    /** @var string Full path to nodejs script file */
    protected string $script;

    /** @var string piped string */
    protected string $pipedString;

    public function __construct(
        /** @var string Node.js executable, you can specify the full path if in a custom location */
        protected string $nodeExecutable = 'node'
    ) {
    }

    /**
     * Set the nodejs js script that the runner will execute.
     */
    public function setScript(string $script): self
    {
        $this->script = $script;

        return $this;
    }

    /**
     * Set the piped string that will be sent to the nodejs script.
     */
    public function pipe(string $pipedString): self
    {
        $this->pipedString = $pipedString;

        return $this;
    }

    /**
     * Echo the passed string and pipe it to the nodejs script
     * i.e : echo '...string...' | nodejs script.js.
     */
    public function echoPipe(string $string): self
    {
        $escaped = str_replace("'", "\'", $string);

        return $this->pipe("echo '" . $escaped . "'");
    }

    /**
     * Run the nodejs script.
     */
    public function run(): Response
    {
        if (empty($this->pipedString)) {
            throw new BridgeException('You must pipe a string to the nodejs script');
        }

        $this->setUtf8Context();

        $command = "{$this->pipedString} | {$this->nodeExecutable} " . $this->script;

        $process = Process::fromShellCommandline($command);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new BridgeException(
                (new ProcessFailedException($process))->getMessage()
            );
        }

        return new Response($process->getOutput());
    }

    /**
     * Fix issues where when node get executed by
     * the php process it will defaults to ascii and tries to
     * read strings in ascii, and mess everythings up.
     *
     * @see https://stackoverflow.com/a/13969829/146253
     */
    protected function setUtf8Context(): void
    {
        setlocale(LC_ALL, $locale = 'en_US.UTF-8');
        putenv('LC_ALL=' . $locale);
    }
}
