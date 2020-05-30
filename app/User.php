<?php
namespace App;

use Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Validation\Validator as ValidatorConcrete;
use Validator;

/**
 * Class User
 *
 * @package App
 * @property string name
 * @property string email
 * @property string avatar
 * @property string password
 * @property bool horizon_allowed
 * @property bool disabled
 */
class User extends Authenticatable {

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'horizon_allowed',
        'disabled',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'horizon_allowed' => 'boolean',
        'disabled' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    /**
     * @var ValidatorConcrete
     */
    private $validator;


    /**
     * Hash password on the fly
     *
     * @param $value
     */
    public function setPasswordAttribute($value) {
        $this->attributes['password'] = Hash::make($value);
    }


    /**
     * @return bool
     */
    public function validates() {
        $this->validator = Validator::make($this->toArray(), static::getRules());

        return $this->validator->passes();
    }


    /**
     * @return ValidatorConcrete
     */
    public function getValidator(): ValidatorConcrete {
        return $this->validator;
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function repositories() {
        return $this->hasMany(Repository::class);
    }


    /**
     * @return array
     */
    public static function getRules(): array {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
        ];
    }
}
