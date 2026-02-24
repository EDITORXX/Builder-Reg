<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\Result\ResultInterface;

class QrCodeService
{
    public function generatePng(string $data, int $size = 300): string
    {
        $result = $this->build($data, $size);

        return $result->getString();
    }

    public function build(string $data, int $size = 300): ResultInterface
    {
        return (new Builder(data: $data, size: $size))->build();
    }
}
