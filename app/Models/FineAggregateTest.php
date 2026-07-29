<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class FineAggregateTest extends Model { use SoftDeletes; protected $guarded=[]; protected $casts=['tested_at'=>'date','received_at'=>'date']; }
