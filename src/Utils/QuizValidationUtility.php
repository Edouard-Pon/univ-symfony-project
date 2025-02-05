<?php

namespace App\Utils;

class QuizValidationUtility
{
    // TODO - add validation for single / multiple choice
    public static function validateJsonData(array $data): bool
    {
        if (!isset($data['quiz'])) {
            return false;
        }

        if (!isset($data['quiz']['title']) ||
            !isset($data['quiz']['questions'])) {
            return false;
        }

        foreach ($data['quiz']['questions'] as $question) {
            if (!isset($question['id']) ||
                !isset($question['type']) ||
                !isset($question['question']) ||
                !isset($question['options'])) {
                return false;
            }

            foreach ($question['options'] as $option) {
                if (!isset($option['id']) ||
                    !isset($option['text']) ||
                    !isset($option['correct'])) {
                    return false;
                }
            }
        }

        return true;
    }
}
