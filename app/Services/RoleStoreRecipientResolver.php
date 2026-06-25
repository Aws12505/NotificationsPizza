<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class RoleStoreRecipientResolver
{
    /**
     * Whether users assigned to "all stores" (store_id === 'all') should be
     * included when a send targets one or more specific stores.
     */
    public function includeAllStoresDefault(): bool
    {
        return (bool) config('nats.role_targeting.include_all_stores', true);
    }

    /**
     * Build a User query for everyone matching the given role/store selector.
     *
     * - roles given, stores empty  => those roles across every store
     * - stores given, roles empty  => every role within those stores
     * - both given                 => those roles within those stores
     *
     * Selecting from users by primary key means chunkById() over the result
     * keyset-paginates with constant memory and dedupes users automatically.
     *
     * @param  string[]  $roles
     * @param  string[]  $stores
     */
    public function usersQuery(array $roles, array $stores, ?bool $includeAllStores = null): Builder
    {
        $includeAll = $includeAllStores ?? $this->includeAllStoresDefault();

        return User::query()->whereIn('id', function ($q) use ($roles, $stores, $includeAll) {
            $q->select('user_id')->from('user_store_roles')->where('active', true);

            if (!empty($roles)) {
                $q->whereIn('role_name', $roles);
            }

            if (!empty($stores)) {
                $q->where(function ($w) use ($stores, $includeAll) {
                    $w->whereIn('store_id', $stores);
                    if ($includeAll) {
                        $w->orWhere('store_id', 'all')->orWhereNull('store_id');
                    }
                });
            }
        });
    }
}
