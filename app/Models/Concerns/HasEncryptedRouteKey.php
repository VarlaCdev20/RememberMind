<?php

namespace App\Models\Concerns;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

trait HasEncryptedRouteKey
{
    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        $key = (string) $this->getAttribute($this->getRouteKeyName());

        return $this->encryptRouteKey($key);
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Try to decrypt the value. If it fails, it's either tampered or an old direct ID
        // The requirement says: return 404 if it fails.
        try {
            $realKey = $this->decryptRouteKey($value);
        } catch (DecryptException $e) {
            abort(404);
        } catch (\Throwable $e) {
            abort(404);
        }

        return $this->where($field ?? $this->getRouteKeyName(), $realKey)->firstOrFail();
    }

    /**
     * Encrypt a given route key value.
     */
    protected function encryptRouteKey(string $value): string
    {
        $encrypted = Crypt::encryptString($value);

        return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
    }

    /**
     * Decrypt a given route key value.
     */
    protected function decryptRouteKey(string $value): string
    {
        $base64 = strtr($value, '-_', '+/');
        $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);

        $encrypted = base64_decode($base64, true);

        if ($encrypted === false) {
            throw new \RuntimeException('Invalid encrypted route key.');
        }

        return Crypt::decryptString($encrypted);
    }
}
