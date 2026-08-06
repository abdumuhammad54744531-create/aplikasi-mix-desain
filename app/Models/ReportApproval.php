<?php

namespace App\Models;

use App\Models\Jmd\Revision;
use Illuminate\Database\Eloquent\Model;

class ReportApproval extends Model
{
    protected $guarded = [];

    protected $casts = ['approved_at' => 'datetime', 'revoked_at' => 'datetime', 'authority_snapshot' => 'array'];

    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jmdRevision()
    {
        return $this->belongsTo(Revision::class, 'jmd_revision_id')->withTrashed();
    }
}
