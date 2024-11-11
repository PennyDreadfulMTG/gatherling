<?php

declare(strict_types=1);

namespace Gatherling\Helpers\Types;

enum TypeMismatch: string
{
    case NOT_OF_TYPE = 'not_of_type';
    case NULL = 'null';
    case NOT_SCALAR = 'not_scalar';
    case NOT_ARRAY = 'not_array';
    case INVALID_KEY_TYPE = 'invalid_key_type';
    case INVALID_VALUE_TYPE = 'invalid_value_type';
    case NOT_INT_COMPATIBLE = 'not_int_compatible';
    case NOT_WHOLE_NUMBER = 'not_whole_number';
}
