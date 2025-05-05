<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shortcode extends Model
{
    protected $fillable = [
        'countryName',
        'see_offer_button_text',
        'find_out_more_button_text',
        'api_key',
    ];
}
