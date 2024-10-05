<?php

namespace Saidabdulsalam\LaravelMemo\Enums;
use Saidabdulsalam\LaravelMemo\Enums\MethodsTrait;
enum MemoStatus: int
{
    use MethodsTrait;

    const DRAFT = 0;
    const SUBMITTED = 1;
    const APPROVED = 2;
    const REJECTED = 3;
    const PENDING = 4;
}
