<?php

namespace Modules\Iuser\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Imagina\Icore\Traits\hasEventsWithBindings;
use Imagina\Icore\Traits\HasOptionalTraits;

use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Contracts\OAuthenticatable;

//use App\Notifications\ResetPasswordNotification;


class User extends Authenticatable implements OAuthenticatable
{

    use HasApiTokens, HasFactory, Notifiable, HasOptionalTraits, hasEventsWithBindings;

    protected $table = 'iuser__users';
    public $transformer = 'Modules\Iuser\Transformers\UserTransformer';
    public $repository = 'Modules\Iuser\Repositories\UserRepository';
    public $requestValidation = [
        'create' => 'Modules\Iuser\Http\Requests\CreateUserRequest',
        'update' => 'Modules\Iuser\Http\Requests\UpdateUserRequest',
        'login' => 'Modules\Iuser\Http\Requests\LoginUserRequest',
        'resetPassword' => 'Modules\Iuser\Http\Requests\ResetPasswordUserRequest',
        'resetPasswordComplete' => 'Modules\Iuser\Http\Requests\ResetPasswordCompleteUserRequest',
    ];
    //Instance external/internal events to dispatch with extraData
    public $dispatchesEventsWithBindings = [
        //eg. ['path' => 'path/module/event', 'extraData' => [/*...optional*/]]
        'created' => [],
        'creating' => [],
        'updated' => [],
        'updating' => [],
        'deleting' => [],
        'deleted' => []
    ];
    //public $translatedAttributes = [];
    protected $fillable = [
        'email',
        'password',
        'permissions',
        'first_name',
        'last_name',
        'is_guest'
    ];

    public $modelRelations = [
        'roles' => 'belongsToMany'
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

    /**
     * RELATIONS
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class,'iuser__role_user')->withTimestamps();
    }


    /**
     * Send a password reset notification to the user.
     */
    public function sendPasswordResetNotification($token): void
    {

        $url = env('APP_URL') . "/reset-password?token=" . $token;
        \Log::info("Iuser::User||Token: " . $token);

        //$this->notify(new ResetPasswordNotification($url));
    }


}
