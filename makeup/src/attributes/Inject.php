<?php

namespace makeUp\src\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Inject
{
    public function __construct(
        public $service = ""
    )
    {
    }
}
