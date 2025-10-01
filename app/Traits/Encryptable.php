<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

trait Encryptable
{
    /**
     * Automatically encrypt attributes before saving.
     */
    public static function bootEncryptable()
    {
        static::saving(function ($model) {
            if (property_exists($model, 'encrypted') && is_array($model->encrypted)) {
                foreach ($model->encrypted as $attribute) {
                    if (!array_key_exists($attribute, $model->attributes)) {
                        continue; // not set
                    }

                    $value = $model->attributes[$attribute];

                    if ($value === null) {
                        $model->attributes[$attribute] = null;
                        continue;
                    }

                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }

                    if (!is_string($value)) {
                        $value = (string) $value;
                    }

                    // Encrypt before saving
                    $model->attributes[$attribute] = Crypt::encryptString($value);
                }
            }
        });
    }

    /**
     * Decrypt an attribute manually.
     */
    public function decryptAttribute(string $attribute)
    {
        if (!property_exists($this, 'encrypted') || !in_array($attribute, $this->encrypted)) {
            return $this->getAttribute($attribute);
        }

        $raw = $this->getAttributes()[$attribute] ?? null;
        if ($raw === null) {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($raw);
            $json = json_decode($decrypted, true);
            return json_last_error() === JSON_ERROR_NONE ? $json : $decrypted;
        } catch (\Exception $e) {
            Log::warning("Decrypt failed for {$attribute} on model " . get_class($this) . ": " . $e->getMessage());
            return null;
        }
    }

    /**
     * Transparently decrypt encrypted attributes on access.
     */
    public function getAttributeValue($key)
    {
        if (property_exists($this, 'encrypted') && in_array($key, $this->encrypted)) {
            $raw = $this->getAttributes()[$key] ?? null;
            if ($raw === null) {
                return null;
            }

            try {
                $decrypted = Crypt::decryptString($raw);
                $json = json_decode($decrypted, true);
                return json_last_error() === JSON_ERROR_NONE ? $json : $decrypted;
            } catch (\Exception $e) {
                Log::warning("Decrypt failed for {$key} on model " . get_class($this) . ": " . $e->getMessage());
                return null;
            }
        }

        return parent::getAttributeValue($key);
    }

    /**
     * Make sure arrays & JSON exports also return decrypted values.
     */
    public function toArray()
    {
        $array = parent::toArray();

        if (property_exists($this, 'encrypted') && is_array($this->encrypted)) {
            foreach ($this->encrypted as $attribute) {
                if (array_key_exists($attribute, $array) && $array[$attribute] !== null) {
                    try {
                        $decrypted = Crypt::decryptString($this->attributes[$attribute]);
                        $json = json_decode($decrypted, true);
                        $array[$attribute] = json_last_error() === JSON_ERROR_NONE ? $json : $decrypted;
                    } catch (\Exception $e) {
                        $array[$attribute] = null;
                    }
                }
            }
        }

        return $array;
    }
}
