<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $primaryKey = 'user_id';
    protected $appends = ['public_profile_image'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [
        'user_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getPublicProfileImageAttribute(){
        return $this->profile_image ? asset('storage') . '/' . $this->profile_image : asset('assets/sample-images/default-profile.png');
    }

    public function dietaryPreferences()
    {
        return $this->belongsToMany(DietaryPreference::class, 'user_dietary_preferences', 'user_id', 'dietary_preference_id');
    }

    public function allergies()
    {
        return $this->belongsToMany(Allergy::class, 'user_allergies', 'user_id', 'allergy_id');
    }

    public function likedRecipes()
    {
        return $this->belongsToMany(Recipe::class, 'like_recipes', 'user_id', 'recipe_id');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }
}
