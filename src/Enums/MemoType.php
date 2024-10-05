<?php

namespace Saidabdulsalam\LaravelMemo\Enums;
use Saidabdulsalam\LaravelMemo\Enums\MethodsTrait;
enum MemoType: int
{
    use MethodsTrait;

    const REQUEST = 0;
    const CIRCULAR = 1;
}
