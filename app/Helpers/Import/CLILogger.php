<?php

namespace Helpers\Import;

class CLILogger
{
    private $enabled = false;

    /** @var $prefix string */
    private $prefix = '';

    /** @var CLILoggerProgress */
    private $progress;
    private string $postfix;

    public function setProgress(CLILoggerProgress $progress)
    {
        $this->progress = $progress;
    }

    public function getProgress(): CLILoggerProgress
    {
        return $this->progress;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }
    private function echo(string $string, bool $echoPrefix = true)
    {
        if ($this->enabled) {
            if ($echoPrefix) {
                echo $this->progress->getCuurentPosOnce();
                echo "{$this->prefix}:";
            }
            echo $string;
        }
    }
    public function enable()
    {
        $this->enabled = true;
    }
    public function disable()
    {
        $this->enabled = false;
    }
    public function setWarning()
    {
        $this->echo("\033[33m", false);
        return $this;
    }
    public function setError()
    {
        $this->echo("\033[31m", false);
        return $this;
    }
    public function setSuccess()
    {
        $this->echo("\033[32m", false);
        return $this;
    }
    public function setNormal()
    {
        $this->echo("\033[0m", false);
        return $this;
    }
    public function add(ILoggableEntity $entity, $id = null)
    {
        $this->setSuccess();
        $this->echo("{$entity->getEntityName()} added {$entity->getDescription()} : id = {$id}\n");
        $this->setNormal();
        return $this;
    }

    public function exists(ILoggableEntity $entity, $id = null)
    {
        $this->setWarning();
        $this->echo("{$entity->getEntityName()} already exists {$entity->getDescription()} : id = {$id}\n");
        $this->setNormal();
        return $this;
    }

    public function message(ILoggableEntity $entity, string $msg)
    {
        if (!empty($this->postfix)) {
            $msg .= $this->postfix;
            $this->postfix = '';
        }
        $this->echo("{$entity->getEntityName()} : {$msg}\n");
        return $this;
    }

    public function simpleMessage(string $msg)
    {
        $this->echo("{$msg}\n", false);
        return $this;
    }

    public function setPostfix(string $postfix)
    {
        $this->postfix = $postfix;
    }
}
