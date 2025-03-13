<?php

namespace App\Http\Controllers\Users\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\StatusInfoController;
use App\Models\product_characteristics;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

/**
 * This controller for manage supplier data from admin
 */
class SupplierController extends Controller
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

    // public function __construct()
    // {
    //     foreach ($this->supplier_tables as $table) {
    //         $functionName = $table . 'Show';
    //         $modelName = '\App\Models\\' . ucfirst($table);

    //         $fillableFields = (new $modelName())->getFillable();

    //         $this->{$functionName} = function (User $user) use ($table, $fillableFields) {
    //             $model = '\App\Models\\' . ucfirst($table);
    //             $info = $user->{$table}();
    //             $info = $info->exists() ? $info->get()->first()->toArray() : array_fill_keys($fillableFields, null);
    //             $status = $user->status()->where('table_name', $table)->get()->first();

    //             return response()->json([
    //                 'message' => 'اطلاعات کاربر با موفقیت دریافت شد.',
    //                 'is_success' => true,
    //                 'is_already_filled' => $info ? true : false,
    //                 'data' => $info,
    //                 'status' => $status,
    //                 'status_code' => 200,
    //             ]);
    //         };
    //     }
    // }


    /**
     * user status infos
     */
    public function status(User $user)
    {
        $status = $user->status()->get()->toArray();
        // length of status array
        if (count($status) < 1) {
            $StatusInfoController = new StatusInfoController();
            $status = $StatusInfoController->checkUserStatusFromAdmin($user);
        }

        return response()->json([
            'message' => 'اطلاعات کاربر با موفقیت دریافت شد.',
            'is_success' => true,
            'data' => $status,
            'status_code' => 200,
        ]);
    }

    /**
     * User nature infos
     */
    public function natureShow(User $user)
    {
        $nature = $user->natural();
        $nature = $nature->exists() ? $nature->get()->first()->toArray() : (new \App\Models\NaturalInfo())->getFillable();
        // user status
        $status = $user->status()->where('table_name', 'nature_infos')->get()->first();
        return response()->json([
            'message' => 'اطلاعات کاربر با موفقیت دریافت شد.',
            'is_success' => true,
            'is_already_filled' => $nature ? true : false,
            'data' => $nature,
            'status' => $status,
            'status_code' => 200,
        ]);
    }

    /**
     * User store infos
     */
    public function storeShow(User $user)
    {
        $store = $user->store();
        $store = $store->exists() ? $store->get()->first()->toArray() : (new \App\Models\StoreInfo())->getFillable();
        // user status
        $status = $user->status()->where('table_name', 'store_infos')->get()->first();
        return response()->json([
            'message' => 'اطلاعات کاربر با موفقیت دریافت شد.',
            'is_success' => true,
            'is_already_filled' => $store ? true : false,
            'data' => $store,
            'status' => $status,
            'status_code' => 200,
        ]);
    }
    /**
     * User address infos
     */
    public function addressShow(User $user)
    {
        $address = $user->address();
        $address = $address->exists() ? $address->get()->first()->toArray() : (new \App\Models\AddressInfo())->getFillable();
        // user status
        $status = $user->status()->where('table_name', 'address_infos')->get()->first();
        return response()->json([
            'message' => 'اطلاعات کاربر با موفقیت دریافت شد.',
            'is_success' => true,
            'is_already_filled' => $address ? true : false,
            'data' => $address,
            'status' => $status,
            'status_code' => 200,
        ]);
    }

    /**
     * User finance infos
     */
    public function financeShow(User $user)
    {
        $finance = $user->finance();
        $finance = $finance->exists() ? $finance->get()->first()->toArray() : (new \App\Models\FinanceInfo())->getFillable();

        // user status
        $status = $user->status()->where('table_name', 'finance_infos')->get()->first();
        return response()->json([
            'message' => 'اطلاعات کاربر با موفقیت دریافت شد.',
            'is_success' => true,
            'is_already_filled' => $finance ? true : false,
            'data' => $finance,
            'status' => $status,
            'status_code' => 200,
        ]);
    }

    /**
     * User documents infos
     */
    public function documentShow(User $user)
    {
        $documents = $user->document();
        $documents = $documents->exists() ? $documents->get()->first()->toArray() : (new \App\Models\DocumentsInfo())->getFillable();
        // user status
        $status = $user->status()->where('table_name', 'documents_infos')->get()->first();
        return response()->json([
            'message' => 'اطلاعات کاربر با موفقیت دریافت شد.',
            'is_success' => true,
            'is_already_filled' => $documents ? true : false,
            'data' => $documents,
            'status' => $status,
            'status_code' => 200,
        ]);
    }

    /**
     * User contract infos
     */
    public function contractShow(User $user)
    {
        $contract = $user->contract();
        $contract = $contract->exists() ? $contract->get()->first()->toArray() : (new \App\Models\ContractInfo())->getFillable();
        // user status
        $status = $user->status()->where('table_name', 'contract_infos')->get()->first();
        return response()->json([
            'message' => 'اطلاعات کاربر با موفقیت دریافت شد.',
            'is_success' => true,
            'is_already_filled' => $contract ? true : false,
            'data' => $contract,
            'status' => $status,
            'status_code' => 200,
        ]);
    }

    /**
     * User legal infos
     */
    public function legalShow(User $user)
    {
        $legal = $user->legal();
        $legal = $legal->exists() ? $legal->get()->first()->toArray() : (new \App\Models\LegalInfo())->getFillable();
        $nature = $user->natural();
        if ($nature->exists()) {
            if ($nature->get()->first()->is_legal_person == 0) {
                return response()->json([
                    'message' => 'کاربر مورد نظر اطلاعات رابط شرکت را تکمیل نکرده است و یا نوع کاربر حقوقی نمی باشد.',
                    'is_success' => false,
                    'is_already_filled' => false,
                    'data' => [],
                    'status_code' => 400,
                ]);
            }
        }
        $nature = $nature->exists() ? $nature->get()->first()->toArray() : (new \App\Models\NaturalInfo())->getFillable();
        // user status
        $status = $user->status()->where('table_name', 'legal_infos')->get()->first();
        return response()->json([
            'message' => 'اطلاعات کاربر با موفقیت دریافت شد.',
            'is_success' => true,
            'is_already_filled' => $legal ? true : false,
            'data' => $legal,
            'status' => $status,
            'status_code' => 200,
        ]);
    }

    /**
     * report controller
     *
     * Our reports stored in status table, its also contain status of user Doucments
     */

    public function reportNote(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            // valid table_name from supplier_tables
            'table_name' => 'required|string|in:' . implode(',', $this->supplier_tables),
            'note' => 'nullable|string|max:512',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات ارسالی نامعتبر است.',
                'is_success' => false,
                'status_code' => 400,
            ]);
        }

        $status = $user->status()->where('table_name', $request->table_name)->get()->first();
        // dd($user->status()->where('table_name', $table_name)->get()->first());

        if ($status) {
            $status->update([
                'note' => $request->note,
                'is_approved' => 0,
                'is_editable' => 1,
                'is_failed' => 1,
            ]);
        }

        return response()->json([
            'message' => 'گزارش با موفقیت ثبت شد.',
            'is_success' => true,
            'status_code' => 200,
        ]);
    }

    /**
     * approve controller
     * this is super function for approve all supplier tables
     * can approve all tables with one function
     * can import data from temp table to main table (status table data to $supplier_tables)
     * this function can approve edited data or main data, cant receive edited data from admin
     *
     * @param Request $request
     * @param User $user
     */
    public function approve(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            // valid table_name from supplier_tables
            'table_name' => 'required|string|in:' . implode(',', $this->supplier_tables),
            // is approved edited data or main data need to be approved
            // default approve_edited_data = false
            'approve_edited_data' => 'nullable|boolean',
            // is editeble data or not
            'is_editable' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات ارسالی نامعتبر است.',
                'is_success' => false,
                'status_code' => 400,
            ]);
        }

        $status = $user->status()->where('table_name', $request->table_name)->get()->first();

        if ($status) {
            $status->update([
                'is_approved' => 1,
                'is_editable' => $request->is_editable ? $request->is_editable : 1,
                'is_failed' => 0,
                'note' => '',
                'data' => null
            ]);
        }

        return response()->json([
            'message' => 'اطلاعات با موفقیت تایید شد.',
            'is_success' => true,
            'status_code' => 200,
        ]);
    }

    /**
     * Update user infos from admin
     *
     * @param Request $request
     * @param User $user
     */
    public function update(Request $request, User $user, $table_name)
    {
        // dd($request->all(),$table_name);
        // switch case for call controller for each table
        switch ($table_name) {
            case 'nature_infos':
                return $this->natureUpdate($request, $user);
            case 'store_infos':
                return $this->storeUpdate($request, $user);
            case 'address_infos':
                return $this->addressUpdate($request, $user);
            case 'finance_infos':
                return $this->financeUpdate($request, $user);
            case 'documents_infos':
                return $this->documentUpdate($request, $user);
            case 'contract_infos':
                return $this->contractUpdate($request, $user);
            case 'legal_infos':
                return $this->legalUpdate($request, $user);
            default:
                return response()->json([
                    'message' => 'اطلاعات ارسالی نامعتبر است.',
                    'is_success' => false,
                    'status_code' => 400,
                ]);
        }
    }

    /**
     * Update user nature infos from admin
     *
     * @param Request $request
     * @param User $user
     */
    private function natureUpdate($request, $user)
    {
        // call nature infos controller for update call updateAdmin function
        $natureInfoController = new \App\Http\Controllers\NaturalInfoController();
        return $natureInfoController->updateByAdmin($request, $user);
    }

    /**
     * Update user store infos from admin
     *
     * @param Request $request
     * @param User $user
     */
    private function storeUpdate(Request $request, User $user)
    {
        // call store infos controller for update call updateAdmin function
        $storeInfoController = new \App\Http\Controllers\StoreInfoController();
        return $storeInfoController->updateByAdmin($request, $user);
    }

    /**
     * Update user address infos from admin
     *
     * @param Request $request
     * @param User $user
     */
    private function addressUpdate(Request $request, User $user)
    {
        // call address infos controller for update call updateAdmin function
        $addressInfoController = new \App\Http\Controllers\AddressInfoController();
        return $addressInfoController->updateByAdmin($request, $user);
    }

    /**
     * Update user finance infos from admin
     */
    private function financeUpdate(Request $request, User $user)
    {
        // call finance infos controller for update call updateAdmin function
        $financeInfoController = new \App\Http\Controllers\FinanceInfoController();
        return $financeInfoController->updateByAdmin($request, $user);
    }

    /**
     * Update user document infos from admin
     */
    private function documentUpdate(Request $request, User $user)
    {
        // call document infos controller for update call updateAdmin function
        $documentInfoController = new \App\Http\Controllers\DocumentsInfoController();
        return $documentInfoController->updateByAdmin($request, $user);
    }

    /**
     * Update user contract infos from admin
     */
    private function contractUpdate(Request $request, User $user)
    {
        // call contract infos controller for update call updateAdmin function
        $contractInfoController = new \App\Http\Controllers\ContractInfoController();
        return $contractInfoController->updateByAdmin($request, $user);
    }

    /**
     * Update user legal infos from admin
     */
    private function legalUpdate(Request $request, User $user)
    {
        // call legal infos controller for update call updateAdmin function
        $legalInfoController = new \App\Http\Controllers\LegalInfoController();
        return $legalInfoController->updateByAdmin($request, $user);
    }

    /**
     * Get user products with product id
     */
    public function productShow(\App\Models\product $product)
    {
        $product = $product->load('product_characteristics.characteristics')->toArray();
        return response()->json([
            'message' => 'اطلاعات محصول با موفقیت دریافت شد.',
            'is_success' => true,
            'status_code' => 200,
            'data' => $product,
        ]);
    }
    /**
     * Create product for supplier
     */
    public function productStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_actived' => 'required|boolean',
            'is_approved' => 'required|boolean',
            'is_rejected' => 'required|boolean',
            'user_id' => 'required|integer',
            'title' => 'required|string|max:128',
            'category_id' => 'nullable|integer',
            'category' => 'nullable|string',
            'price' => 'nullable|integer',
            'time' => 'required|integer',
            'features' => 'nullable|array',
            'features.*.title' => 'required|string',
            'features.*.value' => 'required|string',
            'limited' => 'required|boolean',
            'quantity' => 'nullable|integer',
            'media' => 'nullable|array',
            'media.image' => 'nullable|array',
            'media.image.*.photoUrl' => 'required|string',
            'media.video' => 'nullable|array',
            'details' => 'required|string',
            'filters' => 'nullable|array',
            'filters.*.category_id' => 'required',
            'filters.*.characteristic_id' => 'required',
            'filters.*.characteristic_id.characteristics' => 'nullable|array',
            'filters.*.characteristic_id.characteristics.*.key' => 'required',
            'province_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات وارد شده صحیح نمی باشد', // TODO: change this message $validator->errors()
                'is_success' => false,
                'status_code' => 400,
                'data' => $validator->errors(),
            ]);
        }

        // try and catch to create product
        try {
            $product = new \App\Models\product();
            $product->is_actived = $request->is_actived;
            $product->is_approved = $request->is_approved;
            $product->is_rejected = $request->is_rejected;
            $product->user_id = $request->user_id;
            $product->title = $request->title;
            $product->category_id = $request->category_id;
            $product->category = $request->category;
            $product->price = $request->price;
            $product->time = $request->time;
            $product->features = json_encode($request->features);
            $product->limited = $request->limited;
            $product->quantity = $request->quantity;
            $product->media = json_encode($request->media);
            $product->details = $request->details;
            $product->province_id = $request->province_id;
            $product->save();
            if ($request->has('filters') && count($request->filters) > 0) {
                // get from filter => category_id and from filter->characteristics => characteristics.id and characteristics.key
                foreach ($request->filters as $filter) {
                    product_characteristics::create([
                        'product_id' => $product->id,
                        'category_id' => $filter['category_id'],
                        'characteristic_id' => $filter['characteristic_id'],
                        'default_value' => $filter['characteristics']['key'],
                    ]);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'مشکلی در ایجاد محصول به وجود آمده است',
                'is_success' => false,
                'status_code' => 500,
                'data' => $th->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'محصول با موفقیت ایجاد شد',
            'is_success' => true,
            'status_code' => 201,
            'data' => $product,
        ]);
    }
    /**
     * Update user product infos from admin
     */
    public function productUpdate(Request $request, \App\Models\product $product)
    {
        $validator = Validator::make($request->all(), [
            'is_actived' => 'required|boolean',
            'is_approved' => 'required|boolean',
            'is_rejected' => 'required|boolean',
            'user_id' => 'required|integer',
            'title' => 'required|string|max:128',
            'category_id' => 'nullable|integer',
            'category' => 'nullable|string',
            'price' => 'nullable|integer',
            'time' => 'required|integer',
            'features' => 'nullable|array',
            'features.*.title' => 'required|string',
            'features.*.value' => 'required|string',
            'limited' => 'required|boolean',
            'quantity' => 'nullable|integer',
            'media' => 'nullable|array',
            'media.image' => 'nullable|array',
            'media.image.*.photoUrl' => 'required|string',
            'media.video' => 'nullable|array',
            'details' => 'required|string',
            'filters' => 'nullable|array',
            'filters.*.category_id' => 'required',
            'filters.*.characteristic_id' => 'required',
            'filters.*.characteristic_id.characteristics' => 'nullable|array',
            'filters.*.characteristic_id.characteristics.*.key' => 'required',
            'province_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات وارد شده صحیح نمی باشد', // TODO: change this message $validator->errors()
                'is_success' => false,
                'status_code' => 400,
                'data' => $validator->errors(),
            ]);
        }

        // try and catch to update product
        try {
            $product->update([
                'is_actived' => $request->is_actived,
                'is_approved' => $request->is_approved,
                'is_rejected' => $request->is_rejected,
                'user_id' => $request->user_id,
                'title' => $request->title,
                'category_id' => $request->category_id,
                'category' => $request->category,
                'price' => $request->price,
                'time' => $request->time,
                'features' => json_encode($request->features),
                'limited' => $request->limited,
                'quantity' => $request->quantity,
                'media' => json_encode($request->media),
                'details' => $request->details,
                'province_id' => $request->province_id,
            ]);
            if ($request->has('filters') && count($request->filters) > 0) {
                $product_characteristics = $product->load('product_characteristics.characteristics')->product_characteristics();
                $product_characteristics->delete();
                // get from filter => category_id and from filter->characteristics => characteristics.id and characteristics.key
                foreach ($request->filters as $filter) {
                    product_characteristics::create([
                        'product_id' => $product->id,
                        'category_id' => $filter['category_id'],
                        'characteristic_id' => $filter['characteristic_id'],
                        'default_value' => $filter['characteristics']['key'],
                    ]);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'مشکلی در ویرایش محصول به وجود آمده است',
                'is_success' => false,
                'status_code' => 500,
                'data' => $th->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'محصول با موفقیت ویرایش شد',
            'is_success' => true,
            'status_code' => 200,
            'data' => $product,
        ]);
    }
}
