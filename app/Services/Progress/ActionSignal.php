<?php
namespace App\Services\Progress;

readonly class ActionSignal {
    public function __construct(public string $action) {}
}
