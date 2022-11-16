<?php

declare(strict_types=1);

namespace Nikaia\NodeBridge;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Bridge
{
    /** @var string Full path to nodejs script file */
    protected string $script;

    /** @var string piped string */
    protected string $pipedString;

    public function __construct(
        /** @var string Node.js executable, you can specify the full path if in a custom location */
        protected string $nodePath = 'node'
    )
    {
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * @param string $nodePath
     * @return Bridge
     */
    public function setNode(string $nodePath): Bridge
    {
        $this->nodePath = $nodePath;

        return $this;
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
    public function setPipedString(string $pipedString): self
    {
        $this->pipedString = $pipedString;

        return $this;
    }

    /**
     * Echo the passed string and pipe it to the nodejs script
     * i.e : echo '...string...' | nodejs script.js.
     */
    public function pipeRaw(string $string): self
    {
        $escaped = str_replace("'", "\'", $string);

        return $this->setPipedString("echo '" . $escaped . "'");
    }

    /**
     * Pipe the passed array to the nodejs script after json encoding it.
     */
    public function pipe(array $input): self
    {
        return $this->pipeRaw(json_encode($input));
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

        $command = "{$this->pipedString} | {$this->nodePath} " . $this->script;

        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
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
