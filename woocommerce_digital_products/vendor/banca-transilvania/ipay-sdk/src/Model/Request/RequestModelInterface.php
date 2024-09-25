<?php

namespace BTransilvania\Api\Model\Request;

interface RequestModelInterface
{
    public function fromArray(array $data);
    public function buildRequest();
}
