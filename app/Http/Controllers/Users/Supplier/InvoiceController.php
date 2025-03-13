<?php

namespace App\Http\Controllers\Users\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\purchase_offers;
use App\Models\purchase_requests;
use App\Models\product;
use Carbon\Carbon;
# handle all validators in this functinoality
use App\Http\Requests\Api\V2\Users\Supplier\InvoiceRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class InvoiceController extends Controller
{

    /**
     * Create invoice for purchase request.
     *
     * @param Request $request The HTTP request object.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the result of the operation.
     */
    public function createInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_request_id' => 'required|integer',
            'details' => 'nullable|string',
            'image' => 'nullable|array',
            'image.*.url' => 'required|string', // TODO: add regex for omdico cdn
            'suggested_date' => 'required|date|date_format:Y-m-d|after_or_equal:today', // TODO must get it from suppliers
            'confirmed_date' => 'nullable|boolean', // TODO: must check it
            'items' => 'required|array',
            'items.*.item_name' => 'required|min:2|max:255',
            'items.*.unit' => 'required|string|in:number,weight,volume',
            'items.*.id' => ['nullable', 'integer'],
            'items.*.count' => 'required|integer|min:0',
            'items.*.unit_price' => 'required|regex:/^(?:[0-9]\d{0,11})$/',
            'items.*.is_shop' => 'required|boolean',
            'have_tax' => 'nullable|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors(),
                    'is_success' => false,
                    'status_code' => 422,
                ],
            );
        }

        // check if user can add invoice, if not return error
        $this->supplierCanAddInvoice($request);
        // Admin can add any
        $is_admin =  $request->user()->type === 'admin';
        $items = $this->validateProductIds($validator->validated()['items'], $request->user()->id, $is_admin);
        $proposed_price = $this->totalPrice($items);
        $data = array_merge($validator->validated(), [
            'user_id' => $request->user()->id,
            'items' => json_encode($items),
            'proposed_price' => $proposed_price,
            'status' => 'pending',
            'confirmed_date' => false,
            'is_winner' => false,
            'have_tax' => false,
        ]);

        $invoice = purchase_offers::create($data);
        if (!$invoice) {
            return response()->json([
                'message' => __('message.invoice_cant_created'),
                'is_success' => false,
                'status_code' => 500,
            ]);
        }
        return response()->json([
            'message' => __('message.invoice_created'),
            'is_success' => true,
            'status_code' => 200,
            'data' => $invoice->toArray()
        ]);
    }

    /**
     * Validates the product IDs in the given array of items.
     *
     * @param array $items The array of items to validate.
     * @param int $userId The ID of the user.
     * @param bool $is_admin (optional) Flag indicating if the user is an admin. Defaults to false.
     * @throws HttpResponseException If some products do not exist or do not belong to the specified user.
     * @return array The validated array of items with 'item_name' added for each item.
     */
    protected function validateProductIds(array $items, int $userId, bool $is_admin = false)
    {
        $productIds = array_filter(array_column($items, 'id'));

        if (empty($productIds)) {
            return $this->safeItems($items);
        }

        // Admin can add any
        if ($is_admin) {
            $products = product::whereIn('id', $productIds)->select('id', 'title')->get()->keyBy('id');
        } else {
            $products = product::whereIn('id', $productIds)->where('user_id', $userId)->select('id', 'title')->get()->keyBy('id');
        }

        $productsCount = $products->count();

        if ($productsCount !== count($productIds)) {
            throw new HttpResponseException(response()->json(
                [
                    'message' => 'Some products do not exist or do not belong to the specified user.',
                    'is_success' => false,
                    'status_code' => 422,
                ],
                200
            ));
        } else {
            foreach ($items as &$item) {
                if (isset($item['id']) && isset($products[$item['id']])) {
                    $item['item_name'] = $products[$item['id']]->title;
                    $item['is_shop'] = true;
                }
            }

            return $this->safeItems($items);
        }
    }

    /**
     * Sets the 'is_shop' key of each item in the array to true if the 'id' key is an integer, false otherwise.
     *
     * @param array $items The array of items to process.
     * @return array The processed array of items.
     */
    protected function safeItems(array $items)
    {
        foreach ($items as &$item) {
            $item['is_shop'] = is_int($item['id']);
            $item['total_price'] = $item['unit_price'] * $item['count'];
        }
        return $items;
    }

    /**
     * Calculates the total price of an array of items.
     *
     * @param array $items An array of items, each containing 'unit_price' and 'count' keys.
     * @return int The total price of the items.
     */
    protected function totalPrice(array $items)
    {
        return array_reduce($items, function ($carry, $item) {
            return $carry + ($item['unit_price'] * $item['count']);
        }, 0);
    }

    /**
     * Checks if the supplier can add an invoice for the given purchase request.
     *
     * @param Request $request The HTTP request object containing the user and purchase request information.
     * @throws HttpResponseException If the selected purchase request is not valid for the user.
     * @return bool Returns true if the supplier can add an invoice, false otherwise.
     */
    protected function supplierCanAddInvoice(Request $request)
    {
        $interested = $request->user()->supplierProductsList->pluck('id')->all();
        $res = purchase_requests::where('id', $request->purchase_request_id)
            ->where('status', 'active')
            ->where('active_time', '>=', Carbon::now())
            ->whereIn('category_id', $interested)
            ->where('user_id', '!=', $request->user()->id)->exists();

        if (!($res)) {
            throw new HttpResponseException(response()->json(
                [
                    'message' => 'The selected purchase request is not valid for this user.',
                    'is_success' => false,
                    'status_code' => 422,
                ],
                200
            ));
        }

        $res = purchase_offers::where('purchase_request_id', $request->purchase_request_id)
            ->where('user_id', $request->user()->id)->exists();
        if($res) {
            throw new HttpResponseException(response()->json(
                [
                    'message' => __('message.purchase_offer_is_duplicated'),
                    'is_success' => false,
                    'status_code' => 422,
                ],
                200
            ));
        }
        return $res ? true : false;
    }
}
