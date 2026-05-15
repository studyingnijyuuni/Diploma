<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $primaryKey = 'TagID';
    public $timestamps = false;
    
    protected $fillable = ['Name']; 

    public function mangas()
    {
        return $this->belongsToMany(Manga::class, 'mangatags', 'TagID', 'MangaID');
    }
}
