<?php

namespace Billing;

interface IBillable
{
    public function save($overWriteIfExists = false):string;
    public function computeConsumption($document): array;
}