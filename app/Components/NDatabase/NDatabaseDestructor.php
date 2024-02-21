<?php

namespace Components\NDatabase;

class NDatabaseDestructor
{
    public function __destruct()
    {
        NDatabase::destruct();
    }
}
