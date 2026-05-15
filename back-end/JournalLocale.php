<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalLocale extends Model
{
    public $timestamps = false;
    protected $fillable = ['JournalID', 'LocaleID', 'Title', 'Description'];

}
