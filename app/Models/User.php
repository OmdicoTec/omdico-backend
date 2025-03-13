<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
// Interface and Trait for Mobile Verification
use App\Traits\MustVerifyMobile;
use App\Interface\MustVerifyMobile as IMustVerifyMobile;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable implements IMustVerifyMobile
{
    use HasApiTokens, HasFactory, Notifiable;
    use MustVerifyMobile;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'family',
        'type',
        'email',
        'password',
        'mobile_number',
        'mobile_verify_code',
        'mobile_attempts_left',
        'mobile_last_attempt_date',
        'mobile_verify_code_sent_at',
        'is_active',
        'is_legal_person',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mobile_verify_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'number_verified_at' => 'datetime',
        'mobile_verify_code_sent_at' => 'datetime',
        'mobile_last_attempt_date' => 'datetime',
    ];
    // user types
    public function hasRole($types)
    {
        // return $this->type === $type;
        return in_array($this->type, $types);
    }
    // in model or use MustVerifyMobile trait
    public function userMobileNumber()
    {
        return $this->mobile_number;
    }

    /**
     * Convert is_active boolean to true or false
     */
    public function getIsActiveAttribute($value)
    {
        return $value ? true : false;
    }
    /**
     * Get the user status info
     */
    public function status(): HasMany
    {
        return $this->hasMany(StatusInfo::class);
    }

    /**
     * Get the natural status info
     */
    public function natural(): HasOne
    {
        return $this->hasOne(NaturalInfo::class);
    }
    /**
     * Get the store status info
     */
    public function store(): HasOne
    {
        return $this->hasOne(StoreInfo::class);
    }
    /**
     * Get the legal status infom, not yet implemented
     */
    public function legal(): HasOne
    {
        return $this->hasOne(LegalInfo::class);
    }
    /**
     * Get address info
     */
    public function address(): HasOne
    {
        return $this->hasOne(AddressInfo::class);
    }
    /**
     * Get the finance info
     */
    public function finance(): HasOne
    {
        return $this->hasOne(FinanceInfo::class);
    }
    /**
     * Get the document info
     */
    public function document(): HasOne
    {
        return $this->hasOne(DocumentsInfo::class);
    }
    /**
     * Get the contract info
     */
    public function contract(): HasOne
    {
        return $this->hasOne(ContractInfo::class);
    }

    /**
     * Get the files for the user.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Get the products for the user.
     */
    public function products(): HasMany
    {
        return $this->hasMany(product::class);
    }
    /**
     * Returns the supplier products for the user.
     *
     * @return HasMany
     */
    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class)->with('category:id,title');
    }

    public function supplierProductsCategoryIdOnly(): HasMany
    {
        return $this->hasMany(SupplierProduct::class)
            ->select('supplier_products.user_id', 'supplier_products.category_id');
    }
    public function supplierProductsList()
    {
        return $this->belongsToMany(category::class, 'supplier_products')->select('categories.id', 'categories.title');
    }
    /**
     * Get the purchases offers for the user.
     */
    public function purchase_offers(): HasMany
    {
        return $this->hasMany(purchase_offers::class);
    }
    public function purchase_offers_list_request_ids()
    {
        return $this->purchase_offers()
        ->select('purchase_offers.user_id' , 'purchase_offers.purchase_request_id');
    }
    /**
     * Get the address to delivery for user.
     */
    public function delivery(): HasMany
    {
        return $this->hasMany(Address::class)->with('province:id,name');
    }

    /**
     * Get the purchase requests for the user with specific status(es).
     * If $statuses is empty, retrieve all purchase requests.
     *
     * @param array|null $statuses
     * @return HasMany
     */
    public function purchase_requests(array $statuses = null): HasMany
    {
        $query = $this->hasMany(purchase_requests::class);

        if (!empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        return $query->with(['category' => function ($query) {
            $query->select('id', 'title'); // Include only 'id' and 'title' from the category
        }])->with(['province' => function ($query) {
            $query->select('id', 'name'); // Include only 'id' and
        }])->where('active_time', '>=', now());
    }

    /**
     * Get the invoice for the user for the buyer.
     */
    public function buyerInvoice(): HasMany
    {
        return $this->hasMany(invoice::class, 'buyer_id', 'id');
    }
    // TODO: add the seller invoice
    public function invoice(): HasMany
    {
        return $this->hasMany(invoice::class);
    }
    /**
     * Get the invoice for the user for the seller.
     */
    public function sellerInvoice(): HasMany
    {
        return $this->hasMany(invoice::class, 'seller_id', 'id');
    }

    /**
     * Get the transfers for the user.
     * @return transfers
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(transfers::class);
    }

    /**
     * Get the wallet for the user.
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(wallet::class);
    }

    /**
     * For user login with mobile number without user id identification
     */
    public static function findByMobileNumber($mobileNumber)
    {
        return static::where('mobile_number', $mobileNumber)->first();
    }

    /**
     * Get the carts for user
     */
    public function carts(): HasOne
    {
        return $this->hasOne(Carts::class, 'user_id');
    }

    public function scopeWithSupplierproduct($query)
    {
        return $query->with('supplier_products');
    }
}
