<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MangaLocale extends Model
{
    public $timestamps = false;
    protected $fillable = ['MangaID', 'LocaleID', 'Title'];
}
