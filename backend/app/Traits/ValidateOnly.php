<?php
namespace App\Traits;

trait ValidateOnly
{
    protected function validateOnly(): bool
    {
        return request()->is("*/validate") || request()->is("*/validate/*");
    }
}
