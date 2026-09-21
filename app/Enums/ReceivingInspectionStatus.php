<?php

namespace App\Enums;

enum ReceivingInspectionStatus: string
{
    case Pending = 'pending';
    case Correct = 'correct';
    case Incorrect = 'incorrect';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Not checked',
            self::Correct => 'Correct item',
            self::Incorrect => 'Wrong item',
        };
    }
}
