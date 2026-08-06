<?php

namespace App\Enums;

enum SpecimenType: string
{
    case Cylinder = 'cylinder';
    case Cube = 'cube';
    case Beam = 'beam';
    case Custom = 'custom';
}
