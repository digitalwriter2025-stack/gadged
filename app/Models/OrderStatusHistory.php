<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Timeline entry for a fulfilment ("status") or payment ("payment") change.
 */
#[Fillable(['order_id', 'type', 'from_status', 'to_status', 'note', 'user_id'])]
class OrderStatusHistory extends Model
{
    public const UPDATED_AT = null;

    public const TYPE_STATUS = 'status';

    public const TYPE_PAYMENT = 'payment';

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
