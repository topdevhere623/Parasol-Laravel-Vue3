<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64FileUploadRule implements Rule
{
    /** @var array */
    protected array $mimes;

    /** @var int */
    protected int $size;

    /** @var string */
    protected string $message = '';

    /**
     * MediaUploadRule constructor.
     * @param string $mimes
     * @param int|null $size
     */
    public function __construct(string $mimes, ?int $size = 0)
    {
        $exploded_mime = explode(',', str_replace(' ', '', $mimes));
        $this->mimes = is_array($exploded_mime) ? $exploded_mime : [];
        $this->size = $size ? intval($size) : 20000;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (is_string($value)) {
            $exploded = explode(',', $value);
            $mime = explode('/', current($exploded));

            if (!in_array(next($mime), $this->mimes)) {
                $this->message = "The {$attribute} must type ".implode(',', $this->mimes).'.';
                return false;
            }
            $file = next($exploded);

            if (strlen($file) * 8 / 10000 > $this->size) {
                $this->message = "The {$attribute} max size ".$this->size.' Kb.';
                return false;
            };
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
