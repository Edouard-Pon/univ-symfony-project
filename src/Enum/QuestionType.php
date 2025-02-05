<?php

namespace App\Enum;

enum QuestionType: string
{
    case SINGLE = 'single';
    case MULTIPLE = 'multiple';
}