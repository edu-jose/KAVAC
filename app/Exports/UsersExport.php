<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths
{
    use Exportable;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(): Collection
    {
        $users = User::with('roles', 'permissions')->orderBy('name')->get();
        $rows = new Collection();

        foreach ($users as $user) {
            // Fila principal del usuario con todos sus datos
            $rows->push($this->formatUserRow($user));

            // Fila para los roles
            foreach ($user->roles as $role) {
                $rows->push($this->formatRoleRow($user, $role));
            }

            // Fila para los permisos
            foreach ($user->permissions as $permission) {
                $rows->push($this->formatPermissionRow($user, $permission));
            }
        }

        return $rows;
    }

    /**
     * Formatea la fila principal del usuario.
     */
    protected function formatUserRow(User $user)
    {
        return (object) [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'active' => $user->active ? 'Sí' : 'No',
            'email_verified_at' => $user->email_verified_at ? 'Sí' : 'No',
            'created_at' => $user->created_at,
            'last_login' => $user->last_login,
            'type' => '',
            'details_name' => '',
            'details_description' => '',
            'details_prefix' => '',
        ];
    }

    /**
     * Formatea la fila del rol.
     */
    protected function formatRoleRow(User $user, $role)
    {
        return (object) [
            'id' => '',
            'name' => '',
            'username' => '',
            'email' => '',
            'active' => '',
            'email_verified_at' => '',
            'created_at' => '',
            'last_login' => '',
            'type' => 'Rol',
            'details_name' => $role->name,
            'details_description' => $role->description,
            'details_prefix' => '',
        ];
    }

    /**
     * Formatea la fila del permiso.
     */
    protected function formatPermissionRow(User $user, $permission)
    {
        return (object) [
            'id' => '',
            'name' => '',
            'username' => '',
            'email' => '',
            'active' => '',
            'email_verified_at' => '',
            'created_at' => '',
            'last_login' => '',
            'type' => 'Permiso',
            'details_name' => $permission->name,
            'details_description' => $permission->short_description ?? 'Sin descripción',
            'details_prefix' => $permission->model_prefix,
        ];
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            'ID',
            'NOMBRE',
            'NOMBRE DE USUARIO',
            'CORREO',
            'ACTIVO',
            'VERIFICADO',
            'CREADO',
            'ÚLTIMO INICIO DE SESIÓN',
            'TIPO (Rol/Permiso)',
            'Detalle (Nombre)',
            'Detalle (Descripción)',
            'Detalle (Módulo)',
        ];
    }

    /**
    * @param mixed $row
    * @return array
    */
    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->username,
            $row->email,
            $row->active,
            $row->email_verified_at,
            $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
            $row->last_login ? $row->last_login->format('Y-m-d H:i:s') : '',
            $row->type,
            $row->details_name,
            $row->details_description,
            $row->details_prefix,
        ];
    }

    /**
     * Define los anchos de las columnas.
     *
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // ID
            'B' => 30,   // Nombre
            'C' => 15,   // Nombre de Usuario
            'D' => 45,   // Correo
            'E' => 10,   // Activo
            'F' => 10,   // Verificado
            'G' => 25,   // Creado
            'H' => 25,   // Último inicio de sesión
            'I' => 20,   // Tipo (Rol, Permiso)
            'J' => 45,   // Detalle (Nombre)
            'K' => 50,   // Detalle (Descripción)
            'L' => 20,   // Detalle (Módulo)
        ];
    }
}
