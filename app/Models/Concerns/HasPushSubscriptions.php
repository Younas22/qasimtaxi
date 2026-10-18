<?php

namespace App\Models\Concerns;

use App\Models\PushSubscription;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Mixed into Admin, Customer, and Driver — the three otherwise-unrelated
 * "user" models in this app (see config/auth.php's three guards) — so each
 * gets the same pushSubscriptions() relation the brief asks for on a single
 * User model, without inventing a unified users table this app doesn't have.
 */
trait HasPushSubscriptions
{
    public function pushSubscriptions(): MorphMany
    {
        return $this->morphMany(PushSubscription::class, 'subscribable');
    }
}
