<?php

use App\Models\Message;
use App\Models\Category;
use App\Models\PostTag;
use App\Models\PostCategory;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Shipping;
use App\Models\Cart;
use App\Models\LuckyWheelSetting;
use App\Models\LuckyWheelSpin;
use Illuminate\Support\Str;

// use Auth;
class Helper
{
    public static function messageList()
    {
        return Message::whereNull('read_at')->orderBy('created_at', 'desc')->get();
    }
    public static function getAllCategory()
    {
        $category = new Category();
        $menu = $category->getAllParentWithChild();
        return $menu;
    }

    public static function getHeaderCategory()
    {
        $category = new Category();
        // dd($category);
        $menu = $category->getAllParentWithChild();

        if ($menu) {
?>

            <li>
                <a href="javascript:void(0);">Category<i class="ti-angle-down"></i></a>
                <ul class="dropdown border-0 shadow">
                    <?php
                    foreach ($menu as $cat_info) {
                        if ($cat_info->child_cat->count() > 0) {
                    ?>
                            <li><a href="<?php echo route('product-cat', $cat_info->slug); ?>"><?php echo $cat_info->title; ?></a>
                                <ul class="dropdown sub-dropdown border-0 shadow">
                                    <?php
                                    foreach ($cat_info->child_cat as $sub_menu) {
                                    ?>
                                        <li><a href="<?php echo route('product-sub-cat', [$cat_info->slug, $sub_menu->slug]); ?>"><?php echo $sub_menu->title; ?></a></li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            </li>
                        <?php
                        } else {
                        ?>
                            <li><a href="<?php echo route('product-cat', $cat_info->slug); ?>"><?php echo $cat_info->title; ?></a></li>
                    <?php
                        }
                    }
                    ?>
                </ul>
            </li>
<?php
        }
    }


    public static function postCategoryList($option = "all")
    {
        if ($option == 'all') {
            return PostCategory::orderBy('id', 'DESC')->get();
        }
        return PostCategory::has('posts')->orderBy('id', 'DESC')->get();
    }

    // Cart Count
    public static function cartCount($user_id = '')
    {
        if (Auth::check()) {
            if ($user_id == "") $user_id = auth()->user()->id;
            return Cart::where('user_id', $user_id)->where('order_id', null)->sum('quantity');
        } else {
            return 0;
        }
    }

    // relationship cart with product
    public function product()
    {
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }

    public static function getAllProductFromCart($user_id = '')
    {
        if (Auth::check()) {
            if ($user_id == "") $user_id = auth()->user()->id;
            return Cart::with('product')->where('user_id', $user_id)->where('order_id', null)->get();
        } else {
            return 0;
        }
    }

    // Total amount cart
    public static function totalCartPrice($user_id = '')
    {
        if (Auth::check()) {
            if ($user_id == "") $user_id = auth()->user()->id;
            return Cart::where('user_id', $user_id)->where('order_id', null)->sum('amount');
        } else {
            return 0;
        }
    }

    // Wishlist Count
    public static function wishlistCount($user_id = '')
    {
        if (Auth::check()) {
            if ($user_id == "") $user_id = auth()->user()->id;
            return Wishlist::where('user_id', $user_id)->where('cart_id', null)->sum('quantity');
        } else {
            return 0;
        }
    }

    public static function getAllProductFromWishlist($user_id = '')
    {
        if (Auth::check()) {
            if ($user_id == "") $user_id = auth()->user()->id;
            return Wishlist::with('product')->where('user_id', $user_id)->where('cart_id', null)->get();
        } else {
            return 0;
        }
    }

    public static function totalWishlistPrice($user_id = '')
    {
        if (Auth::check()) {
            if ($user_id == "") $user_id = auth()->user()->id;
            return Wishlist::where('user_id', $user_id)->where('cart_id', null)->sum('amount');
        } else {
            return 0;
        }
    }

    // Total price with shipping and coupon
    public static function grandPrice($id, $user_id)
    {
        $order = Order::find($id);
        // dd($id);
        if ($order) {
            $shipping_price = (float)$order->shipping->price;
            $order_price = self::orderPrice($id, $user_id);
            return number_format((float)($order_price + $shipping_price), 2, '.', '');
        } else {
            return 0;
        }
    }

    // Admin home
    public static function earningPerMonth()
    {
        $month_data = Order::where('status', 'delivered')->get();
        // return $month_data;
        $price = 0;
        foreach ($month_data as $data) {
            $price = $data->cart_info->sum('price');
        }
        return number_format((float)($price), 2, '.', '');
    }

    public static function shipping()
    {
        return Shipping::orderBy('id', 'DESC')->get();
    }

    // Lucky Wheel Helper Methods
    public static function getLuckyWheelSettings()
    {
        return LuckyWheelSetting::first();
    }

    public static function getUserRemainingSpins($userId = null)
    {
        if (!$userId) {
            $userId = auth()->id();
        }

        if (!$userId) {
            return 0;
        }

        $settings = self::getLuckyWheelSettings();
        $maxSpins = $settings ? $settings->max_spins_per_day : 3;

        $userSpinsToday = LuckyWheelSpin::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();

        return max(0, $maxSpins - $userSpinsToday);
    }

    public static function isLuckyWheelEnabled()
    {
        $settings = self::getLuckyWheelSettings();
        return $settings ? $settings->is_enabled : false;
    }

    public static function getUserTotalSpins($userId = null)
    {
        if (!$userId) {
            $userId = auth()->id();
        }

        if (!$userId) {
            return 0;
        }

        return LuckyWheelSpin::where('user_id', $userId)->count();
    }

    public static function getUserWinCount($userId = null)
    {
        if (!$userId) {
            $userId = auth()->id();
        }

        if (!$userId) {
            return 0;
        }

        return LuckyWheelSpin::where('user_id', $userId)
            ->where('is_winner', true)
            ->count();
    }  

    /**
     * Convert image path to use photos directory instead of storage
     */
    public static function getImagePath($imagePath)
    {
        if (empty($imagePath)) {
            return asset('backend/img/thumbnail-default.jpg');
        }

        // If path already starts with photos/, return as is
        if (strpos($imagePath, 'photos/') === 0) {
            return asset($imagePath);
        }

        // If path contains storage path, convert to photos
        if (strpos($imagePath, 'storage/') === 0) {
            $newPath = str_replace('storage/', 'photos/', $imagePath);
            return asset($newPath);
        }

        // If it's just a filename, assume it's in photos/1/
        if (!strpos($imagePath, '/')) {
            return asset('photos/1/' . $imagePath);
        }

        // Default: assume it's already a relative path from public
        return asset($imagePath);
    }

}

if (!function_exists('generateUniqueSlug')) {
    /**
     * Generate a unique slug for a given title and model.
     *
     * @param string $title
     * @param string $modelClass
     * @return string
     */
    function generateUniqueSlug($title, $modelClass)
    {
        $slug = Str::slug($title);
        $count = $modelClass::where('slug', $slug)->count();

        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }

        return $slug;
    }
}

?>