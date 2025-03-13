<?php

namespace App\Http\Controllers;

use App\Models\StatusInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class StatusInfoController extends Controller
{
    // supplier tables
    protected $supplier_tables = [
        'nature_infos',
        'store_infos',
        'address_infos',
        'finance_infos',
        'documents_infos',
        'contract_infos',
        'legal_infos',
    ];

    // customer tables
    protected $customer_tables = [
        'nature_infos',
        // 'store_infos',
        // 'address_infos',
        // 'finance_infos',
        // 'documents_infos',
        // 'contract_infos',
        'legal_infos',
    ];
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * We have two options here:
     * 1. If the user is supplier, we should check if he has already filled the form or not.
     * 2. If the user is customer, we should check if he has already filled the form or not.
     *
     * params: Request $request
     * @return: void
     */
    public function create(Request $request): void
    {
        // Hint: we must remove user::find(1); and use $request->user(); instead of it
        // in this time im do it without user OAuth 2.0, So find user with id 1
        // $user = user::find(1);
        $user = $request->user();
        // must check is status table for user supplier/customer is empty or not and it's there with all fields or not
        $count = $user->status()->count();

        // Its depend on user type (supplier/customer). Because each user type has different tables but process is same
        $user_tables = $user->type == 'supplier' ? $this->supplier_tables : $this->customer_tables;
        $user_tables_count = count($user_tables);

        // Initial status for supplier/customer
        // TODO: must check wich table is created and wich tabe most deleted if not exists in $user_tables
        // must get user all tables and check if not exists in $user_tables, delete it
        $user->status()->whereNotIn('table_name', $user_tables)->delete();
        if ($count === 0 || $count < $user_tables_count) {
            foreach ($user_tables as $table) {
                // !$user->status()->exists()
                // check exists or not and create if not exists
                if (!$user->status()->where('table_name', $table)->exists()) {
                    $user->status()->create([
                        'user_id' => $user->id,
                        'table_name' => $table,
                        'is_approved' => false,
                        'is_editable' => true,
                        'is_failed' => false,
                    ]);
                }
            }
        }
    }
    public function createByUser(User $user): void
    {
        // must check is status table for user supplier/customer is empty or not and it's there with all fields or not
        $count = $user->status()->count();

        // Its depend on user type (supplier/customer). Because each user type has different tables but process is same
        $user_tables = $user->type == 'supplier' ? $this->supplier_tables : $this->customer_tables;
        $user_tables_count = count($user_tables);

        // Initial status for supplier/customer
        // TODO: must check wich table is created and wich tabe most deleted if not exists in $user_tables
        // must get user all tables and check if not exists in $user_tables, delete it
        $user->status()->whereNotIn('table_name', $user_tables)->delete();
        if ($count === 0 || $count < $user_tables_count) {
            foreach ($user_tables as $table) {
                // !$user->status()->exists()
                // check exists or not and create if not exists
                if (!$user->status()->where('table_name', $table)->exists()) {
                    $user->status()->create([
                        'user_id' => $user->id,
                        'table_name' => $table,
                        'is_approved' => false,
                        'is_editable' => true,
                        'is_failed' => false,
                    ]);
                }
            }
        }
    }
    /**
     * Get full status of user from auth user
     *
     * @return array
     */
    public function checkUserStatus(Request $request): array
    {
        // $user = user::find(1);
        $user = $request->user();
        // must check is status table for user supplier/customer is empty or not and it's there with all fields or not
        $count = $user->status()->count();
        // Its depend on user type (supplier/customer). Because each user type has different tables but process is same
        $user_tables = $user->type == 'supplier' ? $this->supplier_tables : $this->customer_tables;
        // must get user all tables and check if not exists in $user_tables, delete it
        $user->status()->whereNotIn('table_name', $user_tables)->delete();
        $user_tables_count = count($user_tables);
        // If for any reason, the user has not filled the form, we should create it
        if ($count === 0 || $count < $user_tables_count)
            $this->create($request);
        return $user->status()->get()->toArray();
    }

    /**
     * Get status of special user from admin
     * @param int $user_id
     * @return array
     */
    public function checkUserStatusFromAdmin(User $user)
    {
        // $user = User::find($user_id);
        // must check is status table for user supplier/customer is empty or not and it's there with all fields or not
        $count = $user->status()->count();
        // Its depend on user type (supplier/customer). Because each user type has different tables but process is same
        $user_tables = $user->type == 'supplier' ? $this->supplier_tables : $this->customer_tables;
        // must get user all tables and check if not exists in $user_tables, delete it
        $user->status()->whereNotIn('table_name', $user_tables)->delete();
        $user_tables_count = count($user_tables);
        // If for any reason, the user has not filled the form, we should create it
        if ($count === 0 || $count < $user_tables_count)
            $this->createByUser($user);
        return $user->status()->get()->toArray();
    }

    /**
     * Check which item need user to fill
     * @return array
     */
    public function filledStatus(Request $request): array
    {
        $user_full_status = $this->checkUserStatus($request);
        $user_status = [];
        foreach ($user_full_status as $status) {
            // $user_status[$status['table_name']] = (!$status['is_approved'] && $status['is_editable'] && !$status['is_failed']) ? false : true;
            $user_status[$status['table_name']]  = [
                'is_approved' => $status['is_approved'] === 1 ? true : false,
                'is_editable' => $status['is_editable'] === 1 ? true : false,
                'is_failed' => $status['is_failed'] === 1 ? true : false,
            ];
        }
        return $user_status;
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(StatusInfo $statusInfo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StatusInfo $statusInfo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StatusInfo $statusInfo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StatusInfo $statusInfo)
    {
        //
    }
}
