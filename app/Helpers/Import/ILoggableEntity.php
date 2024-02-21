<?php

namespace Import;

interface ILoggableEntity
{
    public function setLogger($logger): self;
    public function getEntityName(): string;
    public function getDescription(): string;
}
