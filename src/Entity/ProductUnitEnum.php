<?php

namespace App\Entity;

enum ProductUnitEnum: string
{
    case ML = "millilitres";
    case G = "grams";
    case PKG = "packages";
}
