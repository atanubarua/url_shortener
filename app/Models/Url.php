<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    protected const CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected const BASE = 62;
    protected const CODE_LENGTH = 5;

    protected $guarded = [];

    public function generateHash($url) {
        $normalizedUrl = strtolower(trim(($url)));
        return hash('sha256', $normalizedUrl);
    }

    public function getExistingUrl($hash, $user_id = null, $lock_for_update = false) {
        if (!empty($user_id)) {
            $query = $this->where('hash', $hash)->where('user_id', $user_id);
        } else {
            $query = $this->where('hash', $hash);
        }

        if ($lock_for_update) {
            return $query->lockForUpdate()->first();
        } else {
            return $query->first();
        }
    }

    public function encode(int $number): string {
        if ($number == 0) {
            return self::CHARACTERS[0];
        }

        $result = '';

        while($number > 0) {
            $remainder = $number % self::BASE;
            $result = self::CHARACTERS[$remainder] . $result;
            $number = (int) ($number / self::BASE);
        }

        return $result;
    }

    public function generateCode(int $id): string {
        $code = $this->encode($id);

        if (strlen($code) < self::CODE_LENGTH) {
            $code = str_pad($code, self::CODE_LENGTH, self::CHARACTERS[0], STR_PAD_LEFT);
        }

        return $code;
    }
}
