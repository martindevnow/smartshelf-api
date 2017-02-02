<?php

namespace Martindevnow\Smartshelf\Smartshelf\ACL;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Martindevnow\Smartshelf\ACL\Traits\HasRoles;
use Martindevnow\Smartshelf\Core\Contact;
use Martindevnow\Smartshelf\Core\Report;
use Martindevnow\Smartshelf\Retailer\Location;

class User extends Authenticatable
{
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Return whether or not a User is assigned to a Location
     *
     * @param Location $location
     * @return mixed
     */
    public function isAssignedTo(Location $location)
    {
        return !! $this->locations()->where('id', $location->id)->count();

    }

    /**
     * Assign a User to a Location
     *
     * @param Location $location
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function assignToLocation(Location $location)
    {
        return $this->locations()->save($location);

    }

    /**
     * Remove a User to a Location
     *
     * @param Location $location
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function removeFromLocation(Location $location)
    {
        return $this->locations()->detach($location->id);

    }

    /**
     * A User can run (and so) hasMany Reports
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Many various Users can be assigned to Many various Locations
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function locations()
    {
        return $this->belongsToMany(Location::class, 'location_user');
    }

    /**
     * A User can create various Contacts
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
