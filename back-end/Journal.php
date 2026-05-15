<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Journal extends Model
{
    protected $primaryKey = 'JournalID';
    public $timestamps = false;
    protected $fillable = ['Name', 'SourceLink', 'Description', 'Image', 'LastUpdated'];

    public function mangas()
    {
        return $this->hasMany(Manga::class, 'JournalID', 'JournalID');
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? 'data:image/png;base64,' . base64_encode($value) : null,
        );
    }

    public function translations()
    {
        return $this->hasMany(JournalLocale::class, 'JournalID', 'JournalID');
    }

    public function getLocalizedTitleAttribute()
    {
        $userLocaleId = auth('sanctum')->check() ? auth('sanctum')->user()->PreferredLocale : 2;

        if ($userLocaleId == 1) return ["localized_title"=>$this->attributes['Name'], "localized_description"=>$this->attributes['Description']];
        $translation = $this->translations->where('LocaleID', $userLocaleId)->first();

        if (!$translation && $userLocaleId !== 2) {
            $translation = $this->translations->where('LocaleID', 2)->first();
        }

        return $translation ? ["localized_title"=>$translation->Title, "localized_description"=>$translation->Description] : 
                            ["localized_title"=>$this->attributes['Name'], "localized_description"=>$this->attributes['Description']];
    }
}
