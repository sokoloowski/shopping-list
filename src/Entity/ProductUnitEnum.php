<?php

namespace App\Entity;

enum ProductUnitEnum: string
{
    case PCS = "pieces";
    case ML = "millilitres";
    case L = "litres";
    case G = "grams";
    case KG = "kilograms";
    case PKG = "packages";
}
