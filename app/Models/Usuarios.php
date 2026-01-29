<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class Usuarios
 *
 * @property int $id
 * @property string $nome
 * @property string|null $senha
 * @property string|null $email
 * @property string|null $foto_perfil
 * @property bool $status
 * @property string|null $tipo
 */
class Usuarios extends Authenticatable
{
    use CanResetPassword, HasApiTokens, Notifiable;

    protected $primaryKey = 'id';

    protected $connection = 'mysql';

    protected $table = 'usuarios';

    protected $casts = [
        'status' => 'bool',
    ];

    protected $hidden = [
        'senha',
    ];

    protected $fillable = [
        'nome',
        'senha',
        'email',
        'foto_perfil',
        'status',
        'tipo',
    ];

    // --- Validation Rules ---
    public static function createRules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:usuarios,email'],
            'senha' => ['required', 'string'],
            'confirmar_senha' => ['required', 'same:senha'],
        ];
    }

    public static function messages(): array
    {
        return [
            'senha.required' => 'A senha é obrigatória.',
            'senha.string' => 'A senha deve ser do tipo texto.',
            'confirmar_senha.required' => 'A confirmação de senha é obrigatória.',
            'confirmar_senha.same' => 'As senhas não coincidem.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.string' => 'O e-mail deve ser do tipo texto.',
            'email.email' => 'O e-mail deve ser um endereço válido.',
            'email.max' => 'O e-mail não pode ter mais de 100 caracteres.',
            'email.unique' => 'O e-mail informado já está em uso.',
            'nome.required' => 'O nome é obrigatório.',
            'nome.string' => 'O nome deve ser do tipo texto.',
            'nome.max' => 'O nome não pode ter mais de 100 caracteres.',
            'avatar.file' => 'O avatar deve ser um arquivo válido.',
            'avatar.image' => 'O avatar deve ser uma imagem.',
            'avatar.max' => 'O avatar não pode ser maior que 4MB.',
            'status.boolean' => 'O campo status deve ser verdadeiro ou falso.',
        ];
    }

    // --- Accessors ---

    public function setFotoPerfilAttribute($value)
    {
        $this->attributes['foto_perfil'] = $value ?: 'https://ui-avatars.com/api/?name=' . (
            urlencode($this->nome ?? $this->email)
        ) . '&background=random&size=128&rounded=true&format=svg';
    }

    // --- Mutators ---

    public function setNomeAttribute($value)
    {
        $this->attributes['nome'] = mb_strtolower($value);
    }

    public function setSenhaAttribute($value)
    {
        $this->attributes['senha'] = Hash::make($value);
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = mb_strtolower($value);
    }

    public function setTipoAttribute($value)
    {
        $this->attributes['tipo'] = mb_strtolower($value) ?: 1;
    }

    // --- JWT Methods ---

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPasswordName()
    {
        return 'senha';
    }

    public function getJWTCustomClaims()
    {
        return [
            'id' => (string) $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'avatar' => $this->foto_perfil,
            'tipo' => $this->tipo ?? null,
        ];
    }

    public function notificacoes()
    {
        return $this->hasMany(Notificacoes::class, 'usuario_id');
    }

    public function menus_favoritos()
    {
        return $this->belongsToMany(Menus::class, 'usuarios_menus_favoritos', 'usuario_id', 'menu_id');
    }
}
