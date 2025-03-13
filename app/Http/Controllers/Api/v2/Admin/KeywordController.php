<?php

namespace App\Http\Controllers\Api\v2\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Keyword;
use Illuminate\Support\Facades\Validator;

class KeywordController extends Controller
{
    /**
     *
     */
    public function index(string $type, int $id)
    {
        $type = $type === 'category' ? 'category_id' : 'product_id';
        try {
            $keywords = Keyword::where($type, $id)->get();

            if ($keywords->first() instanceof Keyword) {

                return response()->json(
                    [
                        'message' => __('message.found'),
                        'is_success' => true,
                        'have_key' => true,
                        'status_code' => 200,
                        'data' => $keywords->all(),
                    ],
                );
            } else {
                return response()->json(
                    [
                        'message' => __('message.not_found'),
                        'is_success' => true,
                        'have_key' => false,
                        'status_code' => 200,
                        'data' => null,
                    ],
                );
            }
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'مشکلی در دیتا',
                    'is_success' => false,
                    'status_code' => 400,
                    'data' => null,
                ],
            );
        }
    }

    public function indexCategory(int $category_id)
    {
    }

    public function indexProduct(int $product_id)
    {
    }

    public function create(Request $request, string $type, int $id)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'title' => 'required|string|max:216',
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

        $title = $data['title'];
        $type = $type === 'category' ? 'category_id' : 'product_id';

        try {
            $same = $this->findSame($title);
            if ($same['have_same']) {
                return response()->json(
                    [
                        'message' => 'کیورد مشابه و یا تکراری است',
                        'is_success' => false,
                        'have_same' => $same['have_same'],
                        'status_code' => 200,
                        'data' => Keyword::where($type, $id)->get()->all(),
                        'same_details' => $same['same_details'],
                        'title' => $title
                    ],
                );
            } else {
                $res = Keyword::create([
                    'title' => $title,
                    'product_id' => $type === 'product_id' ? $id : null,
                    'category_id' => $type === 'category_id' ? $id : null,
                ]);

                if ($res instanceof Keyword) {
                    return response()->json(
                        [
                            'message' => __('message.success'),
                            'is_success' => true,
                            'have_same' => $same['have_same'],
                            'status_code' => 200,
                            'data' => Keyword::where($type, $id)->get()->all(),
                            'same_details' => $same['same_details'],
                            'title' => $title
                        ],
                    );
                }
            }
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'مشکلی در دیتا',
                    'is_success' => false,
                    'have_same' => false,
                    'status_code' => 400,
                    'data' => null,
                    'same_details' => null
                ],
            );
        }
    }

    private function findSame(string $title)
    {
        $keys = Keyword::where('title', 'LIKE', '%' . $title . '%')->get();

        if ($keys->first() instanceof Keyword) {
            return [
                'have_same' => true,
                'same_details' => $keys->toArray()
            ];
        } else {
            return [
                'have_same' => false,
                'same_details' => null
            ];
        }
    }

    public function destroy(Keyword $Keyword)
    {
        $id = $Keyword->product_id !== null ? $Keyword->product_id : $Keyword->category_id;
        $title = $Keyword->title;
        $type = $Keyword->product_id !== null ? 'product_id' : 'category_id';
        try {
            $res = $Keyword->delete();
            if ($res) {
                return response()->json(
                    [
                        'message' => __('message.success'),
                        'is_success' => true,
                        'status_code' => 200,
                        'data' => Keyword::where($type, $id)->get()->all(),
                        'title' => $title
                    ],
                );
            }
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'مشکلی در دیتا',
                    'is_success' => false,
                    'status_code' => 400,
                    'data' => null,
                    'title' => null
                ],
            );
        }
    }
}
