<?php

namespace App\Validation;

use \Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Contracts\MediaManager;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Builder
{
    const MAX_STRING = 255;
    const MAX_TEXT = 65535;
    const MIN_PASSWORD_LEN = 8;
    const MIN_PIN_LEN = 4;

    /**
     * Convert rules to arrays
     *
     * @param array|Collection $rules
     * @return array
     */
    public static function get($rules)
    {
        $result = [];

        foreach ($rules as $key => $rule) {
            if ($rule instanceof Collection) {
                foreach(static::get($rule) as $subkey => $subrule) {
                    $result[$subkey === '' ? $key : "{$key}.{$subkey}"] = $subrule;
                }
            } elseif (is_object($rule)) {
                $result[$key] = $rule->get();
            } else {
                $result[$key] = $rule;
            }
        }

        return $result;
    }

    /**
     * Merge and concat rules
     */
    public static function mergeConcat(...$args)
    {
        $result = [];

        foreach ($args as $rules) {
            foreach (static::get($rules) as $key => $rule) {
                if (isset($result[$key])) {
                    $base = $result[$key];
                    if (is_array($base) && is_array($rule)) {
                        $result[$key] = array_merge($base, $rule);
                    } elseif (is_array($base) && is_string($rule)) {
                        $result[$key] = implode('|', $base) .'|'. $rule;
                    } elseif (is_string($base) && is_array($rule)) {
                        $result[$key] = $base .'|'. implode('|', $rule);
                    } else {
                        $result[$key] = $base .'|'. $rule;
                    }
                } else {
                    $result[$key] = $rule;
                }
            }
        }

        return $result;
    }

    /**
     * Merge rules
     */
    public static function merge(...$args)
    {
        $rulesArray = array_map(function($rules) {
            return static::get($rules);
        }, $args);
        return array_merge(...$rulesArray);
    }

    /**
     * Create new builder
     *
     * @param string $method
     * @param array $arguments
     * @return Builder
     */
    public static function __callStatic($method, $arguments)
    {
        $instance = new class
        {
            /**
             * @var array
             */
            protected $rules = [];

            /**
             * Get chain of rules
             *
             * @param bool $asArray
             * @return array
             */
            public function get($asArray = true)
            {
                return $asArray
                    ? $this->rules
                    : implode('|', $this->rules);
            }

            /**
             * Add custom rule
             *
             * @return Builder
             */
            public function append(...$rules)
            {
                foreach ($rules as $rule) {
                    $this->rules[] = $rule;
                }

                return $this;
            }

            /**
             * Add required rule
             *
             * @return Builder
             */
            public function required()
            {
                return $this->append('required');
            }

            /**
             * Add present rule
             *
             * @return Builder
             */
            public function present()
            {
                return $this->append('present');
            }

            /**
             * Add missing rule
             *
             * @return Builder
             */
            public function missing()
            {
                return $this->append('missing');
            }

            /**
             * Conditionally append rules
             *
             * @param bool $condition
             * @param Closure $closure
             * @return Builder
             */
            public function when($condition, $closure)
            {
                if ($condition) {
                    $closure($this);
                }

                return $this;
            }

            /**
             * Add rule that restricts stores to these which user can access
             *
             * @param User $user
             * @return Builder
             */
            public function accessibleStore(?User $user = null)
            {
                $user = $user ?: Auth::user();
                $allowedStores = $user->getAccessibleStoresIds();

                if ($allowedStores !== null) {
                    $this->in($allowedStores);
                }

                return $this;
            }

            /**
             * Add required_if rule
             *
             * @param string $field
             * @param mixed $value
             * @return Builder
             */
            public function requiredIf($field, $value = 'true', ...$args)
            {
                if (is_callable($field) || is_bool($field)) {
                    $actualRule = Rule::requiredIf($field)->__toString();
                    return $actualRule !== '' ? $this->append($actualRule) : $this;
                }

                $rule = sprintf('required_if:%s,%s', $field, $value);

                if (count($args) !== 0) {
                    $rule .= ','. implode(',', $args);
                }

                return $this->append($rule);
            }

            /**
             * Add required_unless rule
             *
             * @param string $field
             * @param mixed $value
             * @return Builder
             */
            public function requiredUnless($field, $value = 'true', ...$args)
            {
                $rule = sprintf('required_unless:%s,%s', $field, $value);

                if (count($args) !== 0) {
                    $rule .= ','. implode(',', $args);
                }

                return $this->append($rule);
            }

            /**
             * Add required_with rule
             *
             * @return Builder
             */
            public function requiredWith(...$args)
            {
                return $this->append('required_with:'. implode(',', $args));
            }

            /**
             * Add required_without rule
             *
             * @return Builder
             */
            public function requiredWithout(...$args)
            {
                return $this->append('required_without:'. implode(',', $args));
            }

            /**
             * Add sometimes rule
             *
             * @return Builder
             */
            public function sometimes()
            {
                return $this->append('sometimes');
            }

            /**
             * Add string rule
             *
             * @return Builder
             */
            public function string(...$args)
            {
                if (count($args) === 0) {
                    return $this->append('string', sprintf('max:%d', Builder::MAX_STRING));
                }

                if (count($args) === 1) {
                    return $this->append('string', sprintf('max:%d', $args[0]));
                }

                return $this->append('string',
                    sprintf('min:%d', $args[0]),
                    sprintf('max:%d', $args[1])
                );
            }

            /**
             * Add digits rule
             *
             * @return Builder
             */
            public function digits(...$args)
            {
                return $this->string(...$args)
                    ->append('regex:/^[0-9]+$/');
            }

            /**
             * Add color hex rule
             *
             * @return Builder
             */
            public function colorHex()
            {
                return $this->append('regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i');
            }

            /**
             * Add string rule
             *
             * @param int $len
             * @return Builder
             */
            public function text($len = Builder::MAX_TEXT)
            {
                return $this->append('string', sprintf('max:%d', $len));
            }

            /**
             * Add email rule
             *
             * @return Builder
             */
            public function email()
            {
                return $this->string()
                    ->append('email');
            }

            /**
             * Add phone rule
             *
             * @return Builder
             */
            public function phone()
            {
                return $this->append('string')
                    ->append('regex:/^[+][1-9][0-9]{1,14}$/');
            }

            /**
             * Add TFN rule
             *
             * @return Builder
             */
            public function tfn()
            {
                return $this->append('string')
                    ->append('regex:/^[0-9]{9}$/');
            }

            /**
             * Add ABN rule
             *
             * @return Builder
             */
            public function abn()
            {
                return $this->append('string')
                    ->append('regex:/^[0-9]{11}$/');
            }

            /**
             * Add mac_address rule
             *
             * @return Builder
             */
            public function macAddress()
            {
                return $this->string()
                    ->append('mac_address');
            }

            /**
             * Add country code rule
             *
             * @return Builder
             */
            public function countryCode()
            {
                return $this->string(2);
            }

            /**
             * Add min rule
             *
             * @param int|float $val
             * @return Builder
             */
            public function min($val)
            {
                return $this->append(sprintf('min:%d', $val));
            }

            /**
             * Add max rule
             *
             * @param int|float $val
             * @return Builder
             */
            public function max($val)
            {
                return $this->append(sprintf('max:%d', $val));
            }

            /**
             * Add between rule
             *
             * @param int|float $min
             * @param int|float $max
             * @return Builder
             */
            public function between($min, $max)
            {
                return $this->append(
                    sprintf('min:%d', $min),
                    sprintf('max:%d', $max)
                );
            }

            /**
             * Add integer rule
             *
             * @return Builder
             */
            public function integer()
            {
                return $this->append('integer');
            }

            /**
             * Add alpha numeric rule
             *
             * @return Builder
             */
            public function alphaNumeric()
            {
                return $this->append('alpha_num');
            }

            /**
             * Add bool rule
             *
             * @return Builder
             */
            public function boolean()
            {
                return $this->append('boolean');
            }

            /**
             * Add array rule
             *
             * @param array $keys
             * @return Builder|Collection
             */
            public function array($keys = null)
            {
                if ($keys !== null) {
                    if (is_numeric(key($keys))) {
                        return $this->append(sprintf('array:%s', implode(',', $keys)));
                    }

                    $allowKeys = array_filter(array_keys($keys), function($key) {
                        return strpos($key, '.') === false;
                    });
                    $this->append(sprintf('array:%s', implode(',', $allowKeys)));
                    return collect(array_merge([
                        '' => $this->rules,
                    ], $keys));
                }

                return $this->append('array');
            }

            /**
             * Add in rule
             *
             * @param array $vals
             * @return Builder
             */
            public function in($vals)
            {
                return $this->append(sprintf('in:%s', implode(',', $vals)));
            }

            /**
             * Add datetime rule
             *
             * @return Builder
             */
            public function datetime()
            {
                return $this->append('date_format:Y-m-d H:i:s');
            }

            /**
             * Add date rule
             *
             * @return Builder
             */
            public function date()
            {
                return $this->append('date_format:Y-m-d');
            }

            /**
             * Add time rule
             *
             * @return Builder
             */
            public function time()
            {
                return $this->append('date_format:H:i:s');
            }

            /**
             * Add gt rule
             *
             * @param string $field
             * @return Builder
             */
            public function gt($field)
            {
                return $this->append(sprintf('gt:%s', $field));
            }

            /**
             * Add gt rule
             *
             * @param string $field
             * @return Builder
             */
            public function lt($field)
            {
                return $this->append(sprintf('lt:%s', $field));
            }

            /**
             * Add after rule
             *
             * @param string $field
             * @return Builder
             */
            public function after($field)
            {
                return $this->append(sprintf('after:%s', $field));
            }

            /**
             * Add before rule
             *
             * @param string $field
             * @return Builder
             */
            public function before($field)
            {
                return $this->append(sprintf('before:%s', $field));
            }

            /**
             * Add numeric rule
             *
             * @return Builder
             */
            public function numeric()
            {
                return $this->append('numeric');
            }

            /**
             * Add distinct rule
             *
             * @param bool $ignoreCase
             * @return Builder
             */
            public function distinct($ignoreCase = true)
            {
                return $this->append('distinct' . ($ignoreCase ? ':ignore_case' : ''));
            }

            /**
             * Add password rule
             *
             * @return Builder
             */
            public function password()
            {
                return $this->append(
                    Password::min(Builder::MIN_PASSWORD_LEN)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised()
                );
            }

            /**
             * Add pin rule
             *
             * @return Builder
             */
            public function pin()
            {
                return $this->append('string')
                    ->min(Builder::MIN_PIN_LEN);
            }

            /**
             * Add current_password rule
             *
             * @return Builder
             */
            public function currentPassword()
            {
                return $this->append('current_password');
            }

            /**
             * Add confirmed rule
             *
             * @return Builder
             */
            public function confirmed()
            {
                return $this->append('confirmed');
            }

            /**
             * Add unique rule
             *
             * @return Builder
             */
            public function unique($table, $column = 'NULL', $setup = null)
            {
                $rule = Rule::unique($table, $column);

                if ($setup) {
                    $setup($rule);
                }

                return $this->append($rule->__toString());
            }

            /**
             * Add nullable rule
             *
             * @return Builder
             */
            public function nullable()
            {
                return $this->append('nullable');
            }

            /**
             * Add file rule
             *
             * @param array $mimes
             * @return Builder
             */
            public function file($mimes = null)
            {
                $this->append('file');

                if ($mimes !== null) {
                    $this->append(sprintf('mimetypes:%s', implode(',', $mimes)));
                }

                return $this;
            }

            /**
             * Add csv rule
             *
             * @return Builder
             */
            public function csv()
            {
                $this->append('file');

                return $this->append('mimetypes:text/csv,text/plain');
            }

            /**
             * Add csv rule
             *
             * @return Builder
             */
            public function xlsx()
            {
                $this->append('file');

                return $this->append('mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            }

            /**
             * Add image rule
             *
             * @param array $mimes
             * @param int $maxSize
             * @return Builder
             */
            public function image($mimes = MediaManager::IMAGE_MIMES,
                $maxSize = MediaManager::IMAGE_MAX_SIZE)
            {
                return $this->file($mimes)->max($maxSize);
            }

            /**
             * Add document rule
             *
             * @param array $mimes
             * @param int $maxSize
             * @return Builder
             */
            public function document($mimes = MediaManager::DOCUMENT_MIMES,
                $maxSize = MediaManager::DOCUMENT_MAX_SIZE)
            {
                return $this->file($mimes)->max($maxSize);
            }

            /**
             * Add GTIN rule
             *
             * @return Builder
             */
            public function gtin()
            {
                return $this->append('string')
                    ->append('regex:/^[0-9]{12}$/');
            }

            /**
             * Add website rule
             *
             * @return Builder
             */
            public function website()
            {
                return $this->string();
            }

            /**
             * Add url rule
             *
             * @return Builder
             */
            public function url($protocols = ['http', 'https'])
            {
                return $this->append(sprintf('url:%s', implode(',', $protocols)));
            }

            /**
             * Add exists rule
             *
             * @param string $param
             * @return Builder
             */
            public function exists($param)
            {
                return $this->append(sprintf('exists:%s', $param));
            }

            /**
             * Add prohibits rule
             *
             * @return Builder
             */
            public function prohibits(...$args)
            {
                return $this->append(sprintf('prohibits:%s', implode(',', $args)));
            }
        };

        if (!method_exists($instance, $method)) {
            throw new \Exception('Method '. __CLASS__ .'::'. $method .' does not exist');
        }

        return $instance->{$method}(...$arguments);
    }
}
