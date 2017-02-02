<?php

namespace Martindevnow\Smartshelf\ACL;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    /**
     * Fields that may be mass-assigned
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code'
    ];

    /**
     * Attach the given Permission to $this Role
     *
     * @param Permission $permission
     * @return bool
     */
    public function givePermissionTo(Permission $permission)
    {
        if ($this->hasPermission($permission))
            return true;

        $this->permissions()->attach($permission->id);
        return true;
    }

    /**
     * Detach the given Permission to $this Role
     *
     * @param Permission $permission
     * @return bool
     */
    public function removePermissionTo(Permission $permission)
    {
        if ( ! $this->hasPermission($permission))
            return true;

        $this->permissions()->detach($permission->id);
        return true;
    }

    /**
     * Determine whether or not $this Role has the given Permission
     *
     * @param Permission|String $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        if (is_string($permission))
            return $this->permissions->contains('code', $permission);

        return !! $this->permissions()->where('id', $permission->id)->count();
    }

    /**
     * Return whether or not $this Role has the given Permission
     *
     * @param $permission
     * @return bool
     */
    public function can($permission)
    {
        return !! $this->hasPermission($permission);
    }

    /**
     * Many Roles can have Many Permissions
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Many various Roles can be filled by Many various Users
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
