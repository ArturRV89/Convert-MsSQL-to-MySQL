<?php

namespace Helpers\Import;

class CLILoggerProgress
{
    private $limit = 0;
    private $position = 0;
    private $prevPosition = -1;
    public function clear(): CLILoggerProgress
    {
        $this->limit = 0;
        $this->position = 0;
        $this->prevPosition = -1;
        return $this;
    }

    public function setLimit(int $limit): CLILoggerProgress
    {
        $this->limit = $limit;
        return $this;
    }

    public function inc(): CLILoggerProgress
    {
        $this->position++;
        return $this;
    }

    public function getCurrentPos()
    {
        $cnt = strlen($this->limit);
        return sprintf("[%' {$cnt}d/%' {$cnt}d] ", $this->position, $this->limit);
    }

    public function getCuurentPosOnce()
    {
        if ($this->prevPosition != $this->position) {
            $this->prevPosition = $this->position;
            return $this->getCurrentPos();
        } else {
            $times = strlen($this->limit) * 2 + 4;
            return str_repeat(' ', $times);
        }
    }
}
