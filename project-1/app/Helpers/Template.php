<?php

namespace App\Helpers;

class Template
{
    /**
     * Process template
     *
     * @param string $template
     * @param array $data
     * @param boolean $keepUndefined
     * @return string
     */
    public static function transform($template, array $data, $keepUndefined = false)
    {
        return preg_replace_callback('/[{]([^}]+)[}]/', function($matches) use($data, $keepUndefined) {
            $key = $matches[1];
            $options = [''];

            if (strpos($key, '?') !== false) {
                // Make options
                // {var?option1:option2} - binary option
                // {var?option1} - default option
                list($key, $default) = explode('?', $key, 2);
                $options = explode(':', $default, 2);
            }

            $key = strtolower($key);

            if ($keepUndefined && ! array_key_exists($key, $data)) {
                // Keep the tag as is as we may want to process it later
                return $matches[0];
            }

            if (count($options) === 2) {
                // Resolve binary option
                return ($data[$key] ?? false) ? $options[0] : $options[1];
            }

            // Return key mapped value of default option
            return ! empty($data[$key]) ? $data[$key] : $options[0];
        }, $template);
    }
}
