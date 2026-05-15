<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Scout\Searchable;

class Manga extends Model
{
    use Searchable;
    public function toSearchableArray()
    {
        return [
            'MangaID' => $this->MangaID,
            'JournalID' => $this->JournalID,
            'Title' => $this->Title, 
            'translations' => $this->translations->pluck('Title')->toArray(),
        ];
    }
    protected $fillable = [
        'Title', 'JournalID', 'SourceLink', 'LastReleaseDate', 
        'UpcomingReleaseDate', 'Image', 'ReleasesInfo', 'LastChapterName'
    ];
    protected $primaryKey = 'MangaID';
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'LastUpdated';

    protected $touches = ['journal']; 

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'JournalID', 'JournalID');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'mangatags', 'MangaID', 'TagID');
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'MangaID', 'UserID')
                    ->withPivot('IsEmailNotificationsOn', 'IsTelegramNotificationsOn');
    }

    public function updateLastRelease($lastChapterName, $lastReleaseDate)
    {
        $this->update([
            'LastChapterName' => $lastChapterName,
            'LastReleaseDate' => $lastReleaseDate,
            'UpcomingReleaseDate' => null,
        ]);
    }

    public function updateUpcomingRelease($releasesInfo, $upcomingReleaseDate)
    {
        $this->update([
            'ReleasesInfo' => $releasesInfo,
            'UpcomingReleaseDate' => $upcomingReleaseDate,
        ]);
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? 'data:image/png;base64,' . base64_encode($value) : null,
        );
    }

    public function translations()
    {
        return $this->hasMany(MangaLocale::class, 'MangaID', 'MangaID');
    }

    public function getLocalizedTitleAttribute()
    {
        $userLocaleId = auth('sanctum')->check() ? auth('sanctum')->user()->PreferredLocale : 1;

        if ($userLocaleId==1) return ["localized_title"=>$this->attributes['Title']];
        $translation = $this->translations->where('LocaleID', $userLocaleId)->first();

        if (!$translation && $userLocaleId !== 2) {
            $translation = $this->translations->where('LocaleID', 2)->first();
        }

        return $translation ? ["localized_title"=>$translation->Title] : ["localized_title"=>$this->attributes['Title']];
    }
}
