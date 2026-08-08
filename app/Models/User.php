<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'access_level', 'permissions', 'permissions_configured', 'must_change_password', 'is_active', 'employee_number', 'position', 'institution', 'approval_authority', 'photo_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'permissions' => 'array',
            'permissions_configured' => 'boolean',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function hasPermission(string $permission): bool
    {
        if (in_array($this->role, ['admin', 'administrator'], true)) {
            return true;
        }
        if ($this->role === 'pemohon') {
            return str_starts_with($permission, 'requests.');
        }
        // Akun yang sudah ada sebelum matriks izin ditambahkan memiliki nilai
        // mentah NULL. Pertahankan perilaku akses lamanya sampai administrator
        // secara eksplisit menyimpan matriks izin untuk akun tersebut.
        if (! $this->permissions_configured) {
            $action = str($permission)->afterLast('.')->toString();
            // Kolom access_level lama memiliki default database "edit". Model
            // yang baru dibuat dapat belum memuat default itu sampai direfresh.
            return in_array($action, ['view', 'print'], true) || $this->access_level !== 'read';
        }

        return in_array($permission, $this->permissions, true);
    }

    public function laboratoryWorkRequests()
    {
        return $this->hasMany(LaboratoryWorkRequest::class);
    }
}
