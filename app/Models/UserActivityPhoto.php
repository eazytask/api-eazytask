<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityPhoto extends Model
{
    use HasFactory;

    /**
     * Get the user's image.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function sign_in(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => asset($value),
        );
    }

    /**
     * Get the user's image.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function sign_out(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => asset($value),
        );
    }
}
