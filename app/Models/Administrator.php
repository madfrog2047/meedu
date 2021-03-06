<?php

/*
 * This file is part of the Qsnh/meedu.
 *
 * (c) XiaoTeng <616896861@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Administrator extends Authenticatable
{
    use Notifiable;

    protected $table = 'administrators';

    protected $fillable = [
        'name', 'email', 'password', 'last_login_ip',
        'last_login_date',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = [
        'edit_url', 'destroy_url',
    ];

    /**
     * 从Request上创建管理员用户.
     *
     * @param Request $request
     *
     * @return Administrator
     */
    public static function createFromRequest(Request $request)
    {
        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'last_login_ip' => '',
            'last_login_date' => '',
        ];
        $self = new self($data);
        $self->save();

        return $self;
    }

    /**
     * 管理员包含的角色.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            AdministratorRole::class,
            'administrator_role_relation',
            'administrator_id',
            'role_id'
        );
    }

    /**
     * 当前管理员是否可以删除.
     *
     * @return bool
     */
    public function couldDestroy()
    {
        return $this->roles()->where('slug', config('meedu.administrator.super_slug'))->exists();
    }

    public function getEditUrlAttribute()
    {
        return route('backend.administrator.edit', $this);
    }

    public function getDestroyUrlAttribute()
    {
        return route('backend.administrator.destroy', $this);
    }

    /**
     * 是否存在指定角色.
     *
     * @param AdministratorRole $role
     *
     * @return bool
     */
    public function hasRole(AdministratorRole $role)
    {
        return $this->roles()->where('id', $role->id)->exists();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'admin_id');
    }
}
