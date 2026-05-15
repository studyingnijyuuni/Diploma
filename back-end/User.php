<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

//#[Fillable(['name', 'email', 'password'])]
//#[Hidden(['PasswordHash', 'remember_token'])]
class User extends Authenticatable
{
    // 2. Add HasApiTokens to the use statement inside the class
    use HasApiTokens, Notifiable;
    protected $primaryKey = 'UserID';
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = null; 

    protected $fillable = [
        'Username', 
        'Email', 
        'PasswordHash', 
        'TelegramID', 
        'TelegramConfirmationCode',
        'PreferredLocale' // From our previous step!
    ];

    protected $hidden = ['PasswordHash'];

    public function getAuthPassword()
    {
        return $this->PasswordHash;
    }

    public function favoriteMangas()
    {
        return $this->belongsToMany(Manga::class, 'favorites', 'UserID', 'MangaID')
                    ->withPivot('IsEmailNotificationsOn', 'IsTelegramNotificationsOn', 'AddedAt');
    }

    public function preferredLocale()
    {
        return $this->belongsTo(Locale::class, 'PreferredLocale', 'LocaleID');
    }
}
