<?php

namespace Addgod\Omnipay\app\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    protected $fillable = [
        'name',
        'gateway',
        'config',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'config' => 'object',
    ];

    public function toArray()
    {
        return [
            'name' => $this->name,
            'gateway' => $this->gateway,
            'config' => (array) $this->config,
        ];
    }
}
