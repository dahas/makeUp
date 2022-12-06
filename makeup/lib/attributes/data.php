<?php

namespace makeUp\lib\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Data
{
    public function __construct(
        public string $table,
        public string $key,
        public string $columns,
    ) {
    }
}
