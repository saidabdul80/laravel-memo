<?php

namespace YourVendor\Memo\Enums;

enum MemoType: int
{
    use MethodsTrait;

    const REQUEST = 0;
    const CIRCULAR = 1;
}
