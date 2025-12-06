<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class CustomersWebController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Customer::class);
        return Inertia::render('panel/Customers/indexCustomers');
    }
}
