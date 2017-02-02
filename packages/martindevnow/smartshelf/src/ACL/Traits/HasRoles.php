<?php

namespace Martindevnow\Smartshelf\ACL\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Martindevnow\Smartshelf\ACL\Permission;
use Martindevnow\Smartshelf\ACL\Role;

trait HasRoles {

    /**
     * Return whether or not the User is an Admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return !! $this->hasRole('admin');
    }

    /**
     * Many Users can have Many various Roles
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Assign the given role to the user.
     *
     * @param  string $role
     * @return mixed
     */
    public function assignRole($role)
    {
        if (is_string($role))
            return $this->roles()->save(
                Role::whereCode($role)->firstOrFail()
            );

        return $this->roles()->save($role);
    }

    /**
     * Remove the given role to the user.
     *
     * @param  string $role
     * @return mixed
     */
    public function removeRole($role)
    {
        if (is_string($role))
            return $this->roles()->detach(
                Role::whereCode($role)->firstOrFail()->id
            );

        return $this->roles()->detach($role->id);
    }

    /**
     * Determine if the user has the given role.
     *
     * @param  mixed $role
     * @return boolean
     */
    public function hasRole($role)
    {
        if (is_string($role))
            return $this->roles->contains('code', $role);

        if ($role instanceof Role)
            return $this->roles()->whereCode($role->code)->count();

        if ($role instanceof Collection)
        {
            foreach ($role as $r)
                if ( $this->hasRole($r) )
                    return true;
            return false;
        }
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param  Permission $permission
     * @return boolean
     */
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
//            Log::info('testing if user has permission to '. $permission->code);
            return $this->hasRole(Permission::whereCode($permission)->firstOrFail()->roles);
        }

        if ($permission instanceof Permission) {
//            Log::info('testing if user has any roles of '. $permission->roles);
            return $this->hasRole($permission->roles);
        }

        if ($permission instanceof Collection) {
//            Log::info('testing if user has any permissions of '. $permission);
            foreach ($permission as $p)
                if ( $this->hasPermission($p) )
                    return true;
            return false;
        }

        Log::error('trait HasRoles->hasPermission was passed something that is not string, Permission or Collection');
        return false;
    }
}

