<?php

// use Illuminate\Support\Str;

if (!function_exists('convertToBoolIfPossible')) {
    /**
     * @param string $value
     *
     * @return bool|string
     */
    function convertToBoolIfPossible(string $value)
    {
        switch ($value) {
            case 'true':
            case '1':
                return true;
            case 'false':
            case '0':
                return false;
            default:
                return $value;
        }
    }
}

if (!function_exists('getModelClassByName')) {
    /**
     * @param string $name
     *
     * @return string
     */
    function getModelClassByName(string $name): string
    {
        // converts a string to its singular form
        $modelName = Str::singular($name);

        // the first character capitalized
        $modelName = Str::title($modelName);

        // replaces a given string within the string
        $modelName = Str::of($modelName)->replace('-', '');

        return config('app.models_path').$modelName;
    }
}

if (!function_exists('wrapText')) {
    function wrapText($text, $lineLength)
    {
        // Разбиваем текст на отдельные слова
        $words = explode(' ', $text);

        $wrappedText = '';
        $line = '';

        foreach ($words as $word) {
            // Checking if a word is an article
            $isArticle = preg_match('/^(a|an|the)$/i', $word);

            // Checking if a word will exceed the character limit in a string
            if (mb_strlen($line.$word) > $lineLength) {
                $wrappedText .= "<p>{$line}</p>";
                $line = '';
            }

            $line .= ($line != '' ? ' ' : '').$word;

            // If the word is an article, then add a nonbreakable space
            if ($isArticle) {
                $line .= '&nbsp;';
            }
        }

        // Adding the last line
        $wrappedText .= "<p>{$line}</p>";

        return $wrappedText;
    }
}
