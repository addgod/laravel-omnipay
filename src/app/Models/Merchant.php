<?php

namespace Addgod\DibsD2\app\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    protected $fillable = [
        'merchant_id',
        'key1',
        'key2',
        'username',
        'password',
        'lang',
        'currency',
        'pay_type',
    ];

    public function toConfig()
    {
        return [
            'merchantid'    => $this->merchant_id,
            'key1'          => $this->key1,
            'key2'          => $this->key2,
            'username'      => $this->username,
            'password'      => $this->password,
            'lang'          => $this->lang,
            'currency'      => $this->currency,
            'payType'       => $this->pay_type,
        ];
    }
}
