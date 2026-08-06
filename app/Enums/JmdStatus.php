<?php

namespace App\Enums;

enum JmdStatus: string
{
    case Draft = 'draft';
    case MaterialIncomplete = 'material_incomplete';
    case MaterialTesting = 'material_testing';
    case ReadyToCalculate = 'ready_to_calculate';
    case CalculationCompleted = 'calculation_completed';
    case TrialMix = 'trial_mix';
    case AwaitingStrengthTest = 'awaiting_strength_test';
    case Completed = 'completed';
    case Approved = 'approved';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::MaterialIncomplete => 'Data material belum lengkap',
            self::MaterialTesting => 'Pengujian material',
            self::ReadyToCalculate => 'Siap dihitung',
            self::CalculationCompleted => 'Perhitungan selesai',
            self::TrialMix => 'Trial mix',
            self::AwaitingStrengthTest => 'Menunggu uji kuat tekan',
            self::Completed => 'Selesai',
            self::Approved => 'Disahkan',
            self::Archived => 'Diarsipkan',
        };
    }
}
