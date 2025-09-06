<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Node;

class ValveService
{
    public function __construct(private Node $node) {

    }

    private ?bool $isOpened = null;

    public function isOpened() : bool {
        if (blank($this->isOpened)) {
            $this->isOpened = $this->node->settings->where('name','opened')->first()?->value() > 0;
        }
        return $this->isOpened;
    }

    public function reset() : void {
        $this->isOpened = null;
    }
}
