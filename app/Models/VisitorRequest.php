<?php

namespace App\Models;

use App\Models\Concerns\IsVisitorRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * Base of the requests visitors send from the site (quote requests, contact
 * messages, job applications): status, assignee, read state, activity log.
 */
abstract class VisitorRequest extends Model
{
    use IsVisitorRequest;
}
