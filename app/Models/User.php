<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\BelongsToBarbershop;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class Users
 *
 * @property int $id
 * @property string $name
 * @property string|null $password
 * @property string|null $email
 * @property string|null $profile_photo
 * @property bool $status
 * @property string|null $type
 */
class User extends Authenticatable
{
    use CanResetPassword, HasApiTokens, Notifiable, BelongsToBarbershop;

    protected $primaryKey = 'id';

    protected $connection = 'mysql';

    protected $table = 'users';

    protected $casts = [
        'status' => 'bool',
    ];

    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'name',
        'password',
        'email',
        'profile_photo',
        'role',
        'first_access'
    ];

    // --- Validation Rules ---
    public static function createRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string'],
            'confirm_password' => ['required', 'same:password'],
        ];
    }

    public static function messages(): array
    {
        return [
            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser do tipo texto.',
            'confirm_password.required' => 'A confirmação de senha é obrigatória.',
            'confirm_password.same' => 'As senhas não coincidem.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.string' => 'O e-mail deve ser do tipo texto.',
            'email.email' => 'O e-mail deve ser um endereço válido.',
            'email.max' => 'O e-mail não pode ter mais de 100 caracteres.',
            'email.unique' => 'O e-mail informado já está em uso.',
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser do tipo texto.',
            'name.max' => 'O nome não pode ter mais de 100 caracteres.',
            'profile_photo.file' => 'A foto de perfil deve ser um arquivo válido.',
            'profile_photo.image' => 'A foto de perfil deve ser uma imagem.',
            'profile_photo.max' => 'A foto de perfil não pode ser maior que 4MB.',
            'first_access.boolean' => 'O campo first_access deve ser verdadeiro ou falso.',
            'role.string' => 'O campo role deve ser do tipo texto.',
        ];
    }

    // --- Accessors ---

    public function setProfilePhotoAttribute($value)
    {
        $this->attributes['profile_photo'] = $value ?: 'https://ui-avatars.com/api/?name=' . (
            urlencode($this->name ?? $this->email)
        ) . '&background=random&size=128&rounded=true&format=svg';
    }

    // --- Mutators ---

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = mb_strtolower($value);
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = mb_strtolower($value);
    }

    public function setRoleAttribute($value)
    {
        $this->attributes['role'] = mb_strtolower($value) ?: 'user';
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    // --- JWT Methods ---

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPasswordName()
    {
        return 'password';
    }

    public function getJWTCustomClaims()
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'profile_photo' => $this->profile_photo,
            'first_access' => $this->first_access,
            'role' => $this->role,
        ];
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function favorite_menus()
    {
        return $this->belongsToMany(Menu::class, 'users_favorite_menus', 'user_id', 'menu_id');
    }
}
