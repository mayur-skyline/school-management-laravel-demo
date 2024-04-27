<?php

namespace App\Util\Builder\Pshe;

abstract class Builder 
{
    abstract public function index(bool $isTTWEnabled, bool $isUsteerEnabled, bool $isFootprintEnabled): array;
}