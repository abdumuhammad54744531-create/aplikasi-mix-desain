<?php

namespace App\Models\Jmd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CementSpecificGravityItem extends Model
{
    protected $table = 'cement_sg_items';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['calculation_snapshot' => 'array'];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(CementSpecificGravityTest::class, 'cement_sg_test_id');
    }
}
